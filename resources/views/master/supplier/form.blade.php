@extends('layouts.app')
@section('title', $item->exists ? 'Edit Supplier' : 'Tambah Supplier')
@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-8 col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid {{ $item->exists ? 'fa-pen-to-square' : 'fa-plus' }} fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Supplier' : 'Tambah Supplier Baru' }}</h5>
                            <small class="text-white-50">{{ $item->exists ? 'Perbarui data partner supplier' : 'Tambahkan partner supplier baru ke sistem' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('supplier.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ $item->exists ? route('supplier.update', $item->kode_supplier) : route('supplier.store') }}" method="POST">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="row g-3 mb-4">
                            <!-- Kode Supplier -->
                            <div class="col-md-6">
                                <label for="kode_supplier" class="form-label fs-7 fw-bold text-secondary">Kode Supplier</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text {{ $item->exists ? 'bg-light' : 'bg-white' }} border-end-0 text-secondary">
                                        <i class="fa-solid fa-fingerprint"></i>
                                    </span>
                                    <input type="text" name="kode_supplier" id="kode_supplier"
                                        class="form-control form-control-sm border-start-0 @error('kode_supplier') is-invalid @enderror {{ $item->exists ? 'bg-light font-monospace fw-bold' : '' }}"
                                        placeholder="Contoh: SPL001" value="{{ old('kode_supplier', $item->kode_supplier) }}" {{ $item->exists ? 'readonly' : 'required' }}>
                                </div>
                                @error('kode_supplier')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Supplier -->
                            <div class="col-md-6">
                                <label for="nama_supplier" class="form-label fs-7 fw-bold text-secondary">Nama Supplier</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-secondary">
                                        <i class="fa-solid fa-truck"></i>
                                    </span>
                                    <input type="text" name="nama_supplier" id="nama_supplier"
                                        class="form-control form-control-sm border-start-0 @error('nama_supplier') is-invalid @enderror"
                                        placeholder="Nama supplier / perusahaan..." value="{{ old('nama_supplier', $item->nama_supplier) }}" required>
                                </div>
                                @error('nama_supplier')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- No HP -->
                            <div class="col-md-6">
                                <label for="no_hp" class="form-label fs-7 fw-bold text-secondary">No HP</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-secondary">
                                        <i class="fa-solid fa-phone"></i>
                                    </span>
                                    <input type="text" name="no_hp" id="no_hp"
                                        class="form-control form-control-sm border-start-0 @error('no_hp') is-invalid @enderror"
                                        placeholder="No HP / Telepon..." value="{{ old('no_hp', $item->no_hp) }}" required>
                                </div>
                                @error('no_hp')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label fs-7 fw-bold text-secondary">Email</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-secondary">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input type="email" name="email" id="email"
                                        class="form-control form-control-sm border-start-0 @error('email') is-invalid @enderror"
                                        placeholder="Alamat email..." value="{{ old('email', $item->email) }}" required>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label for="status" class="form-label fs-7 fw-bold text-secondary">Status</label>
                                <select name="status" id="status"
                                    class="form-select form-select-sm @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', $item->status ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('status', $item->status) === 0 ? 'selected' : '' }}>Non-Aktif</option>
                                </select>
                                @error('status')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Alamat -->
                            <div class="col-12">
                                <label for="alamat" class="form-label fs-7 fw-bold text-secondary">Alamat</label>
                                <textarea name="alamat" id="alamat" class="form-control form-control-sm @error('alamat') is-invalid @enderror"
                                    rows="3" placeholder="Alamat lengkap supplier..." required>{{ old('alamat', $item->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons aksi -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('supplier.index') }}"
                                class="btn btn-light px-4 fw-semibold border hover-scale">
                                <i class="fa-solid fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold hover-scale">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
