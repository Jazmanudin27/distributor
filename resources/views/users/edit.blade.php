@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="row justify-content-start">
    <div class="col-lg-6 col-md-12">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                        style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-user-pen fs-5"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Edit User</h5>
                        <small class="text-white-50">Perbarui data akun pengguna</small>
                    </div>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('users.update', $row->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fs-7 fw-bold text-secondary">Username</label>
                        <input type="text" name="name" id="name"
                            class="form-control form-control-sm @error('name') is-invalid @enderror"
                            value="{{ old('name', $row->name) }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fs-7 fw-bold text-secondary">Email / Username Login</label>
                        <input type="text" name="email" id="email"
                            class="form-control form-control-sm @error('email') is-invalid @enderror"
                            value="{{ old('email', $row->email) }}" required>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fs-7 fw-bold text-secondary">Password</label>
                        <input type="password" name="password" id="password"
                            class="form-control form-control-sm @error('password') is-invalid @enderror"
                            placeholder="Kosongkan jika tidak ingin mengubah password">
                        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label fs-7 fw-bold text-secondary">Role</label>
                        <select name="role" id="role"
                            class="form-select form-select-sm @error('role') is-invalid @enderror" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $r)
                                @php
                                    $currentRole = old('role', $row->getRoleNames()->first() ?? $row->role);
                                @endphp
                                <option value="{{ $r->name }}" {{ $currentRole == $r->name ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="nik" class="form-label fs-7 fw-bold text-secondary">NIK</label>
                        <input type="text" name="nik" id="nik"
                            class="form-control form-control-sm @error('nik') is-invalid @enderror"
                            value="{{ old('nik', $row->nik) }}">
                        @error('nik')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    @php
                        $selectedBarang = explode(',', $row->jenis_barang ?? '');
                    @endphp

                    <div class="mb-3">
                        <label for="jenis_sales" class="form-label fs-7 fw-bold text-secondary">Jenis Pembatasan Barang</label>
                        <select name="jenis_sales" id="jenis_sales" class="form-select form-select-sm" onchange="toggleRestrictionFields()">
                            <option value="semua" {{ old('jenis_sales', $row->jenis_sales) == 'semua' || empty($row->jenis_sales) ? 'selected' : '' }}>Semua Barang (Tanpa Batasan)</option>
                            <option value="kategori" {{ old('jenis_sales', $row->jenis_sales) == 'kategori' ? 'selected' : '' }}>Per Kategori</option>
                            <option value="merk" {{ old('jenis_sales', $row->jenis_sales) == 'merk' ? 'selected' : '' }}>Per Merk</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="kategori-container">
                        <label for="jenis_barang_kategori" class="form-label fs-7 fw-bold text-secondary">Pilih Kategori (Bisa Pilih Banyak)</label>
                        <select name="jenis_barang[]" id="jenis_barang_kategori" class="form-select form-select-sm" multiple style="height: 120px;">
                            @foreach($kategoris as $k)
                                <option value="{{ $k->nama_kategori }}" {{ in_array($k->nama_kategori, old('jenis_barang', $selectedBarang)) ? 'selected' : '' }}>
                                    {{ $k->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Gunakan Ctrl + Klik untuk memilih lebih dari satu kategori.</small>
                    </div>

                    <div class="mb-3 d-none" id="merk-container">
                        <label for="jenis_barang_merk" class="form-label fs-7 fw-bold text-secondary">Pilih Merk (Bisa Pilih Banyak)</label>
                        <select name="jenis_barang[]" id="jenis_barang_merk" class="form-select form-select-sm" multiple style="height: 120px;">
                            @foreach($merks as $m)
                                <option value="{{ $m->nama_merk }}" {{ in_array($m->nama_merk, old('jenis_barang', $selectedBarang)) ? 'selected' : '' }}>
                                    {{ $m->nama_merk }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Gunakan Ctrl + Klik untuk memilih lebih dari satu merk.</small>
                    </div>

                    <div class="mb-3 form-check ms-1">
                        <input type="checkbox" name="is_kanvas" id="is_kanvas" class="form-check-input" value="1" {{ old('is_kanvas', $row->is_kanvas) ? 'checked' : '' }}>
                        <label for="is_kanvas" class="form-check-label fs-7 fw-bold text-secondary">Sales Kanvas (Gunakan sistem kanvas barang)</label>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fs-7 fw-bold text-secondary">Status</label>
                        <select name="status" id="status"
                            class="form-select form-select-sm @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $row->status) == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $row->status) == '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-light px-4 fw-semibold border hover-scale">
                            <i class="fa-solid fa-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold hover-scale">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRestrictionFields() {
        const jenisSales = document.getElementById('jenis_sales').value;
        const kategoriContainer = document.getElementById('kategori-container');
        const merkContainer = document.getElementById('merk-container');
        const selectKategori = document.getElementById('jenis_barang_kategori');
        const selectMerk = document.getElementById('jenis_barang_merk');
        
        kategoriContainer.classList.add('d-none');
        merkContainer.classList.add('d-none');
        selectKategori.disabled = true;
        selectMerk.disabled = true;

        if (jenisSales === 'kategori') {
            kategoriContainer.classList.remove('d-none');
            selectKategori.disabled = false;
        } else if (jenisSales === 'merk') {
            merkContainer.classList.remove('d-none');
            selectMerk.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleRestrictionFields();
    });
</script>
@endsection
