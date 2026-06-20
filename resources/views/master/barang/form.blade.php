@extends('layouts.app')

@section('title', $item->exists ? 'Edit Barang' : 'Tambah Barang')

@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-8 col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div
                    class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid {{ $item->exists ? 'fa-pen-to-square' : 'fa-plus' }} fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Master Barang' : 'Tambah Barang Baru' }}</h5>
                            <small
                                class="text-white-50">{{ $item->exists ? 'Perbarui informasi detail produk atau stok minimal' : 'Tambahkan data barang baru ke dalam sistem' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('barang.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ $item->exists ? route('barang.update', $item->kode_barang) : route('barang.store') }}"
                        method="POST">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <!-- Kode Barang (Otomatis) -->
                            <div class="col-md-6">
                                <label for="kode_barang" class="form-label fs-7 fw-bold text-secondary">Kode Barang
                                    (Sistem)</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text bg-light border-end-0 text-secondary">
                                        <i class="fa-solid fa-desktop"></i>
                                    </span>
                                    <input type="text" name="kode_barang" id="kode_barang"
                                        class="form-control form-control-sm bg-light border-start-0 font-monospace fw-bold"
                                        value="{{ old('kode_barang', $item->kode_barang) }}" readonly>
                                </div>
                                <small class="text-muted fs-8">Otomatis digenerate sistem</small>
                            </div>

                            <!-- Kode Item (Manual) -->
                            <div class="col-md-6">
                                <label for="kode_item" class="form-label fs-7 fw-bold text-secondary">Kode Item
                                    (Manual)</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-secondary">
                                        <i class="fa-solid fa-fingerprint"></i>
                                    </span>
                                    <input type="text" name="kode_item" id="kode_item"
                                        class="form-control form-control-sm border-start-0 @error('kode_item') is-invalid @enderror"
                                        value="{{ old('kode_item', $item->kode_item) }}" placeholder="Contoh: SKU-1002"
                                        required>
                                </div>
                                @error('kode_item')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Barang -->
                            <div class="col-md-12">
                                <label for="nama_barang" class="form-label fs-7 fw-bold text-secondary">Nama Barang</label>
                                <input type="text" name="nama_barang" id="nama_barang"
                                    class="form-control form-control-sm @error('nama_barang') is-invalid @enderror"
                                    placeholder="Masukkan nama barang..."
                                    value="{{ old('nama_barang', $item->nama_barang) }}" required>
                                @error('nama_barang')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kategori -->
                            <div class="col-md-6">
                                <label for="kategori" class="form-label fs-7 fw-bold text-secondary">Kategori</label>
                                <select name="kategori" id="kategori"
                                    class="form-select form-select-sm @error('kategori') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($kategoris as $opt)
                                        <option value="{{ $opt->nama_kategori }}"
                                            {{ old('kategori', $item->kategori) == $opt->nama_kategori ? 'selected' : '' }}>
                                            {{ $opt->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Merk -->
                            <div class="col-md-6">
                                <label for="merk" class="form-label fs-7 fw-bold text-secondary">Merk</label>
                                <select name="merk" id="merk"
                                    class="form-select form-select-sm @error('merk') is-invalid @enderror" required>
                                    <option value="">-- Pilih Merk --</option>
                                    @foreach ($merks as $opt)
                                        <option value="{{ $opt->nama_merk }}"
                                            {{ old('merk', $item->merk) == $opt->nama_merk ? 'selected' : '' }}>
                                            {{ $opt->nama_merk }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('merk')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Supplier -->
                            <div class="col-md-6">
                                <label for="kode_supplier" class="form-label fs-7 fw-bold text-secondary">Supplier</label>
                                <select name="kode_supplier" id="kode_supplier"
                                    class="form-select form-select-sm @error('kode_supplier') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach ($suppliers as $opt)
                                        <option value="{{ $opt->kode_supplier }}"
                                            {{ old('kode_supplier', $item->kode_supplier) == $opt->kode_supplier ? 'selected' : '' }}>
                                            {{ $opt->nama_supplier }} ({{ $opt->kode_supplier }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('kode_supplier')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Stok Minimal -->
                            <div class="col-md-3">
                                <label for="stok_min" class="form-label fs-7 fw-bold text-secondary">Stok Minimal</label>
                                <input type="number" name="stok_min" id="stok_min"
                                    class="form-control form-control-sm @error('stok_min') is-invalid @enderror"
                                    min="0" value="{{ old('stok_min', $item->stok_min ?? 0) }}" required>
                                @error('stok_min')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-3">
                                <label for="status" class="form-label fs-7 fw-bold text-secondary">Status</label>
                                <select name="status" id="status"
                                    class="form-select form-select-sm @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', $item->status ?? 1) == 1 ? 'selected' : '' }}>
                                        Aktif</option>
                                    <option value="0" {{ old('status', $item->status) === 0 ? 'selected' : '' }}>
                                        Non-Aktif</option>
                                </select>
                                @error('status')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Keterangan -->
                            <div class="col-12">
                                <label for="keterangan" class="form-label fs-7 fw-bold text-secondary">Keterangan</label>
                                <textarea name="keterangan" id="keterangan"
                                    class="form-control form-control-sm @error('keterangan') is-invalid @enderror" rows="3"
                                    placeholder="Tambahkan catatan atau keterangan produk...">{{ old('keterangan', $item->keterangan) }}</textarea>
                                @error('keterangan')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons aksi -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('barang.index') }}"
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
