@extends('layouts.app')
@section('title', $item->exists ? 'Edit Wilayah' : 'Tambah Wilayah')
@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-6 col-md-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid {{ $item->exists ? 'fa-pen-to-square' : 'fa-plus' }} fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Wilayah' : 'Tambah Wilayah Baru' }}</h5>
                            <small class="text-white-50">{{ $item->exists ? 'Perbarui data wilayah pemasaran' : 'Tambahkan data wilayah baru' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('wilayah.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ $item->exists ? route('wilayah.update', $item->kode_wilayah) : route('wilayah.store') }}" method="POST">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="kode_wilayah" class="form-label fs-7 fw-bold text-secondary">
                                Kode Wilayah
                                @if(!$item->exists)
                                    <small class="text-muted fw-normal">(Opsional - Otomatis jika dikosongkan)</small>
                                @endif
                            </label>
                            <div class="input-group input-group-merge input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <i class="fa-solid fa-hashtag"></i>
                                </span>
                                <input type="number" name="kode_wilayah" id="kode_wilayah"
                                    class="form-control form-control-sm border-start-0 @error('kode_wilayah') is-invalid @enderror"
                                    placeholder="Contoh: 1"
                                    value="{{ old('kode_wilayah', $item->kode_wilayah) }}"
                                    {{ $item->exists ? 'required' : '' }}>
                            </div>
                            @error('kode_wilayah')
                                <div class="text-danger small mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="nama_wilayah" class="form-label fs-7 fw-bold text-secondary">Nama Wilayah</label>
                            <div class="input-group input-group-merge input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </span>
                                <input type="text" name="nama_wilayah" id="nama_wilayah"
                                    class="form-control form-control-sm border-start-0 @error('nama_wilayah') is-invalid @enderror"
                                    placeholder="Masukkan nama wilayah..."
                                    value="{{ old('nama_wilayah', $item->nama_wilayah) }}" required>
                            </div>
                            @error('nama_wilayah')
                                <div class="text-danger small mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Buttons aksi -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('wilayah.index') }}"
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
