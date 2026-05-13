@extends('layouts.app')
@section('title', 'Detail Jobdesk')
@section('content')
  <div class="page-header"><div><h1 class="page-title">{{ $jobDescription->title }}</h1><p class="page-subtitle">{{ $jobDescription->division?->name }} · {{ $jobDescription->position?->name }}</p></div><a class="btn btn-outline btn-sm" href="{{ route('hr.job-descriptions.index') }}">Kembali</a></div>
  <div class="card"><div class="modal-body"><h3 class="card-title">Deskripsi</h3><p>{{ $jobDescription->description }}</p><h3 class="card-title">Target Kerja</h3><p>{{ $jobDescription->target_work ?: '-' }}</p><h3 class="card-title">Supervisor</h3><p>{{ $jobDescription->directSupervisor?->nama ?? '-' }}</p></div></div>
@endsection
