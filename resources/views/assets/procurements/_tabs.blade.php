<div class="card">
  <div class="card-header">
    <div class="action-btns">
      @can('asset-procurements.view')
        <a class="btn btn-sm {{ request()->routeIs('assets.procurements.index', 'assets.procurements.create', 'assets.procurements.edit', 'assets.procurements.show') ? 'btn-primary' : 'btn-outline' }}" href="{{ route('assets.procurements.index') }}">
          <i class="bi bi-list-ul"></i> Pengajuan dan Pengadaan
        </a>
      @endcan
      @can('asset-procurements.approve')
        <a class="btn btn-sm {{ request()->routeIs('assets.procurements.approvals.*') ? 'btn-primary' : 'btn-outline' }}" href="{{ route('assets.procurements.approvals.index') }}">
          <i class="bi bi-check2-square"></i> Approval
        </a>
      @endcan
      @can('asset-receipts.view')
        <a class="btn btn-sm {{ request()->routeIs('assets.receipts.*') ? 'btn-primary' : 'btn-outline' }}" href="{{ route('assets.receipts.index') }}">
          <i class="bi bi-box-arrow-in-down"></i> Penerimaan Barang
        </a>
      @endcan
      @can('asset-conversions.view')
        <a class="btn btn-sm {{ request()->routeIs('assets.convert.*') ? 'btn-primary' : 'btn-outline' }}" href="{{ route('assets.convert.index') }}">
          <i class="bi bi-arrow-repeat"></i> Convert ke Aset
        </a>
      @endcan
    </div>
  </div>
</div>
