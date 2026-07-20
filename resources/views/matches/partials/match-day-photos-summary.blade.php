@php
    use App\Models\MatchDayPhoto;

    $mdPhotos = $match->matchDayPhotos->keyBy('category');
    $mdUploaded = collect(MatchDayPhoto::CATEGORIES)->filter(fn($c) => isset($mdPhotos[$c]))->count();
    $mdTotal = count(MatchDayPhoto::CATEGORIES);
    $mdComplete = $mdUploaded >= $mdTotal;
    // Photos are edited by the same rule as the rest of the match report.
    $mdCanEdit = isset($canEditMatch) ? $canEditMatch : (auth()->check() && $match->canEditBy(auth()->user()));

    $mdRows = [
        MatchDayPhoto::CATEGORY_HOME_XI          => __('app.mdp_home_xi'),
        MatchDayPhoto::CATEGORY_AWAY_XI          => __('app.mdp_away_xi'),
        MatchDayPhoto::CATEGORY_REFEREE_CAPTAINS => __('app.mdp_referee_captains'),
    ];
@endphp

<div class="card mb-3 border-{{ $mdComplete ? 'success' : 'warning' }}" id="mdpCard">
    <div class="card-header bg-{{ $mdComplete ? 'success' : 'warning' }} {{ $mdComplete ? 'text-white' : 'text-dark' }} py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#matchDayPhotosPanel" role="button">
        <h6 class="mb-0"><i class="fas fa-camera-retro me-2"></i>{{ __('app.mdp_title') }}</h6>
        <span>
            <span class="badge {{ $mdComplete ? 'bg-light text-success' : 'bg-dark' }}" id="mdpProgressBadge">
                @if($mdComplete){{ __('app.mdp_complete') }}@else {{ $mdUploaded }} / {{ $mdTotal }} @endif
            </span>
            <i class="fas fa-chevron-down ms-1"></i>
        </span>
    </div>
    <div class="collapse show" id="matchDayPhotosPanel" @if($mdComplete) data-autocollapse="1" @endif>
        <div class="card-body py-2">
            <ul class="list-group list-group-flush mb-2">
                @foreach($mdRows as $cat => $label)
                    @php $has = isset($mdPhotos[$cat]); @endphp
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2" id="mdpRow_{{ $cat }}" data-label="{{ $label }}">
                        <span>
                            <i class="fas {{ $has ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }} me-2" data-mdp-icon></i>
                            {{ $label }}
                        </span>
                        <span class="d-flex gap-1" data-mdp-actions>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 {{ $has ? '' : 'd-none' }}" data-mdp-view
                                    onclick="mdpView('{{ $cat }}', @js($label))">
                                <i class="fas fa-eye me-1"></i>{{ __('app.mdp_view') }}
                            </button>
                            @if($mdCanEdit)
                                <button type="button" class="btn btn-sm btn-success py-0" data-mdp-upload
                                        onclick="mdpUpload('{{ $cat }}', @js($label))">
                                    <i class="fas fa-camera me-1"></i><span data-mdp-uplabel>{{ $has ? __('app.mdp_replace') : __('app.mdp_upload') }}</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 {{ $has ? '' : 'd-none' }}" data-mdp-remove
                                        onclick="mdpRemove('{{ $cat }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                            @unless($has)
                                <span class="badge bg-secondary align-self-center {{ $mdCanEdit ? 'd-none' : '' }}" data-mdp-missing>{{ __('app.mdp_not_uploaded') }}</span>
                            @endunless
                        </span>
                    </li>
                @endforeach
            </ul>
            <span class="text-muted small"><i class="fas fa-lock me-1"></i>{{ __('app.mdp_private_note') }}</span>
        </div>
    </div>
</div>

{{-- Upload modal (shared; category set on open) --}}
@if($mdCanEdit)
<div class="modal fade" id="mdpUploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="mdpUploadForm" enctype="multipart/form-data">
                <div class="modal-header py-2 bg-success text-white">
                    <h6 class="modal-title"><i class="fas fa-camera me-2"></i>{{ __('app.mdp_upload') }}</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category" id="mdpUploadCategory">
                    <div class="mb-2">
                        <label class="form-label small fw-bold mb-1">{{ __('app.mdp_photo_type') }}</label>
                        <div class="fw-bold" id="mdpUploadLabel">-</div>
                    </div>
                    <div class="mb-2">
                        <input type="file" name="photo" id="mdpUploadFile" class="form-control form-control-sm" accept="image/*" capture="environment" required>
                    </div>
                    <div class="text-center">
                        <img id="mdpUploadPreview" src="" alt="" class="img-fluid rounded border d-none" style="max-height:220px;">
                    </div>
                    <div class="alert alert-danger py-1 px-2 small mt-2 d-none" id="mdpUploadError"></div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-success btn-sm" id="mdpUploadSubmit">
                        <i class="fas fa-upload me-1"></i>{{ __('app.mdp_upload') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- View modal (shared) --}}
