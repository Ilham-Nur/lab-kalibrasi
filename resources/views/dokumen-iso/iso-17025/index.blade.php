@extends('layouts.app')

@section('title', $standard->name)

@php
  $tabs = [
    'review' => ['label' => 'Review', 'code' => null],
    'manual-mutu' => ['label' => 'Manual Mutu', 'code' => 'MM'],
    'quality-procedure' => ['label' => 'Quality Procedure', 'code' => 'QP'],
    'working-instruction' => ['label' => 'Working Instruction', 'code' => 'IK'],
    'form' => ['label' => 'Form', 'code' => 'F'],
  ];
  $categoriesByCode = $categories->keyBy('code');
  $activeCode = $tabs[$activeTab]['code'] ?? null;
  $activeCategory = $activeCode ? $categoriesByCode->get($activeCode) : null;
  $previewRevision = $selectedDocument?->latestRevision;
@endphp

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Dokumen {{ $standard->name }}</h1>
      <p class="page-subtitle">Review relasi bab, dokumen, dan revisi {{ $standard->name }} dari Manual Mutu sampai Form.</p>
    </div>
  </div>

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

  <div class="iso-tabs">
    @foreach ($tabs as $tabKey => $tab)
      <a href="{{ route('dokumen-iso.standard.index', ['standard' => $standard, 'tab' => $tabKey]) }}" class="iso-tab-link @if ($activeTab === $tabKey) active @endif">
        {{ $tab['label'] }}
      </a>
    @endforeach
  </div>

  @if ($activeTab === 'review')
    <div class="card iso-document-card">
      <div class="card-header">
        <div class="card-header-left">
          <h2 class="card-title">Review Dokumen</h2>
          <p class="card-subtitle">Preview Manual Mutu terakhir dan daftar dokumen pendukung berdasarkan bab/sub bab.</p>
        </div>
      </div>

      <div class="iso-document-table-wrap">
        <table class="iso-document-table">
          <thead>
            <tr>
              <th>Main Document</th>
              <th>Second Dokumen</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="iso-main-document-cell">
                @include('dokumen-iso.iso-17025.partials.preview', ['document' => $manualMutu, 'revision' => $manualMutu?->latestRevision, 'allowAdd' => false])
              </td>
              <td class="iso-second-document-cell">
                <div class="review-search">
                  <i class="bi bi-search"></i>
                  <input type="search" id="review-search-input" placeholder="Cari bab, sub bab, atau kode dokumen...">
                </div>
                <div class="review-tree">
                  @forelse ($reviewSections as $item)
                    @php
                      $section = $item['section'];
                      $groupedDocuments = $item['documents'];
                    @endphp
                    <div class="review-section @if ($section->parent_id) is-child @endif" data-review-section>
                      <div class="review-section-title">
                        <span>{{ $section->chapter_number }}</span>
                        <strong>{{ $section->title }}</strong>
                      </div>

                      @foreach (['QP' => 'Quality Procedure', 'IK' => 'Working Instruction', 'F' => 'Form'] as $code => $label)
                        @php $items = $groupedDocuments->get($code, collect()); @endphp
                        @if ($items->isNotEmpty())
                          <div class="review-doc-group">
                            <div class="review-doc-label">{{ $label }}</div>
                            <ul>
                              @foreach ($items as $document)
                                <li>
                                  <a href="{{ route('dokumen-iso.documents.preview', ['standard' => $standard, 'document' => $document]) }}" target="_blank" rel="noopener">
                                    {{ $document->document_code }}
                                    <span>Rev. {{ $document->latestRevision ? str_pad((string) $document->latestRevision->revision_number, 2, '0', STR_PAD_LEFT) : '00' }} terakhir ditambahkan</span>
                                  </a>
                                </li>
                              @endforeach
                            </ul>
                          </div>
                        @endif
                      @endforeach
                    </div>
                  @empty
                    <div class="empty-state iso-empty-state">
                      <div class="empty-icon"><i class="bi bi-diagram-3"></i></div>
                      <div class="empty-title">Bab dan sub bab belum tersedia</div>
                      <div class="empty-desc">Tambahkan struktur bab agar review dokumen bisa dikelompokkan.</div>
                    </div>
                  @endforelse
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  @else
    @php
      $rows = $categoryDocuments->get($activeCode, collect());
    @endphp

    @if ($activeCategory?->code === 'MM')
      <div class="card iso-section-card">
        <div class="card-header">
          <div class="card-header-left">
            <h2 class="card-title">Struktur Bab dan Sub Bab</h2>
            <p class="card-subtitle">Kelola daftar bab/sub bab untuk pengelompokan dokumen ISO 17025.</p>
          </div>
          <button
            class="btn btn-primary btn-sm"
            type="button"
            data-open-modal="modal-section"
            data-section-action="{{ route('dokumen-iso.sections.store', $standard) }}"
            data-section-mode="create"
          >
            <i class="bi bi-plus-lg"></i>
            Tambah Bab
          </button>
        </div>

        <div class="table-responsive">
          <table class="data-table iso-section-table">
            <thead>
              <tr>
                <th>Nomor</th>
                <th>Judul Bab / Sub Bab</th>
                <th>Parent</th>
                <th class="th-aksi">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($sections as $section)
                <tr>
                  <td><strong>{{ $section->chapter_number }}</strong></td>
                  <td>{{ $section->title }}</td>
                  <td>-</td>
                  <td>
                    <button
                      class="btn-action btn-edit"
                      type="button"
                      title="Edit Bab"
                      data-open-modal="modal-section"
                      data-section-action="{{ route('dokumen-iso.sections.update', ['standard' => $standard, 'section' => $section]) }}"
                      data-section-mode="edit"
                      data-section-parent=""
                      data-section-number="{{ $section->chapter_number }}"
                      data-section-title="{{ $section->title }}"
                    >
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                    <form class="inline-delete-form" method="POST" action="{{ route('dokumen-iso.sections.destroy', ['standard' => $standard, 'section' => $section]) }}" data-confirm-delete="Hapus bab {{ $section->chapter_number }}?">
                      @csrf
                      @method('DELETE')
                      <button class="btn-action btn-delete" type="submit" title="Hapus Bab">
                        <i class="bi bi-trash3-fill"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @foreach ($section->children as $child)
                  <tr>
                    <td><strong>{{ $child->chapter_number }}</strong></td>
                    <td>{{ $child->title }}</td>
                    <td>{{ $section->chapter_number }} - {{ $section->title }}</td>
                    <td>
                      <button
                        class="btn-action btn-edit"
                        type="button"
                        title="Edit Sub Bab"
                        data-open-modal="modal-section"
                        data-section-action="{{ route('dokumen-iso.sections.update', ['standard' => $standard, 'section' => $child]) }}"
                        data-section-mode="edit"
                        data-section-parent="{{ $section->id }}"
                        data-section-number="{{ $child->chapter_number }}"
                        data-section-title="{{ $child->title }}"
                      >
                        <i class="bi bi-pencil-fill"></i>
                      </button>
                      <form class="inline-delete-form" method="POST" action="{{ route('dokumen-iso.sections.destroy', ['standard' => $standard, 'section' => $child]) }}" data-confirm-delete="Hapus sub bab {{ $child->chapter_number }}?">
                        @csrf
                        @method('DELETE')
                        <button class="btn-action btn-delete" type="submit" title="Hapus Sub Bab">
                          <i class="bi bi-trash3-fill"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              @empty
                <tr>
                  <td colspan="4">
                    <div class="empty-state">
                      <div class="empty-icon"><i class="bi bi-diagram-3"></i></div>
                      <div class="empty-title">Struktur bab belum tersedia</div>
                      <div class="empty-desc">Klik Tambah Bab untuk membuat bab atau sub bab pertama.</div>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @endif

    <div class="card iso-table-card">
      <div class="card-header">
        <div class="card-header-left">
          <h2 class="card-title">{{ $tabs[$activeTab]['label'] }}</h2>
          <p class="card-subtitle">Kelola dokumen, revisi, file original, dan PDF preview.</p>
        </div>
        <button
          class="btn btn-primary btn-sm"
          type="button"
          data-open-modal="modal-add-document"
          data-prefill-category="{{ $activeCategory?->id }}"
        >
          <i class="bi bi-plus-lg"></i>
          Tambah Data
        </button>
      </div>

      <div class="table-responsive">
        <table class="data-table iso-data-table">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Judul</th>
              @if ($activeCategory?->code !== 'MM')
                <th>Bab / Sub Bab</th>
              @endif
              <th>Revisi</th>
              <th>Status</th>
              <th>File</th>
              <th class="th-aksi">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($rows as $document)
              @php
                $documentSections = $document->sections->isNotEmpty()
                  ? $document->sections
                  : collect([$document->section])->filter();
              @endphp
              <tr class="document-parent-row">
                <td><strong>{{ $document->document_code }}</strong></td>
                <td>
                  <div class="td-name">{{ $document->title }}</div>
                  <div class="td-email-sub">{{ $document->description ?: 'Tanpa deskripsi' }}</div>
                </td>
                @if ($activeCategory?->code !== 'MM')
                  <td>
                    @if ($documentSections->isNotEmpty())
                      <div class="section-chip-list">
                        @foreach ($documentSections as $documentSection)
                          <span class="section-chip">{{ $documentSection->chapter_number }} - {{ $documentSection->title }}</span>
                        @endforeach
                      </div>
                    @else
                      -
                    @endif
                  </td>
                @endif
                <td><span class="revision-pill">Rev. 00</span></td>
                <td><span class="status-badge status-{{ $document->status }}">{{ ucfirst($document->status) }}</span></td>
                <td>
                  @if ($document->preview_file_path)
                    <span class="status-badge status-active">PDF</span>
                  @elseif ($document->original_file_path)
                    <span class="status-badge status-pending">{{ strtoupper($document->original_file_type) }}</span>
                  @else
                    <span class="status-badge status-inactive">Kosong</span>
                  @endif
                </td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action btn-view" type="button" title="Detail" data-open-modal="modal-detail-document-{{ $document->id }}">
                      <i class="bi bi-eye-fill"></i>
                    </button>
                    @if ($document->original_file_path)
                      <a class="btn-action btn-edit" href="{{ route('dokumen-iso.documents.original', ['standard' => $standard, 'document' => $document]) }}" title="Download Original">
                        <i class="bi bi-download"></i>
                      </a>
                    @endif
                    @if ($document->preview_file_path)
                      <a class="btn-action btn-delete" href="{{ route('dokumen-iso.documents.pdf', ['standard' => $standard, 'document' => $document]) }}" title="Download PDF">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                      </a>
                    @endif
                    <button
                      class="btn-action btn-edit"
                      type="button"
                      title="Tambah Revisi"
                      data-open-modal="modal-add-document"
                      data-revision-mode="true"
                      data-prefill-document="{{ $document->id }}"
                      data-prefill-category="{{ $document->category_id }}"
                      data-prefill-sections="{{ $document->sections->pluck('id')->implode(',') ?: $document->section_id }}"
                      data-prefill-code="{{ $document->document_code }}"
                      data-prefill-title="{{ $document->title }}"
                      data-prefill-description="{{ $document->description }}"
                      data-prefill-status="{{ $document->status }}"
                    >
                      <i class="bi bi-plus-circle-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @foreach ($document->revisions->where('revision_number', '>=', 1)->sortBy('revision_number') as $revision)
                @php
                  $snapshotSectionIds = collect($revision->section_ids ?? [])->filter()->map(fn ($id) => (int) $id);
                  $revisionSections = $snapshotSectionIds->isNotEmpty()
                    ? $sections->flatMap(fn ($section) => collect([$section])->merge($section->children))->whereIn('id', $snapshotSectionIds)
                    : $documentSections;
                @endphp
                <tr class="document-revision-row">
                  <td><strong>{{ $revision->document_code ?? $document->document_code }}</strong></td>
                  <td>
                    <div class="td-name">{{ $revision->title ?? $document->title }}</div>
                    <div class="td-email-sub">{{ $revision->description ?? $document->description ?: 'Tanpa deskripsi' }}</div>
                  </td>
                  @if ($activeCategory?->code !== 'MM')
                    <td>
                      @if ($revisionSections->isNotEmpty())
                        <div class="section-chip-list">
                          @foreach ($revisionSections as $documentSection)
                            <span class="section-chip">{{ $documentSection->chapter_number }} - {{ $documentSection->title }}</span>
                          @endforeach
                        </div>
                      @else
                        -
                      @endif
                    </td>
                  @endif
                  <td><span class="revision-pill is-child">Rev. {{ str_pad((string) $revision->revision_number, 2, '0', STR_PAD_LEFT) }}</span></td>
                  <td><span class="status-badge status-{{ $revision->status ?? $document->status }}">{{ ucfirst($revision->status ?? $document->status) }}</span></td>
                  <td>
                    @if ($revision->pdf_file_path)
                      <span class="status-badge status-active">PDF</span>
                    @elseif ($revision->original_file_path)
                      <span class="status-badge status-pending">{{ strtoupper($revision->original_file_type) }}</span>
                    @else
                      <span class="status-badge status-inactive">Kosong</span>
                    @endif
                  </td>
                  <td>
                    <div class="action-btns">
                      <button class="btn-action btn-view" type="button" title="Detail" data-open-modal="modal-detail-revision-{{ $revision->id }}">
                        <i class="bi bi-eye-fill"></i>
                      </button>
                      @if ($revision->original_file_path)
                        <a class="btn-action btn-edit" href="{{ route('dokumen-iso.17025.revisions.original', $revision) }}" title="Download Original">
                          <i class="bi bi-download"></i>
                        </a>
                      @endif
                      @if ($revision->pdf_file_path)
                        <a class="btn-action btn-delete" href="{{ route('dokumen-iso.17025.revisions.pdf', $revision) }}" title="Download PDF">
                          <i class="bi bi-file-earmark-pdf-fill"></i>
                        </a>
                      @endif
                    </div>
                  </td>
                </tr>
              @endforeach
            @empty
              <tr>
                <td colspan="{{ $activeCategory?->code === 'MM' ? 6 : 7 }}">
                  <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-folder2-open"></i></div>
                    <div class="empty-title">Belum ada data {{ $tabs[$activeTab]['label'] }}</div>
                    <div class="empty-desc">Klik Tambah Data untuk membuat dokumen pertama.</div>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @if ($activeTab !== 'review' && isset($rows))
    @foreach ($rows as $document)
      <div class="modal-overlay" id="modal-detail-document-{{ $document->id }}" aria-hidden="true">
        <div class="modal modal-xl" role="dialog" aria-modal="true" aria-labelledby="modal-detail-document-title-{{ $document->id }}">
          <div class="modal-header">
            <h3 class="modal-title" id="modal-detail-document-title-{{ $document->id }}">
              <i class="bi bi-eye-fill"></i> Detail {{ $document->document_code }} Rev. 00
            </h3>
            <button class="modal-close" data-close-modal="modal-detail-document-{{ $document->id }}" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="modal-body">
            <div class="revision-modal-grid">
              <div>
                <h4 class="revision-modal-heading">Preview Dokumen Awal</h4>
                @include('dokumen-iso.iso-17025.partials.preview', ['document' => $document, 'allowAdd' => false])
              </div>
              <div>
                <h4 class="revision-modal-heading">Preview Pembanding</h4>
                <div class="document-empty-row"><i class="bi bi-info-circle"></i> Dokumen awal belum memiliki revisi pembanding.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @foreach ($document->revisions->where('revision_number', '>=', 1) as $revision)
        @php
          $previousRevision = $document->revisions
            ->where('revision_number', '<', $revision->revision_number)
            ->sortByDesc('revision_number')
            ->first();
        @endphp
        <div class="modal-overlay" id="modal-detail-revision-{{ $revision->id }}" aria-hidden="true">
          <div class="modal modal-xl" role="dialog" aria-modal="true" aria-labelledby="modal-detail-revision-title-{{ $revision->id }}">
            <div class="modal-header">
              <h3 class="modal-title" id="modal-detail-revision-title-{{ $revision->id }}">
                <i class="bi bi-eye-fill"></i> Detail {{ $revision->document_code ?? $document->document_code }} Rev. {{ str_pad((string) $revision->revision_number, 2, '0', STR_PAD_LEFT) }}
              </h3>
              <button class="modal-close" data-close-modal="modal-detail-revision-{{ $revision->id }}" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="modal-body">
              <div class="revision-modal-grid">
                <div>
                  <h4 class="revision-modal-heading">Preview Revisi Ini</h4>
                  @include('dokumen-iso.iso-17025.partials.preview', ['document' => $document, 'revision' => $revision, 'allowAdd' => false])
                </div>
                <div>
                  <h4 class="revision-modal-heading">Preview Revisi Sebelumnya</h4>
                  @if ($previousRevision)
                    @include('dokumen-iso.iso-17025.partials.preview', ['document' => $document, 'revision' => $previousRevision, 'allowAdd' => false])
                  @else
                    @include('dokumen-iso.iso-17025.partials.preview', ['document' => $document, 'allowAdd' => false])
                  @endif
                </div>
              </div>

              <div class="revision-diff-panel revision-diff-panel-modal">
                <h3>Perbandingan Metadata</h3>
                <div class="revision-compare">
                  <div>
                    <span>Sebelumnya</span>
                    <strong>Rev. {{ $previousRevision ? str_pad((string) $previousRevision->revision_number, 2, '0', STR_PAD_LEFT) : '00' }}</strong>
                    <p>{{ $previousRevision?->title ?? $document->title }}</p>
                    <p>{{ $previousRevision?->notes ?: 'Tidak ada catatan revisi.' }}</p>
                  </div>
                  <div>
                    <span>Dipilih</span>
                    <strong>Rev. {{ str_pad((string) $revision->revision_number, 2, '0', STR_PAD_LEFT) }}</strong>
                    <p>{{ $revision->title ?? $document->title }}</p>
                    <p>{{ $revision->notes ?: 'Tidak ada catatan revisi.' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    @endforeach
  @endif

  @if ($activeTab !== 'review' && $activeCategory)
    @if ($activeCategory->code === 'MM')
      <div class="modal-overlay" id="modal-section" aria-hidden="true">
        <div class="modal modal-md" role="dialog" aria-modal="true" aria-labelledby="modal-section-title">
          <div class="modal-header">
            <h3 class="modal-title" id="modal-section-title">
              <i class="bi bi-diagram-3-fill"></i> Form Bab / Sub Bab
            </h3>
            <button class="modal-close" data-close-modal="modal-section" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
          </div>

          <form method="POST" action="{{ route('dokumen-iso.sections.store', $standard) }}" id="section-form" novalidate>
            @csrf
            <input type="hidden" id="section-method" value="">
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label" for="section_parent_id">Parent Bab</label>
                <select id="section_parent_id" name="parent_id" class="form-select">
                  <option value="">Bab Utama</option>
                  @foreach ($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->chapter_number }} - {{ $section->title }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="section_chapter_number">Nomor Bab / Sub Bab <span class="required">*</span></label>
                <input type="text" id="section_chapter_number" name="chapter_number" class="form-control" placeholder="Contoh: 1 atau 1.1">
              </div>

              <div class="form-group">
                <label class="form-label" for="section_title">Judul Bab / Sub Bab <span class="required">*</span></label>
                <input type="text" id="section_title" name="title" class="form-control" placeholder="Contoh: Pengendalian Dokumen">
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-outline" data-close-modal="modal-section">Batal</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i>
                Simpan Bab
              </button>
            </div>
          </form>
        </div>
      </div>
    @endif

  <div class="modal-overlay @if ($errors->any()) modal-open @endif" id="modal-add-document" aria-hidden="{{ $errors->any() ? 'false' : 'true' }}">
    <div class="modal modal-lg" role="dialog" aria-modal="true" aria-labelledby="modal-add-document-title">
      <div class="modal-header">
        <h3 class="modal-title" id="modal-add-document-title">
          <i class="bi bi-file-earmark-plus-fill"></i> Tambah Dokumen / Revisi
        </h3>
        <button class="modal-close" data-close-modal="modal-add-document" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
      </div>

      <form method="POST" action="{{ route('dokumen-iso.documents.store', $standard) }}" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" id="document_id" name="document_id" value="{{ old('document_id') }}">
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="category_id_display">Tab / Kategori</label>
              <input type="hidden" id="category_id" name="category_id" value="{{ old('category_id', $activeCategory->id) }}">
              <input type="text" id="category_id_display" class="form-control" value="{{ $activeCategory->code }} - {{ $activeCategory->name }}" readonly>
              @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @if ($activeCategory->code !== 'MM')
              <div class="form-group form-group-full">
                <label class="form-label" for="section_ids">Bab / Sub Bab</label>
                <select id="section_ids" name="section_ids[]" class="form-select iso-multi-select-native @error('section_ids') is-invalid @enderror @error('section_ids.*') is-invalid @enderror" multiple>
                  @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected(in_array($section->id, old('section_ids', [])))>{{ $section->chapter_number }} - {{ $section->title }}</option>
                    @foreach ($section->children as $child)
                      <option value="{{ $child->id }}" @selected(in_array($child->id, old('section_ids', [])))>{{ $child->chapter_number }} - {{ $child->title }}</option>
                    @endforeach
                  @endforeach
                </select>
                <div class="iso-multi-picker" data-multi-picker data-target="#section_ids">
                  <button class="iso-multi-picker-toggle" type="button" data-multi-toggle>
                    <span data-multi-placeholder>Pilih bab/sub bab</span>
                    <i class="bi bi-chevron-down"></i>
                  </button>
                  <div class="iso-multi-picker-menu">
                    <div class="iso-multi-picker-search">
                      <i class="bi bi-search"></i>
                      <input type="search" placeholder="Cari bab atau sub bab..." data-multi-search>
                    </div>
                    <div class="iso-multi-picker-options">
                      @foreach ($sections as $section)
                        <label class="iso-multi-option">
                          <input type="checkbox" value="{{ $section->id }}" data-multi-option>
                          <span>{{ $section->chapter_number }} - {{ $section->title }}</span>
                        </label>
                        @foreach ($section->children as $child)
                          <label class="iso-multi-option is-child">
                            <input type="checkbox" value="{{ $child->id }}" data-multi-option>
                            <span>{{ $child->chapter_number }} - {{ $child->title }}</span>
                          </label>
                        @endforeach
                      @endforeach
                    </div>
                  </div>
                </div>
                <div class="form-help">Bisa pilih lebih dari satu bab/sub bab.</div>
                @error('section_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @error('section_ids.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            @endif

            <div class="form-group">
              <label class="form-label" for="document_code">Kode Dokumen <span class="required">*</span></label>
              <input type="text" id="document_code" name="document_code" class="form-control @error('document_code') is-invalid @enderror" value="{{ old('document_code') }}" placeholder="Contoh: Q1">
              @error('document_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
              <label class="form-label" for="status">Status <span class="required">*</span></label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                <option value="active" @selected(old('status') === 'active')>Active</option>
                <option value="archived" @selected(old('status') === 'archived')>Archived</option>
              </select>
              @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group form-group-full">
              <label class="form-label" for="title">Judul Dokumen <span class="required">*</span></label>
              <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Prosedur Pengendalian Dokumen">
              @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
              <label class="form-label" for="effective_date">Tanggal Efektif</label>
              <input type="date" id="effective_date" name="effective_date" class="form-control @error('effective_date') is-invalid @enderror" value="{{ old('effective_date') }}">
              @error('effective_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
              <label class="form-label">File Original</label>
              <div class="file-upload-wrapper @error('original_file') has-error @enderror">
                <input type="file" id="original-file" name="original_file" class="file-input @error('original_file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx" data-allowed-ext="pdf,doc,docx,xls,xlsx" data-max-size="20">
                <label for="original-file" class="file-label">
                  <div class="file-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                  <div class="file-text">
                    <span class="file-placeholder" data-default-placeholder="Klik untuk pilih file atau drag &amp; drop">Klik untuk pilih file atau drag &amp; drop</span>
                    <span class="file-meta">PDF, Word, Excel - Maks. 20MB</span>
                  </div>
                </label>
                <div class="file-preview"></div>
              </div>
              <div class="invalid-feedback file-error" @error('original_file') style="display: block;" @enderror>@error('original_file') {{ $message }} @enderror</div>
            </div>

            <div class="form-group">
              <label class="form-label">File Preview PDF</label>
              <div class="file-upload-wrapper @error('preview_file') has-error @enderror">
                <input type="file" id="preview-file" name="preview_file" class="file-input @error('preview_file') is-invalid @enderror" accept=".pdf" data-allowed-ext="pdf" data-max-size="20">
                <label for="preview-file" class="file-label">
                  <div class="file-icon"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                  <div class="file-text">
                    <span class="file-placeholder" data-default-placeholder="Klik untuk pilih PDF preview atau drag &amp; drop">Klik untuk pilih PDF preview atau drag &amp; drop</span>
                    <span class="file-meta">PDF - Maks. 20MB</span>
                  </div>
                </label>
                <div class="file-preview"></div>
              </div>
              <div class="invalid-feedback file-error" @error('preview_file') style="display: block;" @enderror>@error('preview_file') {{ $message }} @enderror</div>
            </div>

            <div class="form-group form-group-full">
              <label class="form-label" for="description">Deskripsi</label>
              <textarea id="description" name="description" class="form-textarea @error('description') is-invalid @enderror" rows="2" placeholder="Catatan singkat dokumen">{{ old('description') }}</textarea>
              @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group form-group-full revision-notes-group">
              <label class="form-label" for="notes">Catatan Revisi</label>
              <textarea id="notes" name="notes" class="form-textarea @error('notes') is-invalid @enderror" rows="2" placeholder="Apa yang berubah pada revisi ini">{{ old('notes') }}</textarea>
              @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-close-modal="modal-add-document">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle"></i>
            Simpan Dokumen
          </button>
        </div>
      </form>
    </div>
  </div>
  @endif
@endsection
