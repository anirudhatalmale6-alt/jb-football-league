<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmedMail;
use App\Models\Competition;
use App\Models\Group;
use App\Models\RegistrationPayment;
use App\Models\Team;
use App\Services\ToyyibpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    public function index()
    {
        $competitions = Competition::withCount('teams')
            ->orderBy('start_date')
            ->get();

        return view('registration.index', compact('competitions'));
    }

    public function create($competitionId)
    {
        $competition = Competition::with('groups')->findOrFail($competitionId);
        $groups = $competition->groups()->orderBy('order')->get();

        // Teams the current user already manages in OTHER competitions. Offering
        // these lets them register the same club here without re-typing the
        // details, and their players/officials get copied over automatically.
        $existingTeams = collect();
        $user = Auth::user();
        if ($user) {
            $ids = $user->managedTeams()->pluck('teams.id')->all();
            if (!empty($ids)) {
                $existingTeams = Team::with('competition')
                    ->withCount(['players', 'officials'])
                    ->whereIn('id', $ids)
                    ->where('competition_id', '!=', $competition->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('registration.create', compact('competition', 'groups', 'existingTeams'));
    }

    public function store(Request $request, $competitionId)
    {
        $competition = Competition::findOrFail($competitionId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:10'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_position' => ['required', 'string', 'max:255'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_location' => ['nullable', 'string', 'max:255'],
            'venue_coordinator_name' => ['nullable', 'string', 'max:255'],
            'venue_coordinator_phone' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'reuse_team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'terms_agreed' => ['required', 'accepted'],
        ]);

        // Force uppercase on name fields
        $validated['name'] = mb_strtoupper($validated['name']);
        $validated['applicant_name'] = mb_strtoupper($validated['applicant_name']);
        if (!empty($validated['short_name'])) $validated['short_name'] = mb_strtoupper($validated['short_name']);
        if (!empty($validated['manager_name'])) $validated['manager_name'] = mb_strtoupper($validated['manager_name']);
        if (!empty($validated['venue_name'])) $validated['venue_name'] = mb_strtoupper($validated['venue_name']);
        if (!empty($validated['venue_coordinator_name'])) $validated['venue_coordinator_name'] = mb_strtoupper($validated['venue_coordinator_name']);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $team = Team::create([
            'competition_id' => $competition->id,
            'group_id' => $validated['group_id'] ?? null,
            'name' => $validated['name'],
            'short_name' => $validated['short_name'] ?? null,
            'logo' => $logoPath,
            'manager_name' => $validated['manager_name'] ?? null,
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
            'applicant_name' => $validated['applicant_name'],
            'applicant_position' => $validated['applicant_position'],
            'venue_name' => $validated['venue_name'] ?? null,
            'venue_location' => $validated['venue_location'] ?? null,
            'venue_coordinator_name' => $validated['venue_coordinator_name'] ?? null,
            'venue_coordinator_phone' => $validated['venue_coordinator_phone'] ?? null,
            'status' => 'pending',
            'terms_agreed' => true,
            'terms_agreed_at' => now(),
            'terms_agreed_by' => Auth::user()->name,
        ]);

        // Auto-link user to team
        $user = Auth::user();
        if ($user && $user->isTeamManager()) {
            if (!$user->team_id) {
                $user->update(['team_id' => $team->id]);
            }
            $user->managedTeams()->syncWithoutDetaching([$team->id]);
        }

        // Reuse an existing team the user manages: copy its logo (when no new
        // logo was uploaded) and clone its players + officials so the club does
        // not have to re-enter the whole squad for this competition.
        $reuseId = (int) $request->input('reuse_team_id');
        if ($reuseId) {
            $source = Team::with(['players', 'officials'])->find($reuseId);
            $managedIds = $user ? $user->managedTeams()->pluck('teams.id')->map(fn ($i) => (int) $i)->all() : [];
            $isAdmin = $user && ($user->isSuper() || $user->isLeagueAdmin());
            if ($source && $source->id !== $team->id && ($isAdmin || in_array($reuseId, $managedIds, true))) {
                if (empty($logoPath) && !empty($source->logo)) {
                    $team->update(['logo' => $source->logo]);
                }
                foreach ($source->players as $p) {
                    $team->players()->create($p->only([
                        'name', 'jersey_number', 'position', 'date_of_birth', 'nationality',
                        'ic_number', 'ic_photo', 'photo', 'bg_removed_photo', 'status', 'verification_status',
                    ]));
                }
                foreach ($source->officials as $o) {
                    $team->officials()->create($o->only([
                        'name', 'role', 'nationality', 'ic_number', 'ic_photo',
                        'contact_phone', 'photo', 'certificate',
                    ]));
                }
            }
        }

        $registrationFee = $competition->registration_fee ?? 0;
        $securityDeposit = $competition->security_deposit ?? 0;
        $matchdayFee = $competition->matchday_fee ?? 0;
        // New teams owe the RM50 annual fee by default (league only); an admin
        // can later mark the team exempt, which recalculates its pending payment.
        $annualFee = ($competition->type === 'league') ? Team::AFFILIATE_FEE : 0;
        $fee = $registrationFee + $securityDeposit + $matchdayFee + $annualFee;

        $payment = RegistrationPayment::create([
            'team_id' => $team->id,
            'competition_id' => $competition->id,
            'user_id' => Auth::id(),
            'amount' => $fee,
            'currency' => 'MYR',
            'status' => $fee > 0 ? 'pending' : 'paid',
            'payment_method' => 'fpx',
            'paid_at' => $fee > 0 ? null : now(),
        ]);

        // Real-time path: if the toyyibpay API is configured, create a UNIQUE
        // bill per team so toyyibpay can call us back the instant it is paid and
        // the status flips to PAID automatically (no manual marking, no delay).
        if ($fee > 0 && $this->toyyibpayApiConfigured()) {
            try {
                $service = new ToyyibpayService();
                $result = $service->createBill([
                    'name' => 'Yuran ' . mb_substr($competition->name, 0, 25),
                    'description' => 'Registration fee - ' . $team->name,
                    'amount' => $fee,
                    'return_url' => route('payment.return'),
                    'callback_url' => route('payment.callback'),
                    'reference' => (string) $payment->id,
                    'payer_name' => $validated['contact_email'] ? $team->name : 'Team',
                    'payer_email' => $validated['contact_email'],
                    'payer_phone' => $validated['contact_phone'] ?? '',
                ]);

                if (!empty($result['success']) && !empty($result['url'])) {
                    $payment->update(['billcode' => $result['billcode'] ?? null]);

                    return redirect()->route('registration.success')
                        ->with('team_name', $team->name)
                        ->with('competition_name', $competition->name)
                        ->with('payment_status', 'pending')
                        ->with('fee', $fee)
                        ->with('payment_url', $result['url']);
                }

                Log::warning('toyyibpay createBill failed, falling back to shared link', ['payment' => $payment->id]);
            } catch (\Throwable $e) {
                Log::error('toyyibpay createBill error: ' . $e->getMessage(), ['payment' => $payment->id]);
            }
        }

        if ($fee > 0 && !empty($competition->payment_url)) {
            return redirect()->route('registration.success')
                ->with('team_name', $team->name)
                ->with('competition_name', $competition->name)
                ->with('payment_status', 'pending')
                ->with('fee', $fee)
                ->with('payment_url', $competition->payment_url);
        }

        if ($fee > 0) {
            return redirect()->route('registration.success')
                ->with('team_name', $team->name)
                ->with('competition_name', $competition->name)
                ->with('payment_status', 'pending')
                ->with('fee', $fee);
        }

        return redirect()->route('registration.success')
            ->with('team_name', $team->name)
            ->with('competition_name', $competition->name)
            ->with('payment_status', 'paid')
            ->with('fee', 0);
    }

    public function paymentReturn(Request $request)
    {
        $billcode = $request->input('billcode');
        $statusId = $request->input('status_id');
        $transactionId = $request->input('transaction_id');

        $payment = RegistrationPayment::where('billcode', $billcode)->first();

        if ($payment) {
            if ($statusId == 1) {
                $wasPaid = $payment->status === 'paid';
                $payment->update([
                    'status' => 'paid',
                    'transaction_id' => $transactionId,
                    'paid_at' => $this->fetchToyyibpayPaidAt($billcode, $transactionId),
                ]);
                if (!$wasPaid) {
                    $this->sendPaymentConfirmation($payment);
                }

                return redirect()->route('registration.success')
                    ->with('team_name', $payment->team->name ?? 'Your team')
                    ->with('competition_name', $payment->competition->name ?? '')
                    ->with('payment_status', 'paid');
            }

            // Never downgrade an already-paid registration because of a later
            // failed or duplicate attempt on the same bill.
            if ($payment->status !== 'paid') {
                $payment->update([
                    'status' => 'failed',
                    'transaction_id' => $transactionId,
                ]);
            }

            return redirect()->route('registration.success')
                ->with('team_name', $payment->team->name ?? 'Your team')
                ->with('competition_name', $payment->competition->name ?? '')
                ->with('payment_status', $payment->status === 'paid' ? 'paid' : 'failed');
        }

        return redirect()->route('registration.index')
            ->with('error', 'Payment record not found.');
    }

    public function paymentCallback(Request $request)
    {
        $billcode = $request->input('billcode');
        $statusId = $request->input('status_id');
        $transactionId = $request->input('transaction_id');

        $payment = RegistrationPayment::where('billcode', $billcode)->first();

        if ($payment && $statusId == 1) {
            $wasPaid = $payment->status === 'paid';
            $payment->update([
                'status' => 'paid',
                'transaction_id' => $transactionId,
                'paid_at' => $this->fetchToyyibpayPaidAt($billcode, $transactionId),
            ]);
            if (!$wasPaid) {
                $this->sendPaymentConfirmation($payment);
            }
        } elseif ($payment && $payment->status !== 'paid') {
            // Do not overwrite a confirmed payment with a later failed attempt.
            $payment->update([
                'status' => 'failed',
                'transaction_id' => $transactionId,
            ]);
        }

        return response('OK', 200);
    }

    public function success()
    {
        return view('registration.success');
    }

    public function adminPayments(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $query = RegistrationPayment::with(['team', 'competition', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('team', fn ($q) => $q->where('name', 'like', "%{$s}%"));
        }

        $payments = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Overall figures (whole system, ignoring the current filter) so an admin
        // can cross-check at a glance who has paid and who has not.
        $summary = [
            'total'       => RegistrationPayment::count(),
            'paid'        => RegistrationPayment::where('status', 'paid')->count(),
            'pending'     => RegistrationPayment::where('status', 'pending')->count(),
            'failed'      => RegistrationPayment::where('status', 'failed')->count(),
            'collected'   => RegistrationPayment::where('status', 'paid')->sum('amount'),
            'outstanding' => RegistrationPayment::where('status', '!=', 'paid')->sum('amount'),
        ];

        $competitions = Competition::orderBy('name')->get();

        return view('registration.payments', compact('payments', 'summary', 'competitions'));
    }

    public function myPayments()
    {
        $user = Auth::user();

        $payments = RegistrationPayment::with(['team', 'competition', 'user'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                $mTeamIds = $user->managedTeamIds();
                if (!empty($mTeamIds)) {
                    $query->orWhereIn('team_id', $mTeamIds);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('registration.my-payments', compact('payments'));
    }

    public function markAsPaid($paymentId)
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $payment = RegistrationPayment::findOrFail($paymentId);
        $wasPaid = $payment->status === 'paid';
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => 'Marked as paid by ' . $user->name . ' on ' . now()->format('d/m/Y H:i'),
        ]);
        if (!$wasPaid) {
            $this->sendPaymentConfirmation($payment);
        }

        return redirect()->route('admin.payments')->with('success', __('app.paid'));
    }

    /**
     * Super Admin: pull the latest status from toyyibpay for every pending
     * payment that has a bill code, and mark any that are now settled as PAID.
     * Lets an admin refresh instantly instead of waiting.
     */
    public function syncPayments()
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        if (!$this->toyyibpayApiConfigured()) {
            return redirect()->route('admin.payments')
                ->with('warning', __('app.pay_sync_not_configured'));
        }

        $pending = RegistrationPayment::where('status', 'pending')
            ->whereNotNull('billcode')
            ->where('billcode', '!=', '')
            ->get();

        if ($pending->isEmpty()) {
            return redirect()->route('admin.payments')
                ->with('success', __('app.pay_sync_none'));
        }

        $service = new ToyyibpayService();
        $updated = 0;

        foreach ($pending as $payment) {
            try {
                $transactions = $service->getBillTransactions($payment->billcode);
                if (!is_array($transactions)) {
                    continue;
                }
                // billpaymentStatus === '1' means a successful payment.
                foreach ($transactions as $txn) {
                    if (($txn['billpaymentStatus'] ?? null) == '1') {
                        $payment->update([
                            'status' => 'paid',
                            'transaction_id' => $txn['billpaymentInvoiceNo'] ?? $payment->transaction_id,
                            'paid_at' => $this->parseToyyibpayDate($txn['billPaymentDate'] ?? null),
                        ]);
                        $this->sendPaymentConfirmation($payment);
                        $updated++;
                        break;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('toyyibpay sync error: ' . $e->getMessage(), ['payment' => $payment->id]);
            }
        }

        return redirect()->route('admin.payments')
            ->with('success', __('app.pay_sync_done', ['count' => $updated]));
    }

    /** Whether the toyyibpay API (real-time) credentials are configured. */
    /**
     * Parse a toyyibpay billPaymentDate ("d-m-Y H:i:s", Malaysia time) into a
     * Carbon instance. Falls back to the current time if absent/unparseable.
     */
    private function parseToyyibpayDate($billPaymentDate)
    {
        if (!empty($billPaymentDate)) {
            try {
                return \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', trim($billPaymentDate), 'Asia/Kuala_Lumpur')->utc();
            } catch (\Throwable $e) {
                Log::warning('toyyibpay date parse failed: ' . $e->getMessage(), ['value' => $billPaymentDate]);
            }
        }
        return now();
    }

    /**
     * Look up the exact payment time for a bill from toyyibpay, matching the
     * given transaction/invoice number when possible so our receipt time tallies
     * with the toyyibpay receipt.
     */
    private function fetchToyyibpayPaidAt($billcode, $transactionId = null)
    {
        try {
            $service = new ToyyibpayService();
            $txns = $service->getBillTransactions($billcode);
            if (is_array($txns)) {
                $match = null;
                foreach ($txns as $t) {
                    if (($t['billpaymentStatus'] ?? null) == '1') {
                        if ($transactionId && ($t['billpaymentInvoiceNo'] ?? null) == $transactionId) {
                            $match = $t;
                            break;
                        }
                        if (!$match) {
                            $match = $t;
                        }
                    }
                }
                if ($match && !empty($match['billPaymentDate'])) {
                    return $this->parseToyyibpayDate($match['billPaymentDate']);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('toyyibpay paid_at fetch failed: ' . $e->getMessage(), ['billcode' => $billcode]);
        }
        return now();
    }

    private function toyyibpayApiConfigured(): bool
    {
        return !empty(config('services.toyyibpay.secret_key'))
            && !empty(config('services.toyyibpay.category_code'));
    }

    /**
     * Notify the team (contact email + the manager who registered) that their
     * registration payment has been confirmed. Failures never block the flow.
     */
    private function sendPaymentConfirmation(RegistrationPayment $payment): void
    {
        try {
            $payment->loadMissing(['team', 'competition', 'user']);

            $recipients = [];
            if (!empty($payment->team->contact_email)) {
                $recipients[] = $payment->team->contact_email;
            }
            if (!empty($payment->user->email)) {
                $recipients[] = $payment->user->email;
            }
            $recipients = array_values(array_unique(array_filter($recipients)));

            if (!empty($recipients)) {
                Mail::to($recipients)->send(new PaymentConfirmedMail($payment));
            }
        } catch (\Throwable $e) {
            Log::error('Payment confirmation email failed: ' . $e->getMessage(), ['payment' => $payment->id]);
        }
    }
}
