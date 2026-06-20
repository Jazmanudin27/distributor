@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-0 fw-bold">
                <i class="fa-solid fa-user-shield me-2"></i> Hak Akses Menu & Role
            </h5>
            <small class="text-white-50">Kelola role dan berikan izin akses menu per role.</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-light btn-sm fw-bold hover-scale" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                <i class="fa-solid fa-plus me-1"></i> Tambah Permission
            </button>
            <button class="btn btn-light btn-sm fw-bold hover-scale text-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="fa-solid fa-plus me-1"></i> Tambah Role
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @foreach($roles as $role)
            <form id="delete-role-{{ $role->id }}" action="{{ route('roles.destroy', $role) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        @foreach($permissionGroups as $groupName => $perms)
            @foreach($perms as $perm)
            <form id="delete-perm-{{ $perm['id'] }}" action="{{ route('permissions.destroy', $perm['id']) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
            @endforeach
        @endforeach

        <form action="{{ route('roles.permissions.update') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-secondary text-center">
                        <tr>
                            <th rowspan="2" class="align-middle text-start" style="width: 250px;">Menu / Fitur</th>
                            <th colspan="{{ count($roles) }}">Roles</th>
                        </tr>
                        <tr>
                            @foreach($roles as $role)
                                <th>
                                    {{ $role->name }}
                                    <div class="mt-1">
                                        <button type="button" form="delete-role-{{ $role->id }}" class="btn btn-sm btn-outline-danger py-0 px-1" style="font-size: 0.7rem;" onclick="if(confirm('Hapus role ini?')) document.getElementById('delete-role-{{ $role->id }}').submit();">Hapus</button>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissionGroups as $groupName => $perms)
                            <tr class="table-secondary">
                                <td colspan="{{ count($roles) + 1 }}" class="fw-bold text-uppercase fs-7 text-secondary">
                                    {{ $groupName }}
                                </td>
                            </tr>
                            @foreach($perms as $perm)
                                <tr>
                                    <td class="ps-4 position-relative">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                {{ ucfirst($perm['action']) }} {{ ucfirst($groupName) }}
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $perm['name'] }}</small>
                                            </div>
                                            <button type="button" form="delete-perm-{{ $perm['id'] }}" class="btn btn-sm text-danger px-1 py-0" 
                                                onclick="if(confirm('Hapus permission ini?')) document.getElementById('delete-perm-{{ $perm['id'] }}').submit();" title="Hapus Permission">
                                                <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @foreach($roles as $role)
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" name="permissions[{{ $role->id }}][]" value="{{ $perm['id'] }}" 
                                                    {{ $role->hasPermissionTo($perm['name']) ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="{{ count($roles) + 1 }}" class="text-center text-muted py-4">Belum ada data permission terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary fw-bold px-4">
                    <i class="fa-solid fa-save me-2"></i> Simpan Hak Akses
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Add Role -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Role Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Role</label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Kasir, Gudang, Sales">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Add Permission -->
<div class="modal fade" id="addPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-key me-2"></i>Tambah Permission Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Aksi (Action)</label>
                        <select name="action" class="form-select" required>
                            <option value="">-- Pilih Aksi --</option>
                            <option value="view">View (Melihat)</option>
                            <option value="create">Create (Menambah)</option>
                            <option value="edit">Edit (Mengubah)</option>
                            <option value="delete">Delete (Menghapus)</option>
                            <option value="approve">Approve (Menyetujui)</option>
                            <option value="print">Print (Mencetak)</option>
                            <option value="export">Export (Mengunduh)</option>
                            <option value="import">Import (Mengunggah)</option>
                        </select>
                        <small class="text-muted mt-1 d-block" style="font-size:0.75rem;">Contoh: <b>view</b>, <b>create</b></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nama Menu / Fitur</label>
                        <input type="text" name="menu" class="form-control" required placeholder="Contoh: laporan_sales, mutasi_barang">
                        <small class="text-muted mt-1 d-block" style="font-size:0.75rem;">Jangan gunakan spasi, gunakan underscore (_)</small>
                    </div>
                    <div class="alert alert-light border shadow-sm p-3 mt-3 mb-0" style="font-size:0.85rem;">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i> Permission akan digenerate dengan format: <b>aksi-menu</b> (contoh: <code>view-laporan_sales</code>)
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-save me-1"></i> Simpan Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
