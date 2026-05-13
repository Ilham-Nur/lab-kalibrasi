@php
  $revision = $revision ?? null;
  $allowAdd = $allowAdd ?? true;
  $previewUrl = $revision?->pdf_url ?? $document?->pdf_url;
  $downloadPdfRoute = $revision
    ? route('dokumen-iso.17025.revisions.pdf', $revision)
    : ($document ? route('dokumen-iso.documents.pdf', ['standard' => $standard, 'document' => $document]) : null);
  $downloadOriginalRoute = $revision
    ? route('dokumen-iso.17025.revisions.original', $revision)
    : ($document ? route('dokumen-iso.documents.original', ['standard' => $standard, 'document' => $document]) : null);
  $originalPath = $revision?->original_file_path ?? $document?->original_file_path;
  $revisionLabel = $revision ? str_pad((string) $revision->revision_number, 2, '0', STR_PAD_LEFT) : '00';
@endphp

<div class="pdf-preview-panel">
  <div class="pdf-preview-toolbar">
    <div class="pdf-file-info">
      <i class="bi bi-file-earmark-pdf-fill"></i>
      <div>
        <span class="pdf-file-title">{{ $document?->title ?? 'Belum ada dokumen' }}</span>
        <span class="pdf-file-meta">
          {{ $document?->document_code ?? 'Preview dokumen' }}
          @if ($document)
            &middot; Rev. {{ $revisionLabel }}
          @endif
        </span>
      </div>
    </div>

    @if ($previewUrl && $downloadPdfRoute)
      <a class="btn btn-outline btn-sm" href="{{ $downloadPdfRoute }}">
        <i class="bi bi-download"></i>
        PDF
      </a>
    @endif
  </div>

  @if ($previewUrl)
    <iframe class="pdf-iframe" src="{{ $previewUrl }}" title="Preview {{ $document->title }}"></iframe>
  @else
    <div class="pdf-empty-state">
      <div class="empty-icon"><i class="bi bi-file-earmark-pdf"></i></div>
      <div class="empty-title">
        @if ($originalPath)
          Preview PDF tidak tersedia
        @else
          Dokumen belum tersedia
        @endif
      </div>
      <div class="empty-desc">
        @if ($originalPath)
          File original tersedia, namun preview PDF belum diupload.
        @else
          Tambahkan dokumen atau revisi untuk menampilkan preview di sini.
        @endif
      </div>
      @if ($originalPath && $downloadOriginalRoute)
        <a class="btn btn-outline btn-sm" href="{{ $downloadOriginalRoute }}">
          <i class="bi bi-download"></i>
          Download Original
        </a>
      @elseif ($allowAdd)
        <button class="btn btn-primary btn-sm" type="button" data-open-modal="modal-add-document">
          <i class="bi bi-plus-lg"></i>
          Tambah Dokumen
        </button>
      @endif
    </div>
  @endif
</div>
