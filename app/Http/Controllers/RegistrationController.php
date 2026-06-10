<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Group;
use App\Models\RegistrationPayment;
use App\Models\Team;
use App\Services\ToyyibpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('registration.create', compact('competition', 'groups'));
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
            'terms_agreed' => ['required', 'accepted'],
        ]);

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

        $fee = $competition->registration_fee ?? 0;

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
                $payment->update([
                    'status' => 'paid',
                    'transaction_id' => $transactionId,
                    'paid_at' => now(),
                ]);

                return redirect()->route('registration.success')
                    ->with('team_name', $payment->team->name ?? 'Your team')
                    ->with('competition_name', $payment->competition->name ?? '')
                    ->with('payment_status', 'paid');
            }

            $payment->update([
                'status' => 'failed',
                'transaction_id' => $transactionId,
            ]);

            return redirect()->route('registration.success')
                ->with('team_name', $payment->team->name ?? 'Your team')
                ->with('competition_name', $payment->competition->name ?? '')
                ->with('payment_status', 'failed');
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
            $payment->update([
                'status' => 'paid',
                'transaction_id' => $transactionId,
                'paid_at' => now(),
            ]);
        } elseif ($payment) {
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

    public function adminPayments()
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        $payments = RegistrationPayment::with(['team', 'competition', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('registration.payments', compact('payments'));
    }
}
