<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\PromotionOffer;
use App\Models\RegistrationPayment;
use App\Models\Team;
use App\Models\TeamStatusLog;
use App\Mail\PromotionOfferMail;
use App\Mail\RelegationMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PromotionController extends Controller
{
    /**
     * Promotion ladder: current competition id => next competition id.
     * 4 (Division) -> 3 (Premier), 3 (Premier) -> 2 (Super League).
     */
    private const PROMOTION_MAP = [
        4 => 3, // Division League -> Premier League
        3 => 2, // Premier League  -> Super League
    ];

    public function create($teamId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $team = Team::with('competition')->findOrFail($teamId);

        if (!array_key_exists($team->competition_id, self::PROMOTION_MAP)) {
            return redirect()->route('teams.show', $team->id)
                ->with('error', 'Only Division League or Premier League teams can be promoted.');
        }

        $existingOffer = PromotionOffer::where('team_id', $team->id)
            ->where('status', 'pending')
            ->first();

        if ($existingOffer) {
            return redirect()->route('teams.show', $team->id)
                ->with('error', 'This team already has a pending promotion offer.');
        }

        $toCompetition = Competition::find(self::PROMOTION_MAP[$team->competition_id]);
        $annual = ($team->affiliate_fee_required && $toCompetition->type === 'league') ? Team::AFFILIATE_FEE : 0.0;
        $newFee = $toCompetition->baseFee() + $annual;

        return view('promotions.create', compact('team', 'toCompetition', 'newFee'));
    }

    public function store(Request $request, $teamId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $team = Team::with('competition')->findOrFail($teamId);

        if (!array_key_exists($team->competition_id, self::PROMOTION_MAP)) {
            return redirect()->route('teams.show', $team->id)
                ->with('error', 'Only Division League or Premier League teams can be promoted.');
        }

        $toCompetitionId = self::PROMOTION_MAP[$team->competition_id];

        $offer = PromotionOffer::create([
            'team_id' => $team->id,
            'type' => 'promotion',
            'from_competition_id' => $team->competition_id,
            'to_competition_id' => $toCompetitionId,
            'offered_by' => Auth::id(),
            'status' => 'pending',
            'offered_at' => now(),
            'expires_at' => now()->addHours(48),
        ]);

        TeamStatusLog::create([
            'team_id' => $team->id,
            'changed_by' => Auth::id(),
            'old_status' => $team->status,
            'new_status' => $team->status,
            'reason' => 'Promotion offer sent to ' . (Competition::find($toCompetitionId)->name ?? 'next league') . ' (48h deadline)',
        ]);

        try {
            $email = $team->contact_email;
            if ($email) {
                Mail::to($email)->send(new PromotionOfferMail($team, $offer));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send promotion offer email for team ' . $team->id . ': ' . $e->getMessage());
        }

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Promotion offer sent to ' . $team->name . '. Team has 48 hours to respond.');
    }

    public function respond($offerId)
    {
        $offer = PromotionOffer::with(['team', 'fromCompetition', 'toCompetition'])->findOrFail($offerId);
        $user = Auth::user();

        if (!$user->managesTeam($offer->team_id) && !$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        if ($offer->status === 'pending' && $offer->isExpired()) {
            $offer->update(['status' => 'expired']);
            TeamStatusLog::create([
                'team_id' => $offer->team_id,
                'changed_by' => null,
                'old_status' => $offer->team->status,
                'new_status' => $offer->team->status,
                'reason' => 'Tawaran kenaikan pangkat ke Liga Perdana tamat tempoh (48 jam)',
            ]);
            return redirect()->route('teams.show', $offer->team_id)
                ->with('error', 'Tawaran ini telah tamat tempoh (48 jam). / This promotion offer has expired.');
        }

        if ($offer->status !== 'pending') {
            return redirect()->route('teams.show', $offer->team_id)
                ->with('info', 'This promotion offer has already been ' . $offer->status . '.');
        }

        $toComp = $offer->toCompetition;
        $annual = ($offer->team && $offer->team->affiliate_fee_required && $toComp && $toComp->type === 'league') ? Team::AFFILIATE_FEE : 0.0;
        $newFee = $toComp ? $toComp->baseFee() + $annual : 0;

        return view('promotions.respond', compact('offer', 'newFee'));
    }

    public function accept(Request $request, $offerId)
    {
        $offer = PromotionOffer::with(['team.competition', 'fromCompetition', 'toCompetition'])->findOrFail($offerId);
        $user = Auth::user();

        if (!$user->managesTeam($offer->team_id) && !$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        if ($offer->status !== 'pending') {
            return redirect()->route('teams.show', $offer->team_id)
                ->with('error', 'This offer is no longer pending.');
        }

        if ($offer->isExpired()) {
            $offer->update(['status' => 'expired']);
            TeamStatusLog::create([
                'team_id' => $offer->team_id,
                'changed_by' => null,
                'old_status' => $offer->team->status,
                'new_status' => $offer->team->status,
                'reason' => 'Tawaran kenaikan pangkat ke Liga Perdana tamat tempoh (48 jam)',
            ]);
            return redirect()->route('teams.show', $offer->team_id)
                ->with('error', 'Tawaran ini telah tamat tempoh (48 jam). / This promotion offer has expired.');
        }

        $team = $offer->team;
        $toCompetitionId = $offer->to_competition_id;
        $toCompetition = Competition::find($toCompetitionId);

        // Promotion to Super League: the team already holds a C-licence and its own
        // field on record, so venue/licence are optional - they only confirm the fee.
        $toSuperLeague = $toCompetitionId === 2;

        $rules = [
            'fee_agreed' => ['required', 'accepted'],
        ];
        if ($toSuperLeague) {
            $rules['venue_name'] = ['nullable', 'string', 'max:255'];
            $rules['venue_address'] = ['nullable', 'string', 'max:500'];
            $rules['coaching_license'] = ['nullable', 'file', 'max:5120'];
        } else {
            $rules['venue_name'] = ['required', 'string', 'max:255'];
            $rules['venue_address'] = ['required', 'string', 'max:500'];
            $rules['coaching_license'] = ['required', 'file', 'max:5120'];
        }
        $request->validate($rules);

        // Keep existing licence/venue on file when nothing new is uploaded.
        $licensePath = $offer->coaching_license;
        if ($request->hasFile('coaching_license')) {
            $licensePath = $request->file('coaching_license')->store('coaching-licenses', 'public');
        }

        $venueName = $request->filled('venue_name') ? $request->venue_name : $team->venue_name;
        $venueAddress = $request->filled('venue_address') ? $request->venue_address : $team->venue_location;

        $offer->update([
            'status' => 'accepted',
            'venue_name' => $venueName,
            'venue_address' => $venueAddress,
            'coaching_license' => $licensePath,
            'fee_agreed' => true,
            'responded_at' => now(),
        ]);

        $fromName = $offer->fromCompetition?->name ?? $team->competition?->name ?? 'previous league';

        $team->update([
            'competition_id' => $toCompetitionId,
            'venue_name' => $venueName,
            'venue_location' => $venueAddress,
            'group_id' => null,
        ]);

        TeamStatusLog::create([
            'team_id' => $team->id,
            'changed_by' => Auth::id(),
            'old_status' => $team->status,
            'new_status' => $team->status,
            'reason' => 'Promotion accepted - moved from ' . $fromName . ' to ' . $toCompetition->name,
        ]);

        $annual = ($team->affiliate_fee_required && $toCompetition->type === 'league') ? Team::AFFILIATE_FEE : 0.0;
        $totalFee = $toCompetition->baseFee() + $annual;

        $existingPayment = RegistrationPayment::where('team_id', $team->id)->latest()->first();
        if ($existingPayment) {
            $existingPayment->update([
                'competition_id' => $toCompetitionId,
                'amount' => $totalFee,
                'status' => 'pending',
                'paid_at' => null,
            ]);
        } else {
            RegistrationPayment::create([
                'team_id' => $team->id,
                'competition_id' => $toCompetitionId,
                'user_id' => Auth::id(),
                'amount' => $totalFee,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'Tahniah! Pasukan anda telah berjaya dipromosikan ke ' . $toCompetition->malayName() . ' 2026.');
    }

    public function decline(Request $request, $offerId)
    {
        $offer = PromotionOffer::with(['team'])->findOrFail($offerId);
        $user = Auth::user();

        if (!$user->managesTeam($offer->team_id) && !$user->isSuper() && !$user->isLeagueAdmin()) {
            abort(403);
        }

        if ($offer->status !== 'pending') {
            return redirect()->route('teams.show', $offer->team_id)
                ->with('error', 'This offer is no longer pending.');
        }

        // Some offers are accept-only: the team is not permitted to decline.
        if ($offer->accept_only) {
            return redirect()->route('promotions.respond', $offer->id)
                ->with('error', 'Tawaran ini hanya boleh diterima. / This offer can only be accepted.');
        }

        $offer->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);

        TeamStatusLog::create([
            'team_id' => $offer->team_id,
            'changed_by' => Auth::id(),
            'old_status' => $offer->team->status,
            'new_status' => $offer->team->status,
            'reason' => 'Tawaran kenaikan pangkat ditolak: Pasukan kami tidak dapat memenuhi syarat yang ditetapkan bagi kenaikan ini & memilih untuk kekal dalam Liga Divisyen sahaja.',
        ]);

        return redirect()->route('teams.show', $offer->team_id)
            ->with('info', 'Tawaran kenaikan pangkat telah ditolak. Pasukan kekal dalam Liga Divisyen.');
    }

    public function letter($offerId)
    {
        $offer = PromotionOffer::with(['team.competition', 'fromCompetition', 'toCompetition'])->findOrFail($offerId);
        $user = Auth::user();

        if (!$user->isSuper() && !$user->isLeagueAdmin() && !$user->managesTeam($offer->team_id)) {
            abort(403);
        }

        $team = $offer->team;

        $jbfaLogoBase64 = null;
        $jbfaLogoPath = public_path('images/jbfa_logo.png');
        if (file_exists($jbfaLogoPath)) {
            $jbfaLogoBase64 = 'data:' . mime_content_type($jbfaLogoPath) . ';base64,' . base64_encode(file_get_contents($jbfaLogoPath));
        }

        $view = $offer->type === 'relegation' ? 'pdf.relegation-letter' : 'pdf.promotion-letter';
        $prefix = $offer->type === 'relegation' ? 'JBFA-RL-' : 'JBFA-PL-';
        $filename = $offer->type === 'relegation' ? 'relegation-letter-' : 'promotion-letter-';

        $pdf = Pdf::loadView($view, compact('team', 'offer', 'jbfaLogoBase64'));
        $pdf->setPaper('a4', 'portrait');

        $refNo = $prefix . str_pad($offer->id, 6, '0', STR_PAD_LEFT);

        return $pdf->download($filename . $refNo . '.pdf');
    }

    public function index()
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        PromotionOffer::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $offers = PromotionOffer::with(['team', 'fromCompetition', 'toCompetition', 'offeredByUser'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('promotions.index', compact('offers'));
    }

    // --- Relegation Methods ---

    public function createRelegation($teamId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $team = Team::with('competition')->findOrFail($teamId);

        if ($team->competition_id !== 2) {
            return redirect()->route('teams.show', $team->id)
                ->with('error', 'Only Super League teams can be relegated.');
        }

        $premierLeague = Competition::find(3);

        return view('promotions.relegate-create', compact('team', 'premierLeague'));
    }

    public function storeRelegation(Request $request, $teamId)
    {
        if (!Auth::user()->isSuper() && !Auth::user()->isLeagueAdmin()) {
            abort(403);
        }

        $team = Team::with('competition')->findOrFail($teamId);

        if ($team->competition_id !== 2) {
            return redirect()->route('teams.show', $team->id)
                ->with('error', 'Only Super League teams can be relegated.');
        }

        $offer = PromotionOffer::create([
            'team_id' => $team->id,
            'type' => 'relegation',
            'from_competition_id' => 2,
            'to_competition_id' => 3,
            'offered_by' => Auth::id(),
            'status' => 'completed',
            'offered_at' => now(),
            'expires_at' => now(),
            'responded_at' => now(),
        ]);

        $team->update([
            'competition_id' => 3,
            'group_id' => null,
        ]);

        TeamStatusLog::create([
            'team_id' => $team->id,
            'changed_by' => Auth::id(),
            'old_status' => $team->status,
            'new_status' => $team->status,
            'reason' => 'Relegated from Super League to Premier League by admin',
        ]);

        $premierLeague = Competition::find(3);
        $annual = ($team->affiliate_fee_required && $premierLeague->type === 'league') ? Team::AFFILIATE_FEE : 0.0;
        $totalFee = $premierLeague->baseFee() + $annual;

        $existingPayment = RegistrationPayment::where('team_id', $team->id)->latest()->first();
        if ($existingPayment) {
            $existingPayment->update([
                'competition_id' => 3,
                'amount' => $totalFee,
                'status' => 'pending',
                'paid_at' => null,
            ]);
        } else {
            RegistrationPayment::create([
                'team_id' => $team->id,
                'competition_id' => 3,
                'user_id' => Auth::id(),
                'amount' => $totalFee,
                'status' => 'pending',
            ]);
        }

        try {
            $email = $team->contact_email;
            if ($email) {
                Mail::to($email)->send(new RelegationMail($team, $offer));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send relegation email for team ' . $team->id . ': ' . $e->getMessage());
        }

        return redirect()->route('teams.show', $team->id)
            ->with('success', $team->name . ' has been relegated to Premier League.');
    }
}
