@php
    $official = $match->isOfficialData();
    $statusLabel = ucfirst(str_replace('_', ' ', $match->status));
@endphp
<div class="modal fade" id="deleteMatchModal{{ $match->id }}" tabindex="-1"
     aria-labelledby="deleteMatchModalLabel{{ $match->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteMatchModalLabel{{ $match->id }}">
                    <i class="fas fa-triangle-exclamation me-2"></i>{{ __('app.delete_this_match') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body text-start">
                <p class="mb-2">{{ __('app.delete_match_intro') }}</p>

                {{-- Match details --}}
                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between"><span class="text-muted">{{ __('app.match_code') }}</span><strong>{{ $match->match_code ?? '-' }}</strong></div>
                    <div class="fw-bold my-1">{{ $match->homeTeam->name ?? '-' }} <span class="text-muted">vs</span> {{ $match->awayTeam->name ?? '-' }}</div>
                    <div class="d-flex justify-content-between"><span class="text-muted">{{ __('app.competition') }}</span><span>{{ $match->competition->name ?? '-' }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">{{ __('app.date') }}</span><span>{{ optional($match->match_date)->format('d M Y, g:i A') ?? '-' }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">{{ __('app.status') }}</span><span>{{ $statusLabel }}</span></div>
                </div>

                {{-- Reason for deletion / archive --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="reason{{ $match->id }}">{{ __('app.reason_for_deletion') }}</label>
                    <select class="form-select js-reason-select" id="reason{{ $match->id }}" data-note-target="reasonNote{{ $match->id }}">
                        <option value="Duplicate match">{{ __('app.reason_duplicate') }}</option>
                        <option value="Test match">{{ __('app.reason_test') }}</option>
                        <option value="Wrong fixture">{{ __('app.reason_wrong_fixture') }}</option>
                        <option value="Other">{{ __('app.reason_other') }}</option>
                    </select>
                    <input type="text" class="form-control mt-2 d-none" id="reasonNote{{ $match->id }}"
                           placeholder="{{ __('app.reason_note_placeholder') }}" maxlength="255">
                </div>

                {{-- Extra protection for official / played matches --}}
                @if($official)
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-triangle-exclamation me-1"></i>{{ __('app.official_data_warning_title') }}</strong>
                        <div class="small mt-1">{{ __('app.official_data_warning_body') }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-danger" for="confirmText{{ $match->id }}">
                            {{ __('app.type_delete_to_confirm') }}
                        </label>
                        <input type="text" class="form-control js-confirm-input" id="confirmText{{ $match->id }}"
                               data-delete-btn="permDeleteBtn{{ $match->id }}" autocomplete="off" placeholder="DELETE">
                    </div>
                @endif
            </div>

            <div class="modal-footer flex-wrap">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>

                {{-- Archive (safer option) --}}
                <form action="{{ route('matches.archive', $match) }}" method="POST" class="d-inline js-match-action-form" data-reason-select="reason{{ $match->id }}" data-reason-note="reasonNote{{ $match->id }}">
                    @csrf
                    <input type="hidden" name="reason" value="">
                    <input type="hidden" name="reason_note" value="">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-box-archive me-1"></i>{{ __('app.archive_match') }}
                    </button>
                </form>

                {{-- Permanent delete --}}
                <form action="{{ route('matches.destroy', $match) }}" method="POST" class="d-inline js-match-action-form"
                      data-reason-select="reason{{ $match->id }}" data-reason-note="reasonNote{{ $match->id }}"
                      data-confirm-input="{{ $official ? 'confirmText'.$match->id : '' }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="reason" value="">
                    <input type="hidden" name="reason_note" value="">
                    <input type="hidden" name="confirm_text" value="">
                    <button type="submit" id="permDeleteBtn{{ $match->id }}" class="btn btn-danger" {{ $official ? 'disabled' : '' }}>
                        <i class="fas fa-trash me-1"></i>{{ __('app.delete_permanently') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
