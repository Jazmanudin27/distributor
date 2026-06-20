@extends('layouts.app')
@section('title', $item->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan')
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
                            <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}</h5>
                            <small class="text-white-50">{{ $item->exists ? 'Perbarui data pelanggan terdaftar' : 'Tambahkan data pelanggan baru ke sistem' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('pelanggan.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ $item->exists ? route('pelanggan.update', $item->kode_pelanggan) : route('pelanggan.store') }}" method="POST">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="row g-3 mb-4">
                            <!-- Kode Pelanggan -->
                            <div class="col-md-6">
                                <label for="kode_pelanggan" class="form-label fs-7 fw-bold text-secondary">Kode Pelanggan</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text {{ $item->exists ? 'bg-light' : 'bg-white' }} border-end-0 text-secondary">
                                        <i class="fa-solid fa-fingerprint"></i>
                                    </span>
                                    <input type="text" name="kode_pelanggan" id="kode_pelanggan"
                                        class="form-control form-control-sm border-start-0 @error('kode_pelanggan') is-invalid @enderror {{ $item->exists ? 'bg-light font-monospace fw-bold' : '' }}"
                                        placeholder="Contoh: PLG001" value="{{ old('kode_pelanggan', $item->kode_pelanggan) }}" {{ $item->exists ? 'readonly' : 'required' }}>
                                </div>
                                @error('kode_pelanggan')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Pelanggan -->
                            <div class="col-md-6">
                                <label for="nama_pelanggan" class="form-label fs-7 fw-bold text-secondary">Nama Pelanggan</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-secondary">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <input type="text" name="nama_pelanggan" id="nama_pelanggan"
                                        class="form-control form-control-sm border-start-0 @error('nama_pelanggan') is-invalid @enderror"
                                        placeholder="Nama lengkap pelanggan..." value="{{ old('nama_pelanggan', $item->nama_pelanggan) }}" required>
                                </div>
                                @error('nama_pelanggan')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- No HP -->
                            <div class="col-md-6">
                                <label for="no_hp_pelanggan" class="form-label fs-7 fw-bold text-secondary">No HP</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-secondary">
                                        <i class="fa-solid fa-phone"></i>
                                    </span>
                                    <input type="text" name="no_hp_pelanggan" id="no_hp_pelanggan"
                                        class="form-control form-control-sm border-start-0 @error('no_hp_pelanggan') is-invalid @enderror"
                                        placeholder="No HP / Whatsapp..." value="{{ old('no_hp_pelanggan', $item->no_hp_pelanggan) }}" required>
                                </div>
                                @error('no_hp_pelanggan')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Limit -->
                            <div class="col-md-6">
                                <label for="limit_pelanggan" class="form-label fs-7 fw-bold text-secondary">Limit Piutang / Kredit</label>
                                <div class="input-group input-group-merge input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-secondary">
                                        Rp
                                    </span>
                                    <input type="number" name="limit_pelanggan" id="limit_pelanggan"
                                        class="form-control form-control-sm border-start-0 @error('limit_pelanggan') is-invalid @enderror"
                                        placeholder="0" value="{{ old('limit_pelanggan', $item->exists ? (int)$item->limit_pelanggan : 0) }}" required>
                                </div>
                                @error('limit_pelanggan')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Metode Bayar -->
                            <div class="col-md-6">
                                <label for="metode_bayar" class="form-label fs-7 fw-bold text-secondary">Metode Bayar</label>
                                <select name="metode_bayar" id="metode_bayar"
                                    class="form-select form-select-sm @error('metode_bayar') is-invalid @enderror" required>
                                    <option value="Cash" {{ old('metode_bayar', $item->metode_bayar) === 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="Kredit" {{ old('metode_bayar', $item->metode_bayar) === 'Kredit' ? 'selected' : '' }}>Kredit</option>
                                    <option value="Transfer" {{ old('metode_bayar', $item->metode_bayar) === 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                </select>
                                @error('metode_bayar')
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

                            <!-- Jenis Pelanggan -->
                            <div class="col-md-6">
                                <label for="jenis_pelanggan" class="form-label fs-7 fw-bold text-secondary">Jenis Pelanggan</label>
                                <select name="jenis_pelanggan" id="jenis_pelanggan"
                                    class="form-select form-select-sm @error('jenis_pelanggan') is-invalid @enderror">
                                    <option value="0" {{ old('jenis_pelanggan', $item->jenis_pelanggan) != '1' ? 'selected' : '' }}>Regular (Blokir jika Overdue)</option>
                                    <option value="1" {{ old('jenis_pelanggan', $item->jenis_pelanggan) == '1' ? 'selected' : '' }}>Khusus (Bisa transaksi saat Overdue)</option>
                                </select>
                                @error('jenis_pelanggan')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Wilayah -->
                            <div class="col-md-6">
                                <label for="kode_wilayah" class="form-label fs-7 fw-bold text-secondary">Wilayah</label>
                                <select name="kode_wilayah" id="kode_wilayah"
                                    class="form-select form-select-sm @error('kode_wilayah') is-invalid @enderror">
                                    <option value="">-- Pilih Wilayah --</option>
                                    @foreach ($wilayahs as $w)
                                        <option value="{{ $w->kode_wilayah }}"
                                            {{ old('kode_wilayah', $item->kode_wilayah) == $w->kode_wilayah ? 'selected' : '' }}>
                                            {{ $w->nama_wilayah }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kode_wilayah')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sub Wilayah -->
                            <div class="col-md-6">
                                <label for="sub_wilayah" class="form-label fs-7 fw-bold text-secondary">Sub Wilayah</label>
                                <select name="sub_wilayah" id="sub_wilayah"
                                    class="form-select form-select-sm @error('sub_wilayah') is-invalid @enderror">
                                    <option value="">-- Pilih Sub Wilayah --</option>
                                    @foreach ($subWilayahs as $sw)
                                        <option value="{{ $sw->kode_wilayah }}"
                                            {{ old('sub_wilayah', $item->sub_wilayah) == $sw->kode_wilayah ? 'selected' : '' }}>
                                            {{ $sw->nama_wilayah }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sub_wilayah')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Alamat Pelanggan -->
                            <div class="col-12">
                                <label for="alamat_pelanggan" class="form-label fs-7 fw-bold text-secondary">Alamat Pelanggan</label>
                                <textarea name="alamat_pelanggan" id="alamat_pelanggan"
                                    class="form-control form-control-sm @error('alamat_pelanggan') is-invalid @enderror"
                                    rows="3" placeholder="Alamat lengkap pelanggan..." required>{{ old('alamat_pelanggan', $item->alamat_pelanggan) }}</textarea>
                                @error('alamat_pelanggan')
                                    <div class="text-danger small mt-1"><i
                                            class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons aksi -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('pelanggan.index') }}"
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
