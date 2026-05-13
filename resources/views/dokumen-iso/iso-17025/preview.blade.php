@extends('layouts.app')

@section('title', 'Preview ' . $document->document_code)

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $document->document_code }} - {{ $document->title }}</h1>
      <p class="page-subtitle">Preview dokumen {{ $document->category?->name }} revisi terbaru.</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('dokumen-iso.standard.index', ['standard' => $standard, 'tab' => 'review']) }}">
      <i class="bi bi-arrow-left"></i>
      Kembali
    </a>
  </div>

  <div class="card iso-preview-page-card">
    @include('dokumen-iso.iso-17025.partials.preview', ['document' => $document, 'revision' => $document->latestRevision, 'allowAdd' => false])
  </div>
@endsection