<div class="modal fade" id="mdpViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2 bg-dark text-white">
                <h6 class="modal-title"><i class="fas fa-image me-2"></i><span id="mdpViewLabel">-</span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="mdpViewImg" src="" alt="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var matchId = {{ $match->id }};
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var uploadUrl = "{{ route('match-photos.upload', $match) }}";
    function fileUrl(cat) { return "{{ url('matches/'.$match->id.'/photos') }}/" + cat + "/file?t=" + Date.now(); }
    function destroyUrl(cat) { return "{{ url('matches/'.$match->id.'/photos') }}/" + cat; }

    window.mdpView = function (cat, label) {
        document.getElementById('mdpViewLabel').textContent = label;
        document.getElementById('mdpViewImg').src = fileUrl(cat);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('mdpViewModal')).show();
    };

    var uploadModalEl = document.getElementById('mdpUploadModal');
    window.mdpUpload = function (cat, label) {
        if (!uploadModalEl) return;
        document.getElementById('mdpUploadCategory').value = cat;
        document.getElementById('mdpUploadLabel').textContent = label;
        document.getElementById('mdpUploadFile').value = '';
        var prev = document.getElementById('mdpUploadPreview');
        prev.src = ''; prev.classList.add('d-none');
        document.getElementById('mdpUploadError').classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(uploadModalEl).show();
    };

    var fileInput = document.getElementById('mdpUploadFile');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var prev = document.getElementById('mdpUploadPreview');
            if (this.files && this.files[0]) {
                prev.src = URL.createObjectURL(this.files[0]);
                prev.classList.remove('d-none');
            } else {
                prev.classList.add('d-none');
            }
        });
    }

    function setRowUploaded(cat) {
        var row = document.getElementById('mdpRow_' + cat);
        if (!row) return;
        row.querySelector('[data-mdp-icon]').className = 'fas fa-check-circle text-success me-2';
        var view = row.querySelector('[data-mdp-view]'); if (view) view.classList.remove('d-none');
        var rm = row.querySelector('[data-mdp-remove]'); if (rm) rm.classList.remove('d-none');
        var miss = row.querySelector('[data-mdp-missing]'); if (miss) miss.classList.add('d-none');
        var upl = row.querySelector('[data-mdp-uplabel]'); if (upl) upl.textContent = @json(__('app.mdp_replace'));
    }
    function setRowEmpty(cat) {
        var row = document.getElementById('mdpRow_' + cat);
        if (!row) return;
        row.querySelector('[data-mdp-icon]').className = 'fas fa-times-circle text-muted me-2';
        var view = row.querySelector('[data-mdp-view]'); if (view) view.classList.add('d-none');
        var rm = row.querySelector('[data-mdp-remove]'); if (rm) rm.classList.add('d-none');
        var upl = row.querySelector('[data-mdp-uplabel]'); if (upl) upl.textContent = @json(__('app.mdp_upload'));
    }
    function updateProgress(uploaded, total, complete) {
        var badge = document.getElementById('mdpProgressBadge');
        if (!badge) return;
        badge.textContent = complete ? @json(__('app.mdp_complete')) : (uploaded + ' / ' + total);
        var card = document.getElementById('mdpCard');
        card.className = 'card mb-3 border-' + (complete ? 'success' : 'warning');
    }

    var form = document.getElementById('mdpUploadForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('mdpUploadSubmit');
            var err = document.getElementById('mdpUploadError');
            err.classList.add('d-none');
            btn.disabled = true;
            var cat = document.getElementById('mdpUploadCategory').value;
            var fd = new FormData(form);
            fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: fd
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
              .then(function (res) {
                  btn.disabled = false;
                  if (!res.ok || !res.j.ok) {
                      err.textContent = res.j.message || 'Upload failed. Please use an image under 10MB.';
                      err.classList.remove('d-none');
                      return;
                  }
                  setRowUploaded(cat);
                  updateProgress(res.j.uploaded, res.j.total, res.j.complete);
                  bootstrap.Modal.getInstance(uploadModalEl).hide();
              }).catch(function () {
                  btn.disabled = false;
                  err.textContent = 'Network error. Please try again.';
                  err.classList.remove('d-none');
              });
        });
    }

    window.mdpRemove = function (cat) {
        if (!confirm(@json(__('app.mdp_confirm_remove')))) return;
        fetch(destroyUrl(cat), {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: new URLSearchParams({ _method: 'DELETE' })
        }).then(function (r) { return r.json(); })
          .then(function (j) {
              if (j.ok) { setRowEmpty(cat); updateProgress(j.uploaded, j.total, j.complete); }
          });
    };
})();
</script>
@endpush
