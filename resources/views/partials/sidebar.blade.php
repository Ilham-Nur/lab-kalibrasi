@php
  $activeStandard = request()->route('standard');
  $activeDocument = request()->route('document');

  if (! $activeStandard && $activeDocument?->standard) {
    $activeStandard = $activeDocument->standard;
  }

  if (! $activeStandard && request()->routeIs('dokumen-iso.17025.*', 'dokumen-iso.17025.index')) {
    $activeStandard = ($documentStandards ?? collect())->firstWhere('slug', '17025');
  }
@endphp

<aside id="sidebar" class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">
        <i class="bi bi-hexagon-fill"></i>
      </div>
      <span class="brand-name">LabCal</span>
      <button id="sidebar-close-btn" class="sidebar-close-btn" aria-label="Tutup sidebar">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <ul class="nav-list">
        <li class="nav-item @if (request()->routeIs('dashboard')) active @endif">
          <a href="{{ route('dashboard') }}" class="nav-link" data-page="dashboard" data-tooltip="Dashboard">
            <i class="bi bi-grid-1x2-fill nav-icon"></i>
            <span class="nav-label">Dashboard</span>
          </a>
        </li>
      </ul>

      <div class="nav-section-label">Menu</div>
      <ul class="nav-list">
        <li class="nav-item nav-dropdown">
          <a href="#" class="nav-link nav-submenu-toggle" data-tooltip="Keuangan" aria-expanded="false">
            <i class="bi bi-wallet2 nav-icon"></i>
            <span class="nav-label">Keuangan</span>
            <i class="bi bi-chevron-down nav-chevron"></i>
          </a>
          <ul class="nav-submenu">
            <li><a href="#" class="nav-submenu-link" data-page="keuangan-invoice">Invoice</a></li>
            <li><a href="#" class="nav-submenu-link" data-page="keuangan-pembayaran">Pembayaran</a></li>
            <li><a href="#" class="nav-submenu-link" data-page="keuangan-laporan">Laporan Keuangan</a></li>
          </ul>
        </li>

        <li class="nav-item nav-dropdown">
          <a href="#" class="nav-link nav-submenu-toggle" data-tooltip="Proyek" aria-expanded="false">
            <i class="bi bi-kanban-fill nav-icon"></i>
            <span class="nav-label">Proyek</span>
            <i class="bi bi-chevron-down nav-chevron"></i>
          </a>
          <ul class="nav-submenu">
            <li><a href="#" class="nav-submenu-link" data-page="proyek-daftar">Daftar Proyek</a></li>
            <li><a href="#" class="nav-submenu-link" data-page="proyek-jadwal">Jadwal Proyek</a></li>
            <li><a href="#" class="nav-submenu-link" data-page="proyek-progress">Progress Proyek</a></li>
          </ul>
        </li>

        <li class="nav-item nav-dropdown @if (request()->routeIs('hr.*')) active open @endif">
          <a href="#" class="nav-link nav-submenu-toggle" data-tooltip="HR Management" aria-expanded="{{ request()->routeIs('hr.*') ? 'true' : 'false' }}">
            <i class="bi bi-people-fill nav-icon"></i>
            <span class="nav-label">HR Management</span>
            <i class="bi bi-chevron-down nav-chevron"></i>
          </a>
          <ul class="nav-submenu">
            <li><a href="{{ route('hr.employees.index') }}" class="nav-submenu-link @if(request()->routeIs('hr.employees.*')) active @endif" data-page="hr-karyawan">Data Karyawan</a></li>
            <li><a href="{{ route('hr.job-descriptions.index') }}" class="nav-submenu-link @if(request()->routeIs('hr.job-descriptions.*', 'hr.divisions.*', 'hr.positions.*')) active @endif" data-page="hr-jobdesk">Jobdesk</a></li>
            <li><a href="{{ route('hr.recruitments.index') }}" class="nav-submenu-link @if(request()->routeIs('hr.recruitments.*')) active @endif" data-page="hr-recruitment">Recruitment</a></li>
            <li><a href="{{ route('hr.attendances.index') }}" class="nav-submenu-link @if(request()->routeIs('hr.attendances.*')) active @endif" data-page="hr-absensi">Absensi</a></li>
            <li><a href="{{ route('hr.salaries.index') }}" class="nav-submenu-link @if(request()->routeIs('hr.salaries.*')) active @endif" data-page="hr-payroll">Penggajian</a></li>
          </ul>
        </li>

        <li class="nav-item nav-dropdown">
          <a href="#" class="nav-link nav-submenu-toggle" data-tooltip="Aset dan Peralatan" aria-expanded="false">
            <i class="bi bi-tools nav-icon"></i>
            <span class="nav-label">Aset dan Peralatan</span>
            <i class="bi bi-chevron-down nav-chevron"></i>
          </a>
          <ul class="nav-submenu">
            <li><a href="#" class="nav-submenu-link" data-page="aset-daftar">Daftar Aset</a></li>
            <li><a href="#" class="nav-submenu-link" data-page="aset-kalibrasi">Kalibrasi</a></li>
            <li><a href="#" class="nav-submenu-link" data-page="aset-maintenance">Maintenance</a></li>
          </ul>
        </li>

        <li class="nav-item nav-dropdown @if (request()->routeIs('dokumen-iso.*')) active open @endif">
          <a href="#" class="nav-link nav-submenu-toggle" data-tooltip="Dokumen ISO" aria-expanded="{{ request()->routeIs('dokumen-iso.*') ? 'true' : 'false' }}">
            <i class="bi bi-file-earmark-text-fill nav-icon"></i>
            <span class="nav-label">Dokumen ISO</span>
            <i class="bi bi-chevron-down nav-chevron"></i>
          </a>
          <ul class="nav-submenu">
            @foreach ($documentStandards ?? collect() as $documentStandard)
              <li>
                <a
                  href="{{ route('dokumen-iso.standard.index', $documentStandard) }}"
                  class="nav-submenu-link @if ($activeStandard?->is($documentStandard)) active @endif"
                  data-page="dokumen-iso-{{ $documentStandard->slug }}"
                >
                  {{ $documentStandard->name }}
                </a>
              </li>
            @endforeach
            <li>
              <button class="nav-submenu-link nav-submenu-add" type="button" data-open-modal="modal-add-standard">
                <i class="bi bi-plus-lg"></i>
                Tambah Halaman
              </button>
            </li>
          </ul>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user-mini">
        <div class="avatar-sm">A</div>
        <div class="sidebar-user-info">
          <span class="sidebar-user-name">{{ auth()->user()?->name ?? 'Admin Utama' }}</span>
          <span class="sidebar-user-role">{{ auth()->user()?->username ?? 'admin' }}</span>
        </div>
      </div>
    </div>
  </aside>
