@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
  @php
    $lastPage = method_exists($paginator, 'lastPage') ? $paginator->lastPage() : 1;
    $currentPage = $paginator->currentPage();
    $pages = collect(range(1, max(1, $lastPage)))
      ->filter(fn ($page) => $lastPage <= 7 || $page === 1 || $page === $lastPage || abs($page - $currentPage) <= 1)
      ->values();
  @endphp
  <div class="table-footer">
    <div class="table-info">
      @if ($paginator->total() > 0)
        Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
      @else
        Tidak ada data
      @endif
    </div>

    @if ($paginator->hasPages())
      <div class="pagination">
        @if ($paginator->onFirstPage())
          <button class="page-btn" type="button" disabled><i class="bi bi-chevron-left"></i></button>
        @else
          <a class="page-btn" href="{{ $paginator->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
        @endif

        @php $previousPage = 0; @endphp
        @foreach ($pages as $page)
          @if ($previousPage && $page - $previousPage > 1)
            <button class="page-btn" type="button" disabled>...</button>
          @endif

          @if ($page === $currentPage)
            <button class="page-btn active" type="button" disabled>{{ $page }}</button>
          @else
            <a class="page-btn" href="{{ $paginator->url($page) }}">{{ $page }}</a>
          @endif

          @php $previousPage = $page; @endphp
        @endforeach

        @if ($paginator->hasMorePages())
          <a class="page-btn" href="{{ $paginator->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
        @else
          <button class="page-btn" type="button" disabled><i class="bi bi-chevron-right"></i></button>
        @endif
      </div>
    @else
      <div class="pagination">
        <button class="page-btn active" type="button" disabled>1</button>
      </div>
    @endif
  </div>
@endif
