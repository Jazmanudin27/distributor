@extends('layouts.app')
@section('title', $item->exists ? 'Edit Merk' : 'Tambah Merk')
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
                            <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Merk' : 'Tambah Merk Baru' }}</h5>
                            <small class="text-white-50">{{ $item->exists ? 'Perbarui data merk produk' : 'Tambahkan data merk baru untuk klasifikasi barang' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('merk.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ $item->exists ? route('merk.update', $item->id) : route('merk.store') }}" method="POST">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-4">
                            <label for="nama_merk" class="form-label fs-7 fw-bold text-secondary">Nama Merk</label>
                            <div class="input-group input-group-merge input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <i class="fa-solid fa-tag"></i>
                                </span>
                                <input type="text" name="nama_merk" id="nama_merk"
                                    class="form-control form-control-sm border-start-0 @error('nama_merk') is-invalid @enderror"
                                    placeholder="Masukkan nama merk..."
                                    value="{{ old('nama_merk', $item->nama_merk) }}" required>
                            </div>
                            @error('nama_merk')
                                <div class="text-danger small mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Buttons aksi -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('merk.index') }}"
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
