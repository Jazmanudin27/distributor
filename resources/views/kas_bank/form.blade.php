@extends('layouts.app')
@section('title', $item->exists ? 'Edit Transaksi Kas & Bank' : 'Tambah Transaksi Kas & Bank')
@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-6 col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div
                    class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid {{ $item->exists ? 'fa-pen-to-square' : 'fa-plus' }} fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Transaksi' : 'Tambah Transaksi Baru' }}</h5>
                            <small
                                class="text-white-50">{{ $item->exists ? 'Perbarui data transaksi kas & bank' : 'Catat data transaksi kas & bank baru' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('kas-bank.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ $item->exists ? route('kas-bank.update', $item->id) : route('kas-bank.store') }}"
                        method="POST">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <!-- Tanggal -->
                        <div class="mb-3">
                            <label for="tanggal" class="form-label fs-7 fw-bold text-secondary">Tanggal</label>
                            <div class="input-group input-group-merge input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </span>
                                <input type="date" name="tanggal" id="tanggal"
                                    class="form-control form-control-sm border-start-0 @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') : date('Y-m-d')) }}"
                                    required>
                            </div>
                            @error('tanggal')
                                <div class="text-danger small mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Akun Kas/Bank -->
                        <div class="mb-3">
                            <label for="kode_bank" class="form-label fs-7 fw-bold text-secondary">Akun Kas / Bank</label>
                            <div class="input-group input-group-merge input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <i class="fa-solid fa-building-columns"></i>
                                </span>
                                <select name="kode_bank" id="kode_bank"
                                    class="form-select form-select-sm border-start-0 @error('kode_bank') is-invalid @enderror"
                                    required>
                                    <option value="" disabled
                                        {{ old('kode_bank', $item->kode_bank) ? '' : 'selected' }}>-- Pilih Rekening Kas /
                                        Bank --</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->id }}"
                                            {{ old('kode_bank', $item->kode_bank) == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->nama_bank }} - {{ $bank->no_rekening }} ({{ $bank->atas_nama }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('kode_bank')
                                <div class="text-danger small mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Tipe -->
                        <div class="mb-3">
                            <label for="tipe" class="form-label fs-7 fw-bold text-secondary">Tipe Transaksi</label>
                            <div class="input-group input-group-merge input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <i class="fa-solid fa-right-left"></i>
                                </span>
                                <select name="tipe" id="tipe"
                                    class="form-select form-select-sm border-start-0 @error('tipe') is-invalid @enderror"
                                    required>
                                    <option value="" disabled {{ old('tipe', $item->tipe) ? '' : 'selected' }}>--
                                        Pilih Tipe Transaksi --</option>
                                    <option value="debet" {{ old('tipe', $item->tipe) === 'debet' ? 'selected' : '' }}>
                                        DEBET (Kas Masuk / Penerimaan)</option>
                                    <option value="kredit" {{ old('tipe', $item->tipe) === 'kredit' ? 'selected' : '' }}>
                                        KREDIT (Kas Keluar / Pengeluaran)</option>
                                </select>
                            </div>
                            @error('tipe')
                                <div class="text-danger small mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Jumlah -->
                        <div class="mb-3">
                            <label for="jumlah" class="form-label fs-7 fw-bold text-secondary">Jumlah (Nominal)</label>
                            <div class="input-group input-group-merge input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <strong>Rp</strong>
                                </span>
                                <input type="number" name="jumlah" id="jumlah" min="0.01" step="0.01"
                                    class="form-control form-control-sm border-start-0 @error('jumlah') is-invalid @enderror"
                                    placeholder="Masukkan jumlah nominal..." value="{{ old('jumlah', $item->jumlah) }}"
                                    required>
                            </div>
                            @error('jumlah')
                                <div class="text-danger small mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-4">
                            <label for="keterangan" class="form-label fs-7 fw-bold text-secondary">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3"
                                class="form-control form-control-sm @error('keterangan') is-invalid @enderror"
                                placeholder="Masukkan keterangan detail transaksi...">{{ old('keterangan', $item->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="text-danger small mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Buttons aksi -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('kas-bank.index') }}"
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
