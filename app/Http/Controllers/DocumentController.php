<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentRevision;
use App\Models\DocumentSection;
use App\Models\DocumentStandard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    private const TAB_CODES = [
        'manual-mutu' => 'MM',
        'quality-procedure' => 'QP',
        'working-instruction' => 'IK',
        'form' => 'F',
    ];

    public function iso17025(Request $request): View
    {
        $standard = DocumentStandard::where('slug', '17025')->firstOrFail();

        return $this->renderIso17025($standard, $request->query('tab', 'review'));
    }

    public function standard(Request $request, DocumentStandard $standard): View
    {
        return $this->renderIso17025($standard, $request->query('tab', 'review'));
    }

    public function storeStandard(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);
        $slug = $this->standardSlug($name);
        $baseSlug = $slug;
        $counter = 2;

        while (DocumentStandard::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $standard = DocumentStandard::create([
            'name' => $name,
            'slug' => $slug,
            'order_number' => ((int) DocumentStandard::max('order_number')) + 1,
        ]);

        return redirect()
            ->route('dokumen-iso.standard.index', $standard)
            ->with('success', $standard->name . ' berhasil ditambahkan.');
    }

    public function show(Request $request, DocumentStandard $standard, Document $document): View
    {
        abort_unless((int) $document->standard_id === (int) $standard->id, 404);
        $document->load(['category', 'section.parent', 'sections.parent', 'latestRevision', 'revisions']);

        return $this->renderIso17025(
            $standard,
            $this->tabKeyForCategory($document->category?->code),
            $document
        );
    }

    public function preview(DocumentStandard $standard, Document $document): View
    {
        abort_unless((int) $document->standard_id === (int) $standard->id, 404);
        $document->load(['category', 'section.parent', 'sections.parent', 'latestRevision', 'revisions']);

        return view('dokumen-iso.iso-17025.preview', compact('document', 'standard'));
    }

    public function store(Request $request, DocumentStandard $standard): RedirectResponse
    {
        $validated = $request->validate([
            'document_id' => ['nullable', 'exists:documents,id'],
            'category_id' => ['required', 'exists:document_categories,id'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['exists:document_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'document_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('documents', 'document_code')
                    ->where('standard_id', $standard->id)
                    ->where('category_id', $request->input('category_id'))
                    ->ignore($request->input('document_id')),
            ],
            'description' => ['nullable', 'string'],
            'effective_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,active,archived'],
            'original_file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:20480'],
            'preview_file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'notes' => ['nullable', 'string'],
        ]);

        $category = DocumentCategory::findOrFail($validated['category_id']);
        $isRevision = filled($validated['document_id'] ?? null);
        $sectionIds = $category->code === 'MM'
            ? []
            : collect($validated['section_ids'] ?? [])->filter()->unique()->values()->all();
        $fileData = $this->storeUploadedFiles($request);

        if ($isRevision) {
            $document = Document::findOrFail($validated['document_id']);
            abort_unless((int) $document->standard_id === (int) $standard->id, 404);
            $latestRevisionNumber = (int) $document->revisions()->max('revision_number');
            $nextRevisionNumber = max(1, $latestRevisionNumber + 1);

            $revision = $document->revisions()->create([
                'document_code' => $validated['document_code'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'section_ids' => $sectionIds,
                'revision_number' => $nextRevisionNumber,
                'effective_date' => $validated['effective_date'] ?? null,
                'original_file_path' => $fileData['original_file_path'],
                'pdf_file_path' => $fileData['pdf_file_path'],
                'original_file_type' => $fileData['original_file_type'],
                'conversion_status' => 'uploaded',
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            $document->update([
                'section_id' => $sectionIds[0] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'effective_date' => $validated['effective_date'] ?? null,
            ]);
            $document->sections()->sync($sectionIds);

            return redirect()
                ->route('dokumen-iso.standard.index', ['standard' => $standard, 'tab' => $this->tabKeyForCategory($category->code)])
                ->with('success', 'Revisi dokumen berhasil disimpan sebagai Rev. ' . str_pad((string) $revision->revision_number, 2, '0', STR_PAD_LEFT) . '.');
        }

        $document = Document::create([
            'category_id' => $validated['category_id'],
            'standard_id' => $standard->id,
            'section_id' => $sectionIds[0] ?? null,
            'document_code' => $validated['document_code'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'revision' => '00',
            'effective_date' => $validated['effective_date'] ?? null,
            'file_path' => $fileData['pdf_file_path'],
            'original_file_path' => $fileData['original_file_path'],
            'preview_file_path' => $fileData['pdf_file_path'],
            'original_file_type' => $fileData['original_file_type'],
        ]);
        $document->sections()->sync($sectionIds);

        return redirect()
            ->route('dokumen-iso.standard.index', ['standard' => $standard, 'tab' => $this->tabKeyForCategory($category->code)])
            ->with('success', 'Dokumen berhasil disimpan sebagai Rev. 00.');
    }

    public function storeSection(Request $request, DocumentStandard $standard): RedirectResponse
    {
        $validated = $this->validateSection($request);

        DocumentSection::create([
            ...$validated,
            'standard_id' => $standard->id,
            'order_number' => $this->sectionOrderNumber($validated['chapter_number']),
        ]);

        return redirect()
            ->route('dokumen-iso.standard.index', ['standard' => $standard, 'tab' => 'manual-mutu'])
            ->with('success', 'Bab atau sub bab berhasil ditambahkan.');
    }

    public function updateSection(Request $request, DocumentStandard $standard, DocumentSection $section): RedirectResponse
    {
        abort_unless((int) $section->standard_id === (int) $standard->id, 404);
        $validated = $this->validateSection($request, $section);

        $section->update([
            ...$validated,
            'order_number' => $this->sectionOrderNumber($validated['chapter_number']),
        ]);

        return redirect()
            ->route('dokumen-iso.standard.index', ['standard' => $standard, 'tab' => 'manual-mutu'])
            ->with('success', 'Bab atau sub bab berhasil diperbarui.');
    }

    public function destroySection(DocumentStandard $standard, DocumentSection $section): RedirectResponse
    {
        abort_unless((int) $section->standard_id === (int) $standard->id, 404);
        $hasChildren = $section->children()->exists();
        $hasLinkedDocuments = $section->linkedDocuments()->exists() || $section->documents()->exists();

        if ($hasChildren || $hasLinkedDocuments) {
            return redirect()
                ->route('dokumen-iso.standard.index', ['standard' => $standard, 'tab' => 'manual-mutu'])
                ->with('error', 'Bab atau sub bab tidak bisa dihapus karena masih memiliki sub bab atau dokumen terkait.');
        }

        $section->delete();

        return redirect()
            ->route('dokumen-iso.standard.index', ['standard' => $standard, 'tab' => 'manual-mutu'])
            ->with('success', 'Bab atau sub bab berhasil dihapus.');
    }

    public function downloadOriginal(DocumentRevision $revision): StreamedResponse
    {
        abort_unless($revision->original_file_path, 404);

        return Storage::disk('public')->download($revision->original_file_path);
    }

    public function downloadPdf(DocumentRevision $revision): StreamedResponse
    {
        abort_unless($revision->pdf_file_path, 404);

        return Storage::disk('public')->download($revision->pdf_file_path);
    }

    public function downloadDocumentOriginal(DocumentStandard $standard, Document $document): StreamedResponse
    {
        abort_unless((int) $document->standard_id === (int) $standard->id, 404);
        abort_unless($document->original_file_path, 404);

        return Storage::disk('public')->download($document->original_file_path);
    }

    public function downloadDocumentPdf(DocumentStandard $standard, Document $document): StreamedResponse
    {
        abort_unless((int) $document->standard_id === (int) $standard->id, 404);
        abort_unless($document->preview_file_path, 404);

        return Storage::disk('public')->download($document->preview_file_path);
    }

    private function renderIso17025(DocumentStandard $standard, string $activeTab = 'review', ?Document $selectedDocument = null): View
    {
        $categories = DocumentCategory::query()
            ->whereIn('code', array_values(self::TAB_CODES))
            ->orderBy('order_number')
            ->get();
        $activeCode = self::TAB_CODES[$activeTab] ?? null;
        $activeCategory = $activeCode ? $categories->firstWhere('code', $activeCode) : null;

        $sections = DocumentSection::query()
            ->with(['children' => fn ($query) => $query->orderBy('order_number')])
            ->where('standard_id', $standard->id)
            ->whereNull('parent_id')
            ->orderBy('order_number')
            ->get();

        $sectionRows = DocumentSection::query()
            ->with(['children' => fn ($query) => $query->orderBy('order_number')])
            ->where('standard_id', $standard->id)
            ->whereNull('parent_id')
            ->orderBy('order_number')
            ->paginate(10, ['*'], 'sections_page')
            ->withQueryString();

        $documents = Document::query()
            ->with(['category', 'section.parent', 'sections.parent', 'latestRevision', 'revisions'])
            ->where('standard_id', $standard->id)
            ->whereIn('category_id', $categories->pluck('id'))
            ->orderBy('document_code')
            ->get();

        $activeDocuments = $activeCategory
            ? Document::query()
                ->with(['category', 'section.parent', 'sections.parent', 'latestRevision', 'revisions'])
                ->where('standard_id', $standard->id)
                ->where('category_id', $activeCategory->id)
                ->orderBy('document_code')
                ->paginate(10, ['*'], 'documents_page')
                ->withQueryString()
            : null;

        $manualMutu = $documents
            ->where('category.code', 'MM')
            ->sortByDesc(fn (Document $document) => $document->latestRevision?->created_at ?? $document->created_at)
            ->first();

        $reviewSections = $this->buildReviewSections($sections, $documents);
        $previousRevision = $selectedDocument?->revisions
            ->where('id', '!=', $selectedDocument->latestRevision?->id)
            ->sortByDesc('revision_number')
            ->first();

        return view('dokumen-iso.iso-17025.index', compact(
            'activeTab',
            'activeCategory',
            'activeDocuments',
            'categories',
            'sections',
            'sectionRows',
            'documents',
            'manualMutu',
            'selectedDocument',
            'reviewSections',
            'previousRevision'
        ) + compact('standard'));
    }

    private function buildReviewSections($sections, $documents): array
    {
        $leafSections = [];

        foreach ($sections as $section) {
            $leafSections[] = $section;

            foreach ($section->children as $child) {
                $leafSections[] = $child;
            }
        }

        return collect($leafSections)->map(function (DocumentSection $section) use ($documents) {
            return [
                'section' => $section,
                'documents' => $documents
                    ->whereIn('category.code', ['QP', 'IK', 'F'])
                    ->filter(fn (Document $document) => $document->sections->contains('id', $section->id) || (int) $document->section_id === (int) $section->id)
                    ->groupBy('category.code'),
            ];
        })->all();
    }

    private function storeUploadedFiles(Request $request): array
    {
        $originalFile = $request->file('original_file');
        $previewFile = $request->file('preview_file');

        return [
            'original_file_path' => $originalFile->store('documents/iso-17025/original', 'public'),
            'pdf_file_path' => $previewFile->store('documents/iso-17025/preview', 'public'),
            'original_file_type' => strtolower($originalFile->getClientOriginalExtension()),
        ];
    }

    private function tabKeyForCategory(?string $code): string
    {
        return array_search($code, self::TAB_CODES, true) ?: 'review';
    }

    private function validateSection(Request $request, ?DocumentSection $section = null): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'exists:document_sections,id'],
            'chapter_number' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
        ]);
    }

    private function standardSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/^iso\s*/i', '', $slug) ?: $name;
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);

        return trim($slug, '-') ?: 'iso';
    }

    private function sectionOrderNumber(string $chapterNumber): int
    {
        $segments = array_slice(explode('.', $chapterNumber), 0, 3);
        $segments = array_pad($segments, 3, '0');

        return (int) collect($segments)
            ->map(fn (string $segment) => str_pad((string) ((int) $segment), 3, '0', STR_PAD_LEFT))
            ->implode('');
    }
}
