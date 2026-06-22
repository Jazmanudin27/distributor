@extends('layouts.app')
@section('title', 'Data User')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-users me-2"></i> Data User</h5>
                <small class="text-white-50">Kelola akun pengguna sistem</small>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                <i class="fa-solid fa-circle-plus me-1 text-white"></i> Tambah User
            </a>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary text-uppercase fs-7">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Username</th>
                            <th>Email / Login</th>
                            <th>Role</th>
                            <th>NIK</th>
                            <th>Pembatasan Barang</th>
                            <th class="text-center">Status</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userss as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $item->name }}</td>
                                <td class="text-secondary small">{{ $item->email }}</td>
                                <td>
                                    @if ($item->getRoleNames()->isNotEmpty())
                                        <span class="badge bg-primary">{{ $item->getRoleNames()->first() }}</span>
                                    @elseif($item->role)
                                        <span class="badge bg-secondary">{{ $item->role }}</span>
                                    @else
                                        <span class="badge bg-light text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small text-secondary">{{ $item->nik ?? '-' }}</td>
                                <td>
                                    @if ($item->jenis_sales === 'kategori')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle"
                                            title="{{ $item->jenis_barang }}">Kategori:
                                            {{ Str::limit($item->jenis_barang, 25) }}</span>
                                    @elseif($item->jenis_sales === 'merk')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle"
                                            title="{{ $item->jenis_barang }}">Merk:
                                            {{ Str::limit($item->jenis_barang, 25) }}</span>
                                    @else
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle">Semua
                                            Barang</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->status == 1)
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                    @else
                                        <span
                                            class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('users.edit', $item->id) }}"
                                            class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $item->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger delete rounded"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-users d-block fs-3 mb-2 opacity-50"></i>
                                    Belum ada data user.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
