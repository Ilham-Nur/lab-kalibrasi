@extends('layouts.app')
@section('title', $recruitment->exists ? 'Edit Recruitment' : 'Tambah Recruitment')
@section('content')
  <div class="page-header"><div><h1 class="page-title">{{ $recruitment->exists ? 'Edit Recruitment' : 'Tambah Recruitment' }}</h1><p class="page-subtitle">Catat kebutuhan personel perusahaan.</p></div><a class="btn btn-outline btn-sm" href="{{ route('hr.recruitments.index') }}">Kembali</a></div>
  <div class="card"><form method="POST" action="{{ $recruitment->exists ? route('hr.recruitments.update',$recruitment) : route('hr.recruitments.store') }}">@csrf @if($recruitment->exists) @method('PUT') @endif
    <div class="modal-body"><div class="form-grid">
      <div class="form-group"><label class="form-label">Divisi</label><select class="form-select" name="division_id"><option value="">Pilih</option>@foreach($divisions as $division)<option value="{{ $division->id }}" @selected(old('division_id',$recruitment->division_id)==$division->id)>{{ $division->name }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Jabatan</label><select class="form-select" name="position_id"><option value="">Pilih</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected(old('position_id',$recruitment->position_id)==$position->id)>{{ $position->name }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Jumlah Dibutuhkan</label><input class="form-control" type="number" min="1" name="needed_count" value="{{ old('needed_count',$recruitment->needed_count ?? 1) }}"></div>
      <div class="form-group"><label class="form-label">Tipe</label><select class="form-select" name="employment_type">@foreach($employmentTypes as $type)<option value="{{ $type }}" @selected(old('employment_type',$recruitment->employment_type)===$type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Tanggal Request</label><input class="form-control" type="date" name="request_date" value="{{ old('request_date',$recruitment->request_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
      <div class="form-group"><label class="form-label">Status</label><select class="form-select" name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status',$recruitment->status ?? 'draft')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
      <div class="form-group form-group-full"><label class="form-label">Alasan</label><textarea class="form-textarea" name="reason" required>{{ old('reason',$recruitment->reason) }}</textarea></div>
      <div class="form-group form-group-full"><label class="form-label">Deskripsi</label><textarea class="form-textarea" name="description">{{ old('description',$recruitment->description) }}</textarea></div>
    </div></div><div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
  </form></div>
@endsection
