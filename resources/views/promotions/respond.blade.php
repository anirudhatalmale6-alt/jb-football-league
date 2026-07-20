@extends('layouts.app')

@section('title', 'Tawaran Kenaikan Pangkat / Promotion Offer')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="text-center mb-4">
                <span class="badge bg-success fs-6 px-4 py-2">
                    <i class="fas fa-trophy me-2"></i>TAWARAN KENAIKAN PANGKAT / PROMOTION OFFER
                </span>
            </div>

            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-scroll me-2"></i>Surat Tawaran / Offer Letter</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light p-4 rounded mb-3">
                        <p class="mb-3"><strong>Tawaran Khas Menyertai {{ $offer->toCompetition->malayName() }} 2026.</strong></p>
                        <p class="mb-3">Mesyuarat Jawatankuasa Liga JBFA 2026 memutuskan untuk menawarkan pasukan anda bertanding dalam {{ $offer->toCompetition->malayName() }}.</p>
                        <p class="mb-0">Pasukan anda diminta untuk mengemukakan persetujuan dengan menyediakan keperluan berikut:</p>
                    </div>

                    <table class="table table-bordered mb-3">
                        <tr>
                            <td class="fw-bold bg-light" style="width: 40%;">Pasukan / Team</td>
                            <td>{{ $offer->team->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold bg-light">Liga Semasa / Current League</td>
                            <td>{{ $offer->fromCompetition->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold bg-light">Liga Baharu / New League</td>
                            <td><strong class="text-success">{{ $offer->toCompetition->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold bg-light">Yuran / Fee</td>
                            <td><strong class="text-primary">RM {{ number_format($newFee, 2) }}</strong></td>
                        </tr>
                    </table>

                    @php
                        $hoursLeft = (int) now()->diffInHours($offer->expires_at, false);
                        $minutesLeft = (int) now()->diffInMinutes($offer->expires_at, false);
                    @endphp

                    @if($hoursLeft > 0)
                        <div class="alert alert-warning text-center mb-0">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Tempoh Tamat / Deadline:</strong> {{ $offer->expires_at->format('d M Y, h:i A') }}
                            <br>
                            <span class="text-danger fw-bold">
                                @if($hoursLeft > 1)
                                    {{ $hoursLeft }} jam lagi / {{ $hoursLeft }} hours remaining
                                @else
                                    {{ $minutesLeft }} minit lagi / {{ $minutesLeft }} minutes remaining
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="alert alert-danger text-center mb-0">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Tawaran ini telah tamat tempoh / This offer has expired.</strong>
                        </div>
                    @endif
                </div>
            </div>

            @if($hoursLeft > 0)
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Terima Tawaran / Accept Offer</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('promotions.accept', $offer) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if($offer->to_competition_id === 2)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Pasukan anda telah mempunyai Lesen Kejurulatihan C &amp; padang sendiri dalam rekod.
                            Anda hanya perlu bersetuju dengan yuran baharu dan tekan <strong>Terima Tawaran</strong>.
                            <br><small class="text-muted">Your team already has a C licence &amp; its own field on file. Just agree to the new fee and press Accept.</small>
                        </div>
                        @else
                        <div class="mb-3">
                            <label for="venue_name" class="form-label fw-bold">
                                <i class="fas fa-stadium me-1"></i> Nama Padang / Venue Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('venue_name') is-invalid @enderror"
                                   id="venue_name" name="venue_name"
                                   value="{{ old('venue_name', $offer->team->venue_name) }}"
                                   placeholder="Contoh: Stadium Tan Sri Dato' Haji Hassan Yunos"
                                   required>
                            @error('venue_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="venue_address" class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt me-1"></i> Alamat Padang / Venue Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('venue_address') is-invalid @enderror"
                                      id="venue_address" name="venue_address"
                                      rows="3" placeholder="Alamat penuh padang..."
                                      required>{{ old('venue_address', $offer->team->venue_location) }}</textarea>
                            @error('venue_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="coaching_license" class="form-label fw-bold">
                                <i class="fas fa-id-card me-1"></i> Lesen Kejurulatihan C AFC/FAM <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('coaching_license') is-invalid @enderror"
                                   id="coaching_license" name="coaching_license"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required>
                            <div class="form-text">Muat naik salinan lesen (PDF, JPG, PNG - maks 5MB)</div>
                            @error('coaching_license')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error('fee_agreed') is-invalid @enderror"
                                       type="checkbox" id="fee_agreed" name="fee_agreed" value="1" required>
                                <label class="form-check-label fw-bold" for="fee_agreed">
                                    Saya bersetuju membayar yuran {{ $offer->toCompetition->malayName() }} RM {{ number_format($newFee, 2) }}
                                    <br><small class="text-muted">I agree to pay the {{ $offer->toCompetition->name }} fee of RM {{ number_format($newFee, 2) }}</small>
                                </label>
                                @error('fee_agreed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg"
                                    onclick="return confirm('Adakah anda pasti mahu menerima tawaran ini? / Are you sure you want to accept this offer?')">
                                <i class="fas fa-check me-2"></i>Terima Tawaran / Accept Offer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @unless($offer->accept_only)
            <div class="card border-danger mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Tolak Tawaran / Decline Offer</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded mb-3 border-start border-danger border-4">
                        <p class="mb-0 fst-italic text-dark">
                            &ldquo;Pasukan kami tidak dapat memenuhi syarat yang ditetapkan bagi kenaikan ini &amp; memilih untuk kekal dalam {{ $offer->fromCompetition->malayName() }} sahaja.&rdquo;
                        </p>
                    </div>
                    <p class="text-muted small mb-3 text-center">Dengan menolak, pasukan anda akan kekal dalam {{ $offer->fromCompetition->malayName() }} 2026.</p>
                    <form action="{{ route('promotions.decline', $offer) }}" method="POST" class="text-center">
                        @csrf
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Adakah anda pasti mahu menolak tawaran ini? Pasukan anda akan kekal dalam Liga Divisyen.')">
                            <i class="fas fa-times me-2"></i>Tolak Tawaran / Decline Offer
                        </button>
                    </form>
                </div>
            </div>
            @endunless
            @endif

            <div class="text-center">
                <a href="{{ route('promotions.letter', $offer) }}" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-file-pdf me-2"></i>Muat Turun Surat / Download Letter (PDF)
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
