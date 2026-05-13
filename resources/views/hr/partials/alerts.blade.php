@if (session('success'))
  <div class="alert alert-success iso-alert">
    <i class="bi bi-check-circle-fill"></i>
    <span>{{ session('success') }}</span>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-danger iso-alert iso-alert-danger">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>{{ session('error') }}</span>
  </div>
@endif
