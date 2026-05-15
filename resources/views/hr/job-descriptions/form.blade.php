@extends('layouts.app')

@section('title', $jobDescription->exists ? 'Edit Jobdesk' : 'Tambah Jobdesk')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $jobDescription->exists ? 'Edit Jobdesk' : 'Tambah Jobdesk' }}</h1>
      <p class="page-subtitle">Pilih divisi lebih dulu agar daftar jabatan sesuai dengan divisi tersebut.</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('hr.job-descriptions.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <form method="POST" action="{{ $jobDescription->exists ? route('hr.job-descriptions.update', $jobDescription) : route('hr.job-descriptions.store') }}">
      @csrf
      @if ($jobDescription->exists)
        @method('PUT')
      @endif

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Divisi <span class="required">*</span></label>
            <select class="form-select @error('division_id') is-invalid @enderror" name="division_id" id="jobdesk_division_id">
              <option value="">Pilih Divisi</option>
              @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected(old('division_id', $jobDescription->division_id) == $division->id)>{{ $division->name }}</option>
              @endforeach
            </select>
            @error('division_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Jabatan <span class="required">*</span></label>
            <select class="form-select @error('position_id') is-invalid @enderror" name="position_id" id="jobdesk_position_id" data-selected="{{ old('position_id', $jobDescription->position_id) }}">
              <option value="">Pilih Divisi terlebih dahulu</option>
              @foreach ($positions as $position)
                <option value="{{ $position->id }}" data-division="{{ $position->division_id }}" @selected(old('position_id', $jobDescription->position_id) == $position->id)>{{ $position->name }}</option>
              @endforeach
            </select>
            @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Judul <span class="required">*</span></label>
            <input class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $jobDescription->title) }}" required>
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Supervisor Langsung</label>
            <select class="form-select @error('direct_supervisor_id') is-invalid @enderror" name="direct_supervisor_id">
              <option value="">Pilih</option>
              @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('direct_supervisor_id', $jobDescription->direct_supervisor_id) == $employee->id)>{{ $employee->nama }}</option>
              @endforeach
            </select>
            @error('direct_supervisor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Status <span class="required">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" name="status">
              @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $jobDescription->status ?? 'aktif') === $status)>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group form-group-full">
            <label class="form-label">Deskripsi <span class="required">*</span></label>
            <textarea class="form-textarea @error('description') is-invalid @enderror" name="description" required>{{ old('description', $jobDescription->description) }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group form-group-full">
            <label class="form-label">Target Kerja</label>
            <textarea class="form-textarea @error('target_work') is-invalid @enderror" name="target_work">{{ old('target_work', $jobDescription->target_work) }}</textarea>
            @error('target_work') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <a class="btn btn-outline" href="{{ route('hr.job-descriptions.index') }}">Batal</a>
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Simpan</button>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    (function ($) {
      function syncJobdeskPositions() {
        var divisionId = $('#jobdesk_division_id').val();
        var $position = $('#jobdesk_position_id');
        var selected = $position.data('selected') ? String($position.data('selected')) : '';
        var hasVisibleOption = false;

        $position.find('option').each(function () {
          var $option = $(this);
          var optionDivision = String($option.data('division') || '');
          var isPlaceholder = ! $option.val();
          var shouldShow = isPlaceholder || (divisionId && optionDivision === String(divisionId));

          $option.prop('hidden', ! shouldShow).prop('disabled', ! shouldShow && ! isPlaceholder);

          if (! isPlaceholder && shouldShow) {
            hasVisibleOption = true;
          }
        });

        if (! divisionId) {
          $position.val('');
          $position.find('option:first').text('Pilih Divisi terlebih dahulu');
          return;
        }

        $position.find('option:first').text(hasVisibleOption ? 'Pilih Jabatan' : 'Belum ada jabatan di divisi ini');

        var selectedOption = $position.find('option[value="' + selected + '"]');
        if (selected && selectedOption.length && String(selectedOption.data('division')) === String(divisionId)) {
          $position.val(selected);
        } else if ($position.find('option:selected').prop('hidden')) {
          $position.val('');
        }
      }

      $('#jobdesk_division_id').on('change', function () {
        $('#jobdesk_position_id').data('selected', '');
        syncJobdeskPositions();
      });

      syncJobdeskPositions();
    })(jQuery);
  </script>
@endpush
