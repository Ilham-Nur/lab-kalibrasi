@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- PAGE HEADER -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Dashboard</h1>
          <p class="page-subtitle">Selamat datang kembali, Admin Utama. Ini ringkasan data Anda hari ini.</p>
        </div>
        <button class="btn btn-primary" id="btn-tambah-user">
          <i class="bi bi-plus-lg"></i>
          <span>Tambah User</span>
        </button>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid">
        <div class="stat-card stat-card-blue">
          <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
          <div class="stat-body">
            <div class="stat-value" id="stat-total">0</div>
            <div class="stat-label">Total Users</div>
          </div>
          <div class="stat-trend"><i class="bi bi-arrow-up-right"></i> <span>+12%</span></div>
        </div>
        <div class="stat-card stat-card-green">
          <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
          <div class="stat-body">
            <div class="stat-value" id="stat-active">0</div>
            <div class="stat-label">Active Users</div>
          </div>
          <div class="stat-trend"><i class="bi bi-arrow-up-right"></i> <span>+5%</span></div>
        </div>
        <div class="stat-card stat-card-amber">
          <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
          <div class="stat-body">
            <div class="stat-value" id="stat-pending">0</div>
            <div class="stat-label">Pending Users</div>
          </div>
          <div class="stat-trend trend-down"><i class="bi bi-arrow-down-right"></i> <span>-2%</span></div>
        </div>
        <div class="stat-card stat-card-red">
          <div class="stat-icon"><i class="bi bi-slash-circle-fill"></i></div>
          <div class="stat-body">
            <div class="stat-value" id="stat-banned">0</div>
            <div class="stat-label">Banned Users</div>
          </div>
          <div class="stat-trend trend-down"><i class="bi bi-arrow-down-right"></i> <span>+1%</span></div>
        </div>
      </div>

      <!-- TABLE SECTION -->
      <div class="card">
        <div class="card-header">
          <div class="card-header-left">
            <h2 class="card-title">Manajemen Users</h2>
            <p class="card-subtitle">Kelola seluruh data pengguna sistem</p>
          </div>
          <div class="card-header-right">
            <div class="rows-per-page-wrapper">
              <label class="form-label-inline">Tampilkan</label>
              <select id="rows-per-page" class="form-select-sm">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              <label class="form-label-inline">data</label>
            </div>
          </div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-filters">
            <div class="search-field">
              <i class="bi bi-search"></i>
              <input type="text" id="table-search" class="form-control" placeholder="Cari nama atau email..." />
            </div>
            <select id="filter-role" class="form-select">
              <option value="">Semua Role</option>
              <option value="Super Admin">Super Admin</option>
              <option value="Admin">Admin</option>
              <option value="Editor">Editor</option>
              <option value="Viewer">Viewer</option>
            </select>
            <select id="filter-status" class="form-select">
              <option value="">Semua Status</option>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Pending">Pending</option>
              <option value="Banned">Banned</option>
            </select>
            <button class="btn btn-outline btn-sm" id="btn-reset-filter">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="data-table" id="data-table">
            <thead>
              <tr>
                <th class="th-no">No</th>
                <th class="sortable" data-col="nama">
                  Nama <i class="bi bi-arrow-down-up sort-icon"></i>
                </th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th class="sortable" data-col="tanggal">
                  Tanggal Dibuat <i class="bi bi-arrow-down-up sort-icon"></i>
                </th>
                <th class="th-aksi">Aksi</th>
              </tr>
            </thead>
            <tbody id="table-body">
              <!-- Rendered by jQuery -->
            </tbody>
          </table>
        </div>

        <div class="table-footer">
          <div class="table-info" id="table-info">Menampilkan 0 data</div>
          <div class="pagination" id="pagination"></div>
        </div>
      </div>

      <!-- FORM SECTION -->
      <div class="card" id="section-form">
        <div class="card-header">
          <div class="card-header-left">
            <h2 class="card-title">Contoh Form Komponen</h2>
            <p class="card-subtitle">Contoh penggunaan komponen form yang tersedia</p>
          </div>
        </div>

        <div class="card-body">
          <form id="sample-form" novalidate>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label" for="f-nama">Nama Lengkap <span class="required">*</span></label>
                <input type="text" id="f-nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" />
                <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
              </div>
              <div class="form-group">
                <label class="form-label" for="f-email">Alamat Email <span class="required">*</span></label>
                <input type="email" id="f-email" name="email" class="form-control" placeholder="contoh@email.com" />
                <div class="invalid-feedback">Email tidak valid.</div>
              </div>
              <div class="form-group">
                <label class="form-label" for="f-role">Role <span class="required">*</span></label>
                <select id="f-role" name="role" class="form-select">
                  <option value="">-- Pilih Role --</option>
                  <option value="Super Admin">Super Admin</option>
                  <option value="Admin">Admin</option>
                  <option value="Editor">Editor</option>
                  <option value="Viewer">Viewer</option>
                </select>
                <div class="invalid-feedback">Role wajib dipilih.</div>
              </div>
              <div class="form-group">
                <label class="form-label" for="f-status">Status</label>
                <select id="f-status" name="status" class="form-select">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="Pending">Pending</option>
                </select>
              </div>
              <div class="form-group form-group-full">
                <label class="form-label" for="f-bio">Bio / Keterangan</label>
                <textarea id="f-bio" name="bio" class="form-textarea" rows="3" placeholder="Tuliskan keterangan singkat..."></textarea>
              </div>
              <div class="form-group form-group-full">
                <label class="form-label">Upload Dokumen <span class="required">*</span></label>
                <div class="file-upload-wrapper" id="file-upload-wrapper">
                  <input type="file" id="f-file" name="file" class="file-input" accept=".pdf,.jpg,.jpeg,.png" />
                  <label for="f-file" class="file-label">
                    <div class="file-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                    <div class="file-text">
                      <span class="file-placeholder">Klik untuk pilih file atau drag &amp; drop</span>
                      <span class="file-meta">PDF, JPG, PNG &mdash; Maks. 2MB</span>
                    </div>
                  </label>
                  <div class="file-preview" id="file-preview"></div>
                </div>
                <div class="invalid-feedback" id="file-error"></div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Notifikasi</label>
              <div class="check-group">
                <label class="form-check">
                  <input type="checkbox" name="notif_email" class="check-input" checked />
                  <span class="check-label">Email Notifikasi</span>
                </label>
                <label class="form-check">
                  <input type="checkbox" name="notif_sms" class="check-input" />
                  <span class="check-label">SMS Notifikasi</span>
                </label>
                <label class="form-check">
                  <input type="checkbox" name="notif_push" class="check-input" />
                  <span class="check-label">Push Notification</span>
                </label>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Tipe Akun <span class="required">*</span></label>
              <div class="check-group" id="radio-tipe">
                <label class="form-check">
                  <input type="radio" name="tipe_akun" class="check-input" value="personal" checked />
                  <span class="check-label">Personal</span>
                </label>
                <label class="form-check">
                  <input type="radio" name="tipe_akun" class="check-input" value="bisnis" />
                  <span class="check-label">Bisnis</span>
                </label>
                <label class="form-check">
                  <input type="radio" name="tipe_akun" class="check-input" value="enterprise" />
                  <span class="check-label">Enterprise</span>
                </label>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn btn-outline" id="btn-reset-form">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Simpan Data
              </button>
            </div>
          </form>
        </div>
      </div>
@endsection

