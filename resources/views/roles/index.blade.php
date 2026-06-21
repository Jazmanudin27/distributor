@extends('layouts.app')
@section('title', 'Manajemen Hak Akses')

@push('styles')
    <style>
        .permission-matrix {
            overflow-x: auto;
        }

        .permission-matrix table {
            min-width: 600px;
        }

        .permission-matrix th,
        .permission-matrix td {
            white-space: nowrap;
            font-size: 0.8rem;
        }

        .permission-matrix thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .permission-matrix tbody td:first-child {
            position: sticky;
            left: 0;
            background: var(--bs-body-bg);
            z-index: 1;
            border-right: 2px solid var(--bs-border-color);
        }

        .group-header td {
            background: rgba(99, 102, 241, 0.08) !important;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6366f1;
            padding: 6px 12px !important;
        }

        .check-cell {
            text-align: center;
            vertical-align: middle;
        }

        .form-check-input.perm-check {
            width: 1.1rem;
            height: 1.1rem;
            cursor: pointer;
        }

        .role-col-header {
            text-align: center;
            min-width: 90px;
        }

        .badge-role {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .btn-delete-role {
            opacity: 0;
            transition: opacity 0.2s;
        }

        .role-col-header:hover .btn-delete-role {
            opacity: 1;
        }

        .sticky-save-bar {
            position: sticky;
            bottom: 0;
            background: var(--bs-body-bg);
            border-top: 1px solid var(--bs-border-color);
            z-index: 10;
            padding: 12px 0;
            margin: 0 -1px;
        }

        .action-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .action-view {
            background: rgba(34, 197, 94, .15);
            color: #16a34a;
        }

        .action-create {
            background: rgba(59, 130, 246, .15);
            color: #2563eb;
        }

        .action-edit {
            background: rgba(245, 158, 11, .15);
            color: #d97706;
        }

        .action-delete {
            background: rgba(239, 68, 68, .15);
            color: #dc2626;
        }

        .action-other {
            background: rgba(99, 102, 241, .15);
            color: #6366f1;
        }

        .select-all-col {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column gap-4">

        {{-- ── HEADER ── --}}
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-0 fw-bold">
                        <i class="fa-solid fa-user-shield me-2"></i> Manajemen Role & Hak Akses
                    </h5>
                    <small class="text-white-50">Atur role dan permission yang tersedia untuk setiap pengguna</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal"
                        data-bs-target="#modalTambahPermission">
                        <i class="fa-solid fa-key me-1 text-warning"></i> Tambah Permission
                    </button>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahRole">
                        <i class="fa-solid fa-plus me-1 text-primary"></i> Tambah Role
                    </button>
                </div>
            </div>
        </div>

        {{-- ── PERMISSION MATRIX ── --}}
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <form action="{{ route('roles.permissions.update') }}" method="POST" id="permissionsForm">
                    @csrf

                    <div class="permission-matrix">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    {{-- Permission column --}}
                                    <th
                                        style="min-width: 200px; position: sticky; left: 0; z-index: 3; background: #212529;">
                                        <span class="text-white-50 fw-semibold"
                                            style="font-size: 0.75rem; letter-spacing: 0.05em;">PERMISSION</span>
                                    </th>
                                    {{-- Role columns --}}
                                    @foreach ($roles as $role)
                                        <th class="role-col-header">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <span class="badge bg-primary badge-role">{{ $role->name }}</span>
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 text-danger btn-delete-role"
                                                    data-url="{{ route('roles.destroy', $role->id) }}"
                                                    data-name="{{ $role->name }}" title="Hapus Role {{ $role->name }}"
                                                    style="font-size:0.7rem;">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                            {{-- Select All column --}}
                                            <div class="mt-1 select-all-col" title="Pilih semua permission untuk role ini">
                                                <input type="checkbox" class="form-check-input select-all-col-check"
                                                    data-role="{{ $role->id }}"
                                                    style="width:1rem;height:1rem;cursor:pointer;">
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Group permissions by menu name (format: action-menu)
                                    $grouped = [];
                                    foreach ($allPermissions as $perm) {
                                        $parts = explode('-', $perm->name, 2);
                                        $group = count($parts) > 1 ? $parts[1] : 'other';
                                        $action = $parts[0];
                                        $grouped[$group][] = ['perm' => $perm, 'action' => $action];
                                    }
                                    ksort($grouped);

                                    // Map each role's permissions for fast lookup
$rolePermMap = [];
foreach ($roles as $role) {
    $rolePermMap[$role->id] = $role->permissions->pluck('id')->toArray();
}

$actionColors = [
    'view' => 'action-view',
    'create' => 'action-create',
    'edit' => 'action-edit',
    'delete' => 'action-delete',
                                    ];
                                @endphp

                                @forelse ($grouped as $group => $perms)
                                    {{-- Group header row --}}
                                    <tr class="group-header">
                                        <td colspan="{{ $roles->count() + 1 }}">
                                            <i class="fa-solid fa-folder-open me-1"></i>
                                            {{ ucwords(str_replace('_', ' ', $group)) }}
                                            <span class="ms-2 text-secondary fw-normal" style="font-size:0.7rem;">
                                                ({{ count($perms) }} permission)
                                            </span>
                                        </td>
                                    </tr>

                                    @foreach ($perms as $item)
                                        @php
                                            $perm = $item['perm'];
                                            $action = $item['action'];
                                        @endphp
                                        <tr class="hover-row">
                                            <td style="padding-left: 20px;">
                                                <span class="action-tag {{ $actionColors[$action] ?? 'action-other' }}">
                                                    @switch($action)
                                                        @case('view')
                                                            <i class="fa-solid fa-eye fa-xs"></i>
                                                        @break

                                                        @case('create')
                                                            <i class="fa-solid fa-plus fa-xs"></i>
                                                        @break

                                                        @case('edit')
                                                            <i class="fa-solid fa-pen fa-xs"></i>
                                                        @break

                                                        @case('delete')
                                                            <i class="fa-solid fa-trash fa-xs"></i>
                                                        @break

                                                        @default
                                                            <i class="fa-solid fa-key fa-xs"></i>
                                                    @endswitch
                                                    {{ $action }}
                                                </span>
                                                <span class="ms-1 text-secondary"
                                                    style="font-size:0.78rem;">{{ $perm->name }}</span>

                                                {{-- Delete permission button --}}
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 text-danger btn-delete-permission"
                                                    data-url="{{ route('permissions.destroy', $perm->id) }}"
                                                    data-name="{{ $perm->name }}" title="Hapus permission"
                                                    style="opacity:0.3;font-size:0.65rem; transition:opacity .2s;"
                                                    onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.3">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </td>

                                            @foreach ($roles as $role)
                                                <td class="check-cell">
                                                    <input type="checkbox" name="permissions[{{ $role->id }}][]"
                                                        value="{{ $perm->id }}"
                                                        class="form-check-input perm-check col-check-{{ $role->id }}"
                                                        {{ in_array($perm->id, $rolePermMap[$role->id]) ? 'checked' : '' }}>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="{{ $roles->count() + 1 }}" class="text-center py-5 text-muted">
                                                <i class="fa-solid fa-key fa-2x mb-2 d-block opacity-25"></i>
                                                Belum ada permission. Silakan tambah permission terlebih dahulu.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- ── STICKY SAVE BAR ── --}}
                        @if ($allPermissions->count() > 0 && $roles->count() > 0)
                            <div class="sticky-save-bar px-4 d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    Centang permission yang ingin diberikan ke setiap role, lalu klik <strong>Simpan</strong>.
                                    Role <span class="badge bg-danger">Super Admin</span> selalu memiliki semua akses (tidak
                                    perlu diatur).
                                </small>
                                <button type="submit" class="btn btn-primary fw-bold px-4 hover-scale">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

        </div>

        {{-- ── MODAL TAMBAH ROLE ── --}}
        <div class="modal fade" id="modalTambahRole" tabindex="-1" aria-labelledby="modalTambahRoleLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header card-premium-header text-white">
                        <h5 class="modal-title fw-bold" id="modalTambahRoleLabel">
                            <i class="fa-solid fa-shield-halved me-2"></i> Tambah Role Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="role_name" class="form-label fw-semibold">Nama Role</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i
                                            class="fa-solid fa-shield-halved text-primary"></i></span>
                                    <input type="text" name="name" id="role_name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Contoh: Kasir, Admin Gudang..." value="{{ old('name') }}" required
                                        autofocus>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">Nama role harus unik. Role <strong>Super Admin</strong> tidak
                                    dapat dihapus.</div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── MODAL TAMBAH PERMISSION ── --}}
        <div class="modal fade" id="modalTambahPermission" tabindex="-1" aria-labelledby="modalTambahPermLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header card-premium-header text-white">
                        <h5 class="modal-title fw-bold" id="modalTambahPermLabel">
                            <i class="fa-solid fa-key me-2"></i> Tambah Permission Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('permissions.store') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="perm_name" class="form-label fw-semibold">Nama Permission</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-key text-primary"></i></span>
                                        <input type="text" name="name" id="perm_name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            placeholder="Contoh: batal-penjualan, view-barang..." value="{{ old('name') }}"
                                            required>
                                    </div>
                                    @error('name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="alert alert-info d-flex gap-2 align-items-start mt-3 mb-0 py-2 small">
                                <i class="fa-solid fa-circle-info mt-1 flex-shrink-0"></i>
                                <span>Permission yang dibuat akan otomatis ditambahkan ke role <strong>Super Admin</strong>.
                                    Gunakan huruf kecil dan tanda hubung, contoh: <strong>batal-penjualan</strong>,
                                    <strong>view-barang</strong></span>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fa-solid fa-key me-1"></i> Tambah Permission
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Hidden Form for Delete Actions --}}
        <form id="deleteForm" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

    @endsection

    @push('scripts')
        <script>
            $(document).ready(function() {

                // ── Select All per column (role) ──
                $(document).on('change', '.select-all-col-check', function() {
                    const roleId = $(this).data('role');
                    const isChecked = $(this).prop('checked');
                    $('.col-check-' + roleId).prop('checked', isChecked);
                });

                // ── Update Select All state when individual checkbox changes ──
                $(document).on('change', '.perm-check', function() {
                    const classes = $(this).attr('class').match(/col-check-(\d+)/);
                    if (classes) {
                        const roleId = classes[1];
                        const total = $('.col-check-' + roleId).length;
                        const checked = $('.col-check-' + roleId + ':checked').length;
                        $('[data-role="' + roleId + '"]').prop('checked', total === checked);
                    }
                });

                // ── Initialize select-all state on load ──
                @foreach ($roles as $role)
                    (function() {
                        const total = $('.col-check-{{ $role->id }}').length;
                        const checked = $('.col-check-{{ $role->id }}:checked').length;
                        $('[data-role="{{ $role->id }}"]').prop('checked', total === checked && total > 0);
                    })();
                @endforeach

                // ── Open modal if validation error on role/permission store ──
                @if ($errors->any() && old('name'))
                    new bootstrap.Modal(document.getElementById('modalTambahRole')).show();
                @endif
                @if ($errors->any() && old('action'))
                    new bootstrap.Modal(document.getElementById('modalTambahPermission')).show();
                @endif

                // ── Confirm delete role ──
                $(document).on('click', '.btn-delete-role', function(e) {
                    e.preventDefault();
                    const url = $(this).data('url');
                    const name = $(this).data('name');
                    Swal.fire({
                        title: 'Hapus Role?',
                        text: `Semua permission untuk role "${name}" akan dicabut. Lanjutkan?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            $('#deleteForm').attr('action', url).submit();
                        }
                    });
                });

                // ── Confirm delete permission ──
                $(document).on('click', '.btn-delete-permission', function(e) {
                    e.preventDefault();
                    const url = $(this).data('url');
                    const name = $(this).data('name');
                    Swal.fire({
                        title: 'Hapus Permission?',
                        text: `Permission "${name}" akan dihapus dari semua role. Lanjutkan?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            $('#deleteForm').attr('action', url).submit();
                        }
                    });
                });
            });
        </script>
    @endpush
