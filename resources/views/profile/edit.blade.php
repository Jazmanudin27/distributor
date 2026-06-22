@extends('layouts.app')

@section('title', 'Ubah Profil - Distributor')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-white">Pengaturan Profil</h4>
            <p class="text-secondary small mb-0">Perbarui username dan password login Anda di sini.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold text-white">
                        <i class="fa-solid fa-user-gear me-2 text-primary"></i> Detail Akun Anda
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>Gagal memperbarui profil:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf

                        <!-- Username (Name) -->
                        <div class="mb-3">
                            <label for="usernameInput" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user text-secondary"></i></span>
                                <input type="text" id="usernameInput" name="name" class="form-control" value="{{ old('name', $user->name) }}" placeholder="Masukkan username baru" required>
                            </div>
                            <div class="form-text text-secondary small">Nama ini akan digunakan untuk masuk/login ke dalam sistem.</div>
                        </div>

                        <!-- Email (Read-only for safety/reference) -->
                        <div class="mb-3">
                            <label class="form-label">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope text-secondary"></i></span>
                                <input type="email" class="form-control bg-secondary-subtle text-secondary" value="{{ $user->email }}" readonly style="cursor: not-allowed;">
                            </div>
                        </div>

                        <hr class="my-4 border-white-10">

                        <h6 class="fw-bold text-white-50 mb-3"><i class="fa-solid fa-key me-2 text-warning"></i>Ubah Password (Kosongkan jika tidak ingin diubah)</h6>

                        <!-- Password Baru -->
                        <div class="mb-3">
                            <label for="passwordInput" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock text-secondary"></i></span>
                                <input type="password" id="passwordInput" name="password" class="form-control" placeholder="Minimal 6 karakter">
                            </div>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="mb-3">
                            <label for="passwordConfInput" class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock-open text-secondary"></i></span>
                                <input type="password" id="passwordConfInput" name="password_confirmation" class="form-control" placeholder="Masukkan ulang password baru">
                            </div>
                        </div>

                        <hr class="my-4 border-white-10">

                        <!-- Password Lama (Verifikasi) -->
                        <div class="mb-4">
                            <label for="currentPasswordInput" class="form-label text-warning"><i class="fa-solid fa-shield-halved me-1"></i>Password Saat Ini (Wajib)</label>
                            <div class="input-group">
                                <span class="input-group-text border-warning-subtle"><i class="fa-solid fa-check text-warning"></i></span>
                                <input type="password" id="currentPasswordInput" name="current_password" class="form-control border-warning-subtle" placeholder="Masukkan password lama untuk konfirmasi" required>
                            </div>
                            <div class="form-text text-warning small mt-1">Harap verifikasi password Anda saat ini untuk menyimpan perubahan.</div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4 fw-bold">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
