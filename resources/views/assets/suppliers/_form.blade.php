<div class="card">
  <form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
    @csrf
    @if($supplier->exists)
      @method('PUT')
    @endif
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Nama Supplier <span class="required">*</span></label>
          <input class="form-control" name="name" value="{{ old('name', $supplier->name) }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status <span class="required">*</span></label>
          <select class="form-select" name="status">
            @foreach($statuses as $status)
              <option value="{{ $status }}" @selected(old('status', $supplier->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Contact Person</label>
          <input class="form-control" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Telepon</label>
          <input class="form-control" name="phone" value="{{ old('phone', $supplier->phone) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" value="{{ old('email', $supplier->email) }}">
        </div>
        <div class="form-group form-group-full">
          <label class="form-label">Alamat</label>
          <textarea class="form-textarea" name="address">{{ old('address', $supplier->address) }}</textarea>
        </div>
        <div class="form-group form-group-full">
          <label class="form-label">Catatan</label>
          <textarea class="form-textarea" name="notes">{{ old('notes', $supplier->notes) }}</textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <a class="btn btn-outline" href="{{ route('suppliers.index') }}">Batal</a>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </form>
</div>
