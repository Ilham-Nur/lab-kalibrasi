<!-- Modal: Tambah User -->
  <div class="modal-overlay" id="modal-tambah" aria-hidden="true">
    <div class="modal modal-md" role="dialog" aria-modal="true" aria-labelledby="modal-tambah-title">
      <div class="modal-header">
        <h3 class="modal-title" id="modal-tambah-title">
          <i class="bi bi-person-plus-fill"></i> Tambah User Baru
        </h3>
        <button class="modal-close" data-close-modal="modal-tambah" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <form id="form-tambah" novalidate>
          <div class="form-group">
            <label class="form-label" for="t-nama">Nama Lengkap <span class="required">*</span></label>
            <input type="text" id="t-nama" name="nama" class="form-control" placeholder="Nama lengkap" />
            <div class="invalid-feedback">Nama wajib diisi.</div>
          </div>
          <div class="form-group">
            <label class="form-label" for="t-email">Email <span class="required">*</span></label>
            <input type="email" id="t-email" name="email" class="form-control" placeholder="email@domain.com" />
            <div class="invalid-feedback">Email tidak valid.</div>
          </div>
          <div class="form-group">
            <label class="form-label" for="t-role">Role <span class="required">*</span></label>
            <select id="t-role" name="role" class="form-select">
              <option value="">-- Pilih Role --</option>
              <option value="Super Admin">Super Admin</option>
              <option value="Admin">Admin</option>
              <option value="Editor">Editor</option>
              <option value="Viewer">Viewer</option>
            </select>
            <div class="invalid-feedback">Role wajib dipilih.</div>
          </div>
          <div class="form-group">
            <label class="form-label" for="t-status">Status</label>
            <select id="t-status" name="status" class="form-select">
              <option value="Active">Active</option>
              <option value="Pending">Pending</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" data-close-modal="modal-tambah">Batal</button>
        <button class="btn btn-primary" id="btn-simpan-tambah">
          <i class="bi bi-plus-lg"></i> Tambah User
        </button>
      </div>
    </div>
  </div>

  <!-- Modal: View Detail -->
  <div class="modal-overlay" id="modal-view" aria-hidden="true">
    <div class="modal modal-md" role="dialog" aria-modal="true" aria-labelledby="modal-view-title">
      <div class="modal-header">
        <h3 class="modal-title" id="modal-view-title">
          <i class="bi bi-person-badge-fill"></i> Detail User
        </h3>
        <button class="modal-close" data-close-modal="modal-view" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body" id="modal-view-body">
        <!-- Rendered by jQuery -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" data-close-modal="modal-view">Tutup</button>
      </div>
    </div>
  </div>

  <!-- Modal: Edit User -->
  <div class="modal-overlay" id="modal-edit" aria-hidden="true">
    <div class="modal modal-md" role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
      <div class="modal-header">
        <h3 class="modal-title" id="modal-edit-title">
          <i class="bi bi-pencil-fill"></i> Edit User
        </h3>
        <button class="modal-close" data-close-modal="modal-edit" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <form id="form-edit" novalidate>
          <input type="hidden" id="e-id" name="id" />
          <div class="form-group">
            <label class="form-label" for="e-nama">Nama Lengkap <span class="required">*</span></label>
            <input type="text" id="e-nama" name="nama" class="form-control" placeholder="Nama lengkap" />
            <div class="invalid-feedback">Nama wajib diisi.</div>
          </div>
          <div class="form-group">
            <label class="form-label" for="e-email">Email <span class="required">*</span></label>
            <input type="email" id="e-email" name="email" class="form-control" placeholder="email@domain.com" />
            <div class="invalid-feedback">Email tidak valid.</div>
          </div>
          <div class="form-group">
            <label class="form-label" for="e-role">Role <span class="required">*</span></label>
            <select id="e-role" name="role" class="form-select">
              <option value="">-- Pilih Role --</option>
              <option value="Super Admin">Super Admin</option>
              <option value="Admin">Admin</option>
              <option value="Editor">Editor</option>
              <option value="Viewer">Viewer</option>
            </select>
            <div class="invalid-feedback">Role wajib dipilih.</div>
          </div>
          <div class="form-group">
            <label class="form-label" for="e-status">Status</label>
            <select id="e-status" name="status" class="form-select">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Pending">Pending</option>
              <option value="Banned">Banned</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" data-close-modal="modal-edit">Batal</button>
        <button class="btn btn-primary" id="btn-simpan-edit">
          <i class="bi bi-check2-circle"></i> Simpan Perubahan
        </button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-add-standard" aria-hidden="true">
    <div class="modal modal-sm" role="dialog" aria-modal="true" aria-labelledby="modal-add-standard-title">
      <div class="modal-header">
        <h3 class="modal-title" id="modal-add-standard-title">
          <i class="bi bi-file-earmark-plus-fill"></i> Tambah Halaman ISO
        </h3>
        <button class="modal-close" data-close-modal="modal-add-standard" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
      </div>

      <form method="POST" action="{{ route('dokumen-iso.standards.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label" for="standard_name">Nama Halaman <span class="required">*</span></label>
            <input type="text" id="standard_name" name="name" class="form-control" placeholder="Contoh: ISO 14001" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-close-modal="modal-add-standard">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle"></i>
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Delete Confirmation -->
  <div class="modal-overlay" id="modal-delete" aria-hidden="true">
    <div class="modal modal-sm" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title">
      <div class="modal-header modal-header-danger">
        <h3 class="modal-title" id="modal-delete-title">
          <i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Hapus
        </h3>
        <button class="modal-close" data-close-modal="modal-delete" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <div class="delete-confirm-body">
          <div class="delete-icon-wrapper">
            <i class="bi bi-trash3-fill"></i>
          </div>
          <p class="delete-message" id="delete-message">Anda akan menghapus data:</p>
          <p class="delete-target-name" id="delete-target-name">â€”</p>
          <p class="delete-warning">Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin?</p>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" data-close-modal="modal-delete">Batal</button>
        <button class="btn btn-danger" id="btn-confirm-delete">
          <i class="bi bi-trash3-fill"></i> Ya, Hapus
        </button>
      </div>
    </div>
  </div>
