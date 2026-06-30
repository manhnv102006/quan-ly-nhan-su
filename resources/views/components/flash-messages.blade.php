@if(session('success'))
    <div id="flash-success-toast"
         class="position-fixed top-0 end-0 p-3 z-3"
         style="margin-top: 5.5rem;">
        <div class="toast align-items-center text-bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div id="flash-error-toast"
         class="position-fixed top-0 end-0 p-3 z-3"
         style="margin-top: 5.5rem;">
        <div class="toast align-items-center text-bg-danger border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success') || session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('#flash-success-toast .toast, #flash-error-toast .toast').forEach(function (toastEl) {
                if (window.bootstrap) {
                    const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
                    toast.show();
                    toastEl.addEventListener('hidden.bs.toast', function () {
                        toastEl.closest('#flash-success-toast, #flash-error-toast')?.remove();
                    });
                }
            });
        });
    </script>
@endif
