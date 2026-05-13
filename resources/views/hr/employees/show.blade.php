@extends('layouts.app')

@section('title', 'Detail Karyawan')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $employee->nama }}</h1>
      <p class="page-subtitle">{{ $employee->position?->name ?? 'Jabatan belum diisi' }} · {{ $employee->division?->name ?? 'Divisi belum diisi' }}</p>
    </div>
    <div class="action-btns">
      <a class="btn btn-outline btn-sm" href="{{ route('hr.employees.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
      <a class="btn btn-primary btn-sm" href="{{ route('hr.employees.edit', $employee) }}"><i class="bi bi-pencil-fill"></i> Edit</a>
    </div>
  </div>

  @include('hr.partials.alerts')

  <div class="iso-detail-grid">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Data Utama</h2></div>
      <div class="modal-body">
        <div class="detail-grid">
          <div class="detail-item"><div class="detail-label">NIK KTP</div><div class="detail-value">{{ $employee->nik_ktp }}</div></div>
          <div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="status-badge status-{{ $employee->status_karyawan }}">{{ ucfirst($employee->status_karyawan) }}</span></div></div>
          <div class="detail-item"><div class="detail-label">TTL</div><div class="detail-value">{{ $employee->tempat_lahir ?: '-' }}, {{ $employee->tanggal_lahir?->format('d M Y') ?? '-' }}</div></div>
          <div class="detail-item"><div class="detail-label">Masuk</div><div class="detail-value">{{ $employee->tanggal_masuk?->format('d M Y') ?? '-' }}</div></div>
          <div class="detail-item"><div class="detail-label">Kontak</div><div class="detail-value">{{ $employee->no_hp ?: '-' }}<br>{{ $employee->email ?: '-' }}</div></div>
          <div class="detail-item"><div class="detail-label">Administrasi</div><div class="detail-value">NPWP: {{ $employee->no_npwp ?: '-' }}<br>BPJS Kesehatan: {{ $employee->no_bpjs_kesehatan ?: '-' }}<br>BPJS TK: {{ $employee->no_bpjs_ketenagakerjaan ?: '-' }}</div></div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h2 class="card-title">Jobdesk Terkait</h2></div>
      <div class="modal-body">
        @forelse ($jobDescriptions as $job)
          <div class="document-empty-row">
            <i class="bi bi-list-task"></i>
            <span><strong>{{ $job->title }}</strong><br>{{ str($job->description)->limit(110) }}</span>
          </div>
        @empty
          <div class="empty-state"><div class="empty-title">Belum ada jobdesk terkait</div></div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-header-left"><h2 class="card-title">Dokumen / Berkas</h2></div>
      <button class="btn btn-primary btn-sm" type="button" data-open-modal="modal-document"><i class="bi bi-plus-lg"></i> Upload Dokumen</button>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>Jenis</th><th>Nama</th><th>Expired</th><th>File</th><th class="th-aksi">Aksi</th></tr></thead>
        <tbody>
          @forelse ($employee->documents as $document)
            <tr>
              <td>{{ $document->document_type }}</td>
              <td>{{ $document->document_name }}<div class="td-email-sub">{{ $document->description }}</div></td>
              <td>{{ $document->expired_date?->format('d M Y') ?? '-' }}</td>
              <td>{{ $document->file_original_name }}</td>
              <td><div class="action-btns"><a class="btn-action btn-edit" href="{{ route('hr.employee-documents.download', $document) }}"><i class="bi bi-download"></i></a><form method="POST" action="{{ route('hr.employee-documents.destroy', $document) }}" data-confirm-delete="Hapus dokumen {{ $document->document_name }}?">@csrf @method('DELETE')<button class="btn-action btn-delete"><i class="bi bi-trash3-fill"></i></button></form></div></td>
            </tr>
          @empty
            <tr><td colspan="5"><div class="empty-state"><div class="empty-title">Belum ada dokumen</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-header-left"><h2 class="card-title">Sertifikat</h2></div>
      <button class="btn btn-primary btn-sm" type="button" data-open-modal="modal-certificate"><i class="bi bi-plus-lg"></i> Tambah Sertifikat</button>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>Judul</th><th>Nomor</th><th>Pelaksanaan</th><th>Penerbit</th><th>Tipe</th><th>Masa Berlaku</th><th>File</th><th class="th-aksi">Aksi</th></tr></thead>
        <tbody>
          @forelse ($employee->certificates as $certificate)
            <tr>
              <td>{{ $certificate->certificate_title }}</td>
              <td>{{ $certificate->certificate_number ?: '-' }}</td>
              <td>{{ $certificate->execution_date?->format('d M Y') ?? '-' }}</td>
              <td>{{ $certificate->issuer ?: '-' }}</td>
              <td><span class="status-badge status-active">{{ ucfirst($certificate->certificate_type) }}</span></td>
              <td>{{ $certificate->expired_date?->format('d M Y') ?? '-' }}</td>
              <td>@if($certificate->file_path)<a href="{{ route('hr.employee-certificates.download', $certificate) }}">Download</a>@else - @endif</td>
              <td><form method="POST" action="{{ route('hr.employee-certificates.destroy', $certificate) }}" data-confirm-delete="Hapus sertifikat {{ $certificate->certificate_title }}?">@csrf @method('DELETE')<button class="btn-action btn-delete"><i class="bi bi-trash3-fill"></i></button></form></td>
            </tr>
          @empty
            <tr><td colspan="8"><div class="empty-state"><div class="empty-title">Belum ada sertifikat</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="iso-detail-grid">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Absensi Terbaru</h2></div>
      <div class="modal-body">
        @forelse ($employee->attendances as $attendance)
          <div class="document-empty-row"><i class="bi bi-calendar-check"></i>{{ $attendance->attendance_date?->format('d M Y') }} · {{ ucfirst($attendance->status) }}</div>
        @empty
          <div class="empty-state"><div class="empty-title">Belum ada absensi</div></div>
        @endforelse
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h2 class="card-title">Riwayat Gaji</h2></div>
      <div class="modal-body">
        @forelse ($employee->salaries as $salary)
          <div class="document-empty-row"><i class="bi bi-cash-stack"></i>{{ $salary->salary_period }} · Rp {{ number_format($salary->total_salary, 0, ',', '.') }}</div>
        @empty
          <div class="empty-state"><div class="empty-title">Belum ada riwayat gaji</div></div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-document" aria-hidden="true">
    <div class="modal modal-lg"><div class="modal-header"><h3 class="modal-title">Upload Dokumen</h3><button class="modal-close" data-close-modal="modal-document"><i class="bi bi-x-lg"></i></button></div>
      <form method="POST" action="{{ route('hr.employees.documents.store', $employee) }}" enctype="multipart/form-data">@csrf
        <div class="modal-body"><div class="form-grid">
          <div class="form-group"><label class="form-label">Jenis</label><select class="form-select" name="document_type">@foreach($documentTypes as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach</select></div>
          <div class="form-group"><label class="form-label">Nama Dokumen</label><input class="form-control" name="document_name" required></div>
          <div class="form-group"><label class="form-label">File</label><input class="form-control" type="file" name="file" required></div>
          <div class="form-group"><label class="form-label">Expired</label><input class="form-control" type="date" name="expired_date"></div>
          <div class="form-group form-group-full"><label class="form-label">Deskripsi</label><textarea class="form-textarea" name="description"></textarea></div>
        </div></div>
        <div class="modal-footer"><button class="btn btn-outline" type="button" data-close-modal="modal-document">Batal</button><button class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="modal-certificate" aria-hidden="true">
    <div class="modal modal-lg"><div class="modal-header"><h3 class="modal-title">Tambah Sertifikat</h3><button class="modal-close" data-close-modal="modal-certificate"><i class="bi bi-x-lg"></i></button></div>
      <form method="POST" action="{{ route('hr.employees.certificates.store', $employee) }}" enctype="multipart/form-data">@csrf
        <div class="modal-body"><div class="form-grid">
          <div class="form-group"><label class="form-label">Judul</label><input class="form-control" name="certificate_title" required></div>
          <div class="form-group"><label class="form-label">Nomor</label><input class="form-control" name="certificate_number"></div>
          <div class="form-group"><label class="form-label">Penerbit</label><input class="form-control" name="issuer"></div>
          <div class="form-group"><label class="form-label">Tanggal Pelaksanaan</label><input class="form-control" type="date" name="execution_date"></div>
          <div class="form-group"><label class="form-label">Expired</label><input class="form-control" type="date" name="expired_date"></div>
          <div class="form-group"><label class="form-label">Tipe</label><select class="form-select" name="certificate_type">@foreach($certificateTypes as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select></div>
          <div class="form-group"><label class="form-label">File</label><input class="form-control" type="file" name="file"></div>
          <div class="form-group form-group-full"><label class="form-label">Deskripsi</label><textarea class="form-textarea" name="description"></textarea></div>
        </div></div>
        <div class="modal-footer"><button class="btn btn-outline" type="button" data-close-modal="modal-certificate">Batal</button><button class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>
@endsection
