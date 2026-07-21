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

{{-- Upload modal (shared; category set on open) — Take Photo (live camera) or Upload Photo --}}
@if($mdCanEdit)
<div class="modal fade" id="mdpUploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 bg-success text-white">
                <h6 class="modal-title"><i class="fas fa-camera me-2"></i><span id="mdpUploadLabel">-</span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mdpUploadCategory">
                {{-- Hidden inputs: gallery picker + native-camera fallback --}}
                <input type="file" id="mdpFileGallery" class="d-none" accept="image/*">
                <input type="file" id="mdpFileCamera" class="d-none" accept="image/*" capture="environment">
                <canvas id="mdpCanvas" class="d-none"></canvas>

                {{-- STEP 1: choose source --}}
                <div id="mdpChooseView">
                    <p class="text-muted small mb-3">{{ __('app.mdp_choose_source_hint') }}</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" id="mdpTakePhotoBtn">
                            <i class="fas fa-camera me-2"></i>{{ __('app.mdp_take_photo') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="mdpChooseFileBtn">
                            <i class="fas fa-folder-open me-2"></i>{{ __('app.mdp_choose_file') }}
                        </button>
                    </div>
                </div>

                {{-- STEP 2: live camera --}}
                <div id="mdpCameraView" class="d-none text-center">
                    <div class="ratio ratio-4x3 bg-dark rounded overflow-hidden mb-2">
                        <video id="mdpVideo" playsinline muted autoplay style="object-fit:cover;width:100%;height:100%;"></video>
                    </div>
                    <div id="mdpCameraStatus" class="text-muted small mb-2">{{ __('app.mdp_camera_starting') }}</div>
                    <button type="button" class="btn btn-success" id="mdpCaptureBtn" disabled>
                        <i class="fas fa-circle me-2"></i>{{ __('app.mdp_capture') }}
                    </button>
                </div>

                {{-- STEP 3: preview captured/selected photo --}}
                <div id="mdpPreviewView" class="d-none text-center">
                    <img id="mdpUploadPreview" src="" alt="" class="img-fluid rounded border mb-2" style="max-height:260px;">
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="mdpRetakeBtn">
                            <i class="fas fa-redo me-1"></i>{{ __('app.mdp_retake') }}
                        </button>
                    </div>
                </div>

                <div class="alert alert-danger py-1 px-2 small mt-2 d-none" id="mdpUploadError"></div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="button" class="btn btn-success btn-sm d-none" id="mdpUploadSubmit">
                    <i class="fas fa-check me-1"></i>{{ __('app.mdp_use_photo') }}
                </button>
            </div>
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
    var mdpBlob = null;          // the File/Blob queued for upload
    var mdpStream = null;        // active camera stream, if any

    function $id(id) { return document.getElementById(id); }
    function show(el) { el && el.classList.remove('d-none'); }
    function hide(el) { el && el.classList.add('d-none'); }

    function stopCamera() {
        if (mdpStream) {
            mdpStream.getTracks().forEach(function (t) { t.stop(); });
            mdpStream = null;
        }
        var v = $id('mdpVideo');
        if (v) { v.srcObject = null; }
    }

    function resetModal() {
        stopCamera();
        mdpBlob = null;
        var prev = $id('mdpUploadPreview');
        if (prev) { prev.src = ''; }
        if ($id('mdpFileGallery')) $id('mdpFileGallery').value = '';
        if ($id('mdpFileCamera')) $id('mdpFileCamera').value = '';
        hide($id('mdpCameraView'));
        hide($id('mdpPreviewView'));
        show($id('mdpChooseView'));
        hide($id('mdpUploadSubmit'));
        hide($id('mdpUploadError'));
    }

    // Show the captured/selected image in the preview step.
    function goToPreview(blob) {
        mdpBlob = blob;
        stopCamera();
        var prev = $id('mdpUploadPreview');
        prev.src = URL.createObjectURL(blob);
        hide($id('mdpChooseView'));
        hide($id('mdpCameraView'));
        show($id('mdpPreviewView'));
        show($id('mdpUploadSubmit'));
        hide($id('mdpUploadError'));
    }

    // Open the live camera. Falls back to the native camera input if getUserMedia is unavailable/denied.
    function startCamera() {
        hide($id('mdpChooseView'));
        hide($id('mdpPreviewView'));
        show($id('mdpCameraView'));
        var status = $id('mdpCameraStatus');
        var captureBtn = $id('mdpCaptureBtn');
        captureBtn.disabled = true;
        status.textContent = @json(__('app.mdp_camera_starting'));

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            // No live-camera API — use the OS camera via a file input with capture.
            hide($id('mdpCameraView'));
            show($id('mdpChooseView'));
            $id('mdpFileCamera').click();
            return;
        }

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
            .then(function (stream) {
                mdpStream = stream;
                var v = $id('mdpVideo');
                v.srcObject = stream;
                v.onloadedmetadata = function () {
                    v.play();
                    status.textContent = '';
                    captureBtn.disabled = false;
                };
            })
            .catch(function () {
                // Permission denied or no camera — fall back to native camera input.
                stopCamera();
                hide($id('mdpCameraView'));
                show($id('mdpChooseView'));
                $id('mdpFileCamera').click();
            });
    }

    window.mdpUpload = function (cat, label) {
        if (!uploadModalEl) return;
        $id('mdpUploadCategory').value = cat;
        $id('mdpUploadLabel').textContent = label;
        resetModal();
        bootstrap.Modal.getOrCreateInstance(uploadModalEl).show();
    };

    // Wire up the buttons once.
    if (uploadModalEl) {
        $id('mdpTakePhotoBtn').addEventListener('click', startCamera);
        $id('mdpChooseFileBtn').addEventListener('click', function () { $id('mdpFileGallery').click(); });
        $id('mdpRetakeBtn').addEventListener('click', function () { resetModal(); });

        // Capture a frame from the live video.
        $id('mdpCaptureBtn').addEventListener('click', function () {
            var v = $id('mdpVideo');
            var canvas = $id('mdpCanvas');
            var w = v.videoWidth, h = v.videoHeight;
            if (!w || !h) return;
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(v, 0, 0, w, h);
            canvas.toBlob(function (blob) {
                if (blob) goToPreview(blob);
            }, 'image/jpeg', 0.9);
        });

        // A file chosen from gallery or the native camera goes straight to preview.
        function onFilePicked() {
            if (this.files && this.files[0]) { goToPreview(this.files[0]); }
        }
        $id('mdpFileGallery').addEventListener('change', onFilePicked);
        $id('mdpFileCamera').addEventListener('change', onFilePicked);

        // Always release the camera when the modal closes.
        uploadModalEl.addEventListener('hidden.bs.modal', stopCamera);
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

    var submitBtn = $id('mdpUploadSubmit');
    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            if (!mdpBlob) return;
            var err = $id('mdpUploadError');
            err.classList.add('d-none');
            submitBtn.disabled = true;
            var cat = $id('mdpUploadCategory').value;
            var filename = (mdpBlob.name && mdpBlob.name.indexOf('.') > -1) ? mdpBlob.name : (cat + '.jpg');
            var fd = new FormData();
            fd.append('category', cat);
            fd.append('photo', mdpBlob, filename);
            fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: fd
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
              .then(function (res) {
                  submitBtn.disabled = false;
                  if (!res.ok || !res.j.ok) {
                      err.textContent = res.j.message || 'Upload failed. Please use an image under 10MB.';
                      err.classList.remove('d-none');
                      return;
                  }
                  setRowUploaded(cat);
                  updateProgress(res.j.uploaded, res.j.total, res.j.complete);
                  bootstrap.Modal.getInstance(uploadModalEl).hide();
              }).catch(function () {
                  submitBtn.disabled = false;
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
