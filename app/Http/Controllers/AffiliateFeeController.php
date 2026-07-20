<?php

namespace App\Http\Controllers;

use App\Mail\AffiliateFeeReminderMail;
use App\Models\Competition;
use App\Models\RegistrationPayment;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AffiliateFeeController extends Controller
{
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }
    }

    /**
     * Base query: teams that are actually part of a LEAGUE
     * (exclude rejected / withdrawn applications, and exclude
     * knockout competitions like the FA Cup / Sumbangsih Cup where
     * the RM50 annual membership fee does not apply).
     */
    private function membershipQuery()
    {
        return Team::with('competition')
            ->whereNotIn('status', ['rejected', 'withdrawn'])
            ->whereHas('competition', function ($q) {
                $q->where('type', 'league');
            });
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $statusFilter = $request->query('status', 'all');   // all | paid | unpaid | exempt
        $competitionId = $request->query('competition');

        $query = $this->membershipQuery();

        if ($statusFilter === 'paid') {
            $query->where('affiliate_fee_required', true)->where('affiliate_fee_paid', true);
        } elseif ($statusFilter === 'unpaid') {
            $query->where('affiliate_fee_required', true)->where('affiliate_fee_paid', false);
        } elseif ($statusFilter === 'exempt') {
            $query->where('affiliate_fee_required', false);
        }

        if (!empty($competitionId)) {
            $query->where('competition_id', $competitionId);
        }

        $teams = $query->orderBy('affiliate_fee_required', 'desc')
            ->orderBy('affiliate_fee_paid')
            ->orderBy('name')
            ->paginate(300)
            ->withQueryString();

        // Totals cover only the teams that actually OWE the RM50 (required),
        // independent of the current filter view. Exempt teams are counted apart.
        $totalTeams = $this->membershipQuery()->where('affiliate_fee_required', true)->count();
        $paidCount = $this->membershipQuery()->where('affiliate_fee_required', true)->where('affiliate_fee_paid', true)->count();
        $unpaidCount = $totalTeams - $paidCount;
        $exemptCount = $this->membershipQuery()->where('affiliate_fee_required', false)->count();
        $collected = $paidCount * Team::AFFILIATE_FEE;
        $outstanding = $unpaidCount * Team::AFFILIATE_FEE;

        $competitions = Competition::where('type', 'league')->orderBy('name')->get();
        $fee = Team::AFFILIATE_FEE;

        return view('affiliate-fees.index', compact(
            'teams', 'totalTeams', 'paidCount', 'unpaidCount', 'exemptCount',
            'collected', 'outstanding', 'competitions', 'fee',
            'statusFilter', 'competitionId'
        ));
    }

    public function markPaid(Request $request, Team $team)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $team->update([
            'affiliate_fee_paid' => true,
            'affiliate_fee_paid_at' => now(),
            'affiliate_fee_reference' => $validated['reference'] ?? null,
            'affiliate_fee_marked_by' => Auth::user()->name,
        ]);

        return redirect()->back()->with('success', __('app.affiliate_marked_paid', ['team' => $team->name]));
    }

    public function markUnpaid(Team $team)
    {
        $this->authorizeAdmin();

        $team->update([
            'affiliate_fee_paid' => false,
            'affiliate_fee_paid_at' => null,
            'affiliate_fee_reference' => null,
            'affiliate_fee_marked_by' => null,
        ]);

        return redirect()->back()->with('success', __('app.affiliate_marked_unpaid', ['team' => $team->name]));
    }

    /**
     * Bulk mark selected teams paid or unpaid in one action.
     */
    public function bulkMark(Request $request)
    {
        $this->authorizeAdmin();

        $ids = $request->input('team_ids', []);
        $action = $request->input('action');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', __('app.affiliate_none_selected'));
        }
        if (!in_array($action, ['paid', 'unpaid'], true)) {
            return redirect()->back()->with('error', __('app.affiliate_none_selected'));
        }

        $ids = array_map('intval', $ids);

        if ($action === 'paid') {
            Team::whereIn('id', $ids)->update([
                'affiliate_fee_paid' => true,
                'affiliate_fee_paid_at' => now(),
                'affiliate_fee_marked_by' => Auth::user()->name,
            ]);
            $msg = __('app.affiliate_bulk_paid', ['count' => count($ids)]);
        } else {
            Team::whereIn('id', $ids)->update([
                'affiliate_fee_paid' => false,
                'affiliate_fee_paid_at' => null,
                'affiliate_fee_reference' => null,
                'affiliate_fee_marked_by' => null,
            ]);
            $msg = __('app.affiliate_bulk_unpaid', ['count' => count($ids)]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Bulk set whether the selected teams owe the RM50 annual fee.
     * required=1 -> team owes RM50 (total includes RM50).
     * required=0 -> team is exempt (total drops by RM50).
     * Any pending (unpaid) registration payment is recalculated to match.
     */
    public function bulkRequire(Request $request)
    {
        $this->authorizeAdmin();

        $ids = $request->input('team_ids', []);
        $required = $request->input('required');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', __('app.affiliate_none_selected'));
        }
        if (!in_array($required, ['0', '1'], true)) {
            return redirect()->back()->with('error', __('app.affiliate_none_selected'));
        }

        $ids = array_map('intval', $ids);
        $requiredBool = ($required === '1');

        $teams = Team::with('competition')->whereIn('id', $ids)->get();
        foreach ($teams as $team) {
            $team->affiliate_fee_required = $requiredBool;
            $team->save();

            // Keep any not-yet-paid registration payment in sync with the RM50 change.
            $payment = RegistrationPayment::where('team_id', $team->id)->latest()->first();
            if ($payment && $payment->status !== 'paid') {
                $payment->update(['amount' => $team->registrationTotal()]);
            }
        }

        $msg = $requiredBool
            ? __('app.affiliate_bulk_required', ['count' => count($ids)])
            : __('app.affiliate_bulk_exempt', ['count' => count($ids)]);

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Send reminder emails to the selected (unpaid) teams.
     */
    public function bulkRemind(Request $request)
    {
        $this->authorizeAdmin();

        $ids = $request->input('team_ids', []);
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', __('app.affiliate_none_selected'));
        }

        $ids = array_map('intval', $ids);
        $teams = $this->membershipQuery()
            ->whereIn('id', $ids)
            ->where('affiliate_fee_required', true)
            ->where('affiliate_fee_paid', false)
            ->get();

        $sent = 0;
        $skipped = 0;
        foreach ($teams as $team) {
            if ($this->sendReminder($team)) {
                $sent++;
            } else {
                $skipped++;
            }
        }

        return redirect()->back()->with('success', __('app.affiliate_reminder_bulk', ['sent' => $sent, 'skipped' => $skipped]));
    }

    public function remind(Team $team)
    {
        $this->authorizeAdmin();

        if ($team->affiliate_fee_paid) {
            return redirect()->back()->with('error', __('app.affiliate_already_paid'));
        }

        $sent = $this->sendReminder($team);

        if ($sent) {
            return redirect()->back()->with('success', __('app.affiliate_reminder_sent', ['team' => $team->name]));
        }

        return redirect()->back()->with('error', __('app.affiliate_reminder_no_email', ['team' => $team->name]));
    }

    public function remindAll(Request $request)
    {
        $this->authorizeAdmin();

        $competitionId = $request->input('competition');

        $query = $this->membershipQuery()->where('affiliate_fee_required', true)->where('affiliate_fee_paid', false);
        if (!empty($competitionId)) {
            $query->where('competition_id', $competitionId);
        }

        $teams = $query->get();

        $sent = 0;
        $skipped = 0;
        foreach ($teams as $team) {
            if ($this->sendReminder($team)) {
                $sent++;
            } else {
                $skipped++;
            }
        }

        return redirect()->back()->with('success', __('app.affiliate_reminder_bulk', ['sent' => $sent, 'skipped' => $skipped]));
    }

    /**
     * Send a single reminder email. Returns false if the team has no email
     * on file or if sending fails (so the caller can report it).
     */
    private function sendReminder(Team $team): bool
    {
        if (empty($team->contact_email)) {
            return false;
        }

        try {
            Mail::to($team->contact_email)->send(new AffiliateFeeReminderMail($team));
            $team->update(['affiliate_fee_reminded_at' => now()]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Affiliate fee reminder failed for team ' . $team->id . ': ' . $e->getMessage());
            return false;
        }
    }
}
