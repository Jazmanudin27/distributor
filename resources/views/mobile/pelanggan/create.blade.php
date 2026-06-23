@extends('layouts.mobile')

@section('title', 'Daftar Pelanggan Baru')

@push('styles')
    <style>
        .form-section-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            color: #818cf8;
            margin-top: 20px;
            margin-bottom: 12px;
            padding-bottom: 4px;
            border-bottom: 1px solid rgba(129, 140, 248, 0.2);
        }

        .upload-card {
            border: 2px dashed rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.01);
            border-radius: 16px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upload-card:active {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }

        .preview-img {
            max-height: 120px;
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
    </style>
@endpush

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.kunjungan.index') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Daftar Pelanggan Baru</h5>
    </div>

    <form action="{{ route('mobile.pelanggan.store') }}" method="POST" enctype="multipart/form-data" class="pb-5">
        @csrf

        <!-- Section: Informasi Toko -->
        <div class="mobile-card">
            <div class="form-section-title mt-0">Informasi Toko</div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Nama Toko / Pelanggan *</label>
                <input type="text" name="nama_pelanggan" class="form-control form-control-mobile"
                    placeholder="Contoh: Toko Maju Jaya" value="{{ old('nama_pelanggan') }}" required>
                @error('nama_pelanggan')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Alamat Lengkap Toko *</label>
                <textarea name="alamat_pelanggan" class="form-control form-control-mobile" rows="2"
                    placeholder="Nama jalan, nomor, RT/RW, kelurahan..." required>{{ old('alamat_pelanggan') }}</textarea>
                @error('alamat_pelanggan')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Alamat Pengiriman (Toko) <small
                        class="text-secondary-50">(Kosongkan jika sama)</small></label>
                <textarea name="alamat_toko" class="form-control form-control-mobile" rows="2"
                    placeholder="Alamat kirim barang jika berbeda...">{{ old('alamat_toko') }}</textarea>
                @error('alamat_toko')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold mb-1">No. HP / Whatsapp *</label>
                <input type="tel" name="no_hp_pelanggan" class="form-control form-control-mobile font-monospace"
                    placeholder="Contoh: 08123456789" value="{{ old('no_hp_pelanggan') }}" required>
                @error('no_hp_pelanggan')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Section: Wilayah & Pembayaran -->
        <div class="mobile-card">
            <div class="form-section-title mt-0">Wilayah & Pembayaran</div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Wilayah *</label>
                <select name="kode_wilayah" class="form-control form-control-mobile" required>
                    <option value="">-- Pilih Wilayah --</option>
                    @foreach ($wilayahs as $w)
                        <option value="{{ $w->kode_wilayah }}"
                            {{ old('kode_wilayah') == $w->kode_wilayah ? 'selected' : '' }}>
                            {{ $w->nama_wilayah }}
                        </option>
                    @endforeach
                </select>
                @error('kode_wilayah')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Sub Wilayah *</label>
                <select name="sub_wilayah" class="form-control form-control-mobile" required>
                    <option value="">-- Pilih Sub Wilayah --</option>
                    @foreach ($subWilayahs as $sw)
                        <option value="{{ $sw->kode_wilayah }}"
                            {{ old('sub_wilayah') == $sw->kode_wilayah ? 'selected' : '' }}>
                            {{ $sw->nama_wilayah }}
                        </option>
                    @endforeach
                </select>
                @error('sub_wilayah')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Metode Pembayaran *</label>
                <select name="metode_bayar" class="form-control form-control-mobile" required>
                    <option value="Cash" {{ old('metode_bayar') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Kredit" {{ old('metode_bayar') == 'Kredit' ? 'selected' : '' }}>Kredit</option>
                    <option value="Transfer" {{ old('metode_bayar') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
                @error('metode_bayar')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Section: GPS & Berkas -->
        <div class="mobile-card">
            <div class="form-section-title mt-0">Lokasi & Berkas Pendukung</div>

            <!-- GPS Coordinates -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-semibold">GPS Geolokasi</span>
                    <span id="gps-status" class="badge bg-secondary">Mendeteksi...</span>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="text" name="latitude" id="latitude" readonly
                            class="form-control form-control-mobile font-monospace py-2" placeholder="Latitude"
                            value="{{ old('latitude') }}" style="background-color: rgba(255,255,255,0.02) !important;">
                    </div>
                    <div class="col-6">
                        <input type="text" name="longitude" id="longitude" readonly
                            class="form-control form-control-mobile font-monospace py-2" placeholder="Longitude"
                            value="{{ old('longitude') }}" style="background-color: rgba(255,255,255,0.02) !important;">
                    </div>
                </div>
            </div>

            <!-- File Uploads -->
            <div class="row g-3 mb-2">
                <div class="col-6">
                    <label class="form-label text-secondary small fw-semibold mb-1">Foto Toko</label>
                    <div class="upload-card" onclick="document.getElementById('input-foto').click()">
                        <i class="fa-solid fa-store text-secondary mb-1" style="font-size: 1.3rem;"></i>
                        <span class="d-block text-secondary-50" style="font-size: 0.65rem;" id="lbl-foto">Pilih
                            Foto</span>
                        <input type="file" name="foto" id="input-foto" accept="image/*" class="d-none"
                            onchange="previewFile(this, 'preview-foto', 'lbl-foto')">
                    </div>
                    <img id="preview-foto" class="preview-img">
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary small fw-semibold mb-1">Foto KTP Pemilik</label>
                    <div class="upload-card" onclick="document.getElementById('input-ktp').click()">
                        <i class="fa-solid fa-id-card text-secondary mb-1" style="font-size: 1.3rem;"></i>
                        <span class="d-block text-secondary-50" style="font-size: 0.65rem;" id="lbl-ktp">Pilih
                            Foto</span>
                        <input type="file" name="foto_ktp" id="input-ktp" accept="image/*" class="d-none"
                            onchange="previewFile(this, 'preview-ktp', 'lbl-ktp')">
                    </div>
                    <img id="preview-ktp" class="preview-img">
                </div>
            </div>
        </div>

        <button type="submit" id="btn-submit" class="btn btn-mobile btn-mobile-primary w-100 py-3 mt-2 fw-bold">
            <i class="fa-solid fa-floppy-disk me-2"></i> Daftarkan Pelanggan
        </button>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 on dropdowns
            $('select[name="kode_wilayah"]').select2({
                placeholder: "-- Pilih Wilayah --",
                allowClear: true
            });
            $('select[name="sub_wilayah"]').select2({
                placeholder: "-- Pilih Sub Wilayah --",
                allowClear: true
            });
            $('select[name="metode_bayar"]').select2({
                placeholder: "-- Pilih Metode Pembayaran --",
                minimumResultsForSearch: Infinity
            });

            // Geolocation Capturing
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;

                    const statusEl = document.getElementById('gps-status');
                    statusEl.innerText = 'Lokasi Terkunci';
                    statusEl.className = 'badge bg-success';
                }, function(error) {
                    console.error('GPS Error:', error);
                    const statusEl = document.getElementById('gps-status');
                    statusEl.innerText = 'GPS Dinonaktifkan';
                    statusEl.className = 'badge bg-danger';
                }, {
                    enableHighAccuracy: true,
                    timeout: 8000,
                    maximumAge: 0
                });
            } else {
                const statusEl = document.getElementById('gps-status');
                statusEl.innerText = 'Tidak Didukung';
                statusEl.className = 'badge bg-warning';
            }
        });

        let compressionInProgress = 0;

        function updateSubmitButtonState() {
            const btnSubmit = document.getElementById('btn-submit');
            if (btnSubmit) {
                if (compressionInProgress > 0) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Memproses Foto...';
                } else {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i> Daftarkan Pelanggan';
                }
            }
        }

        function compressAndResizeImage(file, maxWidth = 1024, maxHeight = 1024, quality = 0.7) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(event) {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = function() {
                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > maxWidth) {
                                height = Math.round((height * maxWidth) / width);
                                width = maxWidth;
                            }
                        } else {
                            if (height > maxHeight) {
                                width = Math.round((width * maxHeight) / height);
                                height = maxHeight;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob((blob) => {
                            if (blob) {
                                let filename = file.name;
                                const extIndex = filename.lastIndexOf('.');
                                if (extIndex !== -1) {
                                    filename = filename.substring(0, extIndex) + '.jpg';
                                } else {
                                    filename = filename + '.jpg';
                                }

                                const compressedFile = new File([blob], filename, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(compressedFile);
                            } else {
                                reject(new Error('Canvas compression failed'));
                            }
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = function(err) {
                        reject(err);
                    };
                };
                reader.onerror = function(err) {
                    reject(err);
                };
            });
        }

        // Image Preview logic with client-side compression
        function previewFile(input, previewId, labelId) {
            const preview = document.getElementById(previewId);
            const label = document.getElementById(labelId);

            if (input.files && input.files[0]) {
                const file = input.files[0];

                if (file.type.startsWith('image/')) {
                    compressionInProgress++;
                    updateSubmitButtonState();
                    label.innerText = 'Memproses...';

                    compressAndResizeImage(file, 1024, 1024, 0.7)
                        .then(compressedFile => {
                            // Update input.files with compressed file
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(compressedFile);
                            input.files = dataTransfer.files;

                            const compressedSizeKB = (compressedFile.size / 1024).toFixed(1);
                            label.innerText = compressedFile.name.substring(0, 10) + '... (' + compressedSizeKB +
                                ' KB)';

                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                            }
                            reader.readAsDataURL(compressedFile);
                        })
                        .catch(err => {
                            console.error('Image compression error:', err);
                            label.innerText = file.name.substring(0, 15) + (file.name.length > 15 ? '...' : '');
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                            }
                            reader.readAsDataURL(file);
                        })
                        .finally(() => {
                            compressionInProgress--;
                            updateSubmitButtonState();
                        });
                } else {
                    label.innerText = file.name.substring(0, 15) + (file.name.length > 15 ? '...' : '');
                    preview.src = '';
                    preview.style.display = 'none';
                }
            } else {
                preview.src = '';
                preview.style.display = 'none';
                label.innerText = 'Pilih Foto';
            }
        }
    </script>
@endpush
