@extends('layouts.app')

@section('title', 'Pelacakan Kunjungan Sales (GPS Map)')

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #map {
            height: 520px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .checkin-row {
            cursor: pointer;
            transition: all 0.2s;
        }

        .checkin-row:hover {
            background-color: rgba(108, 99, 255, 0.08) !important;
        }

        /* Leaflet Dark Theme Styling */
        .leaflet-container {
            background-color: #0F1117 !important;
        }

        .leaflet-bar a {
            background-color: #1A1D27 !important;
            color: #F1F5F9 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        .leaflet-bar a:hover {
            background-color: #1F2333 !important;
            color: #fff !important;
        }

        .leaflet-popup-content-wrapper {
            background-color: #1A1D27 !important;
            color: #F1F5F9 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px !important;
        }

        .leaflet-popup-tip {
            background-color: #1A1D27 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid p-0">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h5 class="fw-bold text-white mb-1">
                                <i class="fa-solid fa-map-location-dot text-danger me-2"></i>Pelacakan GPS Kunjungan Sales
                            </h5>
                            <p class="text-secondary small mb-0">Visualisasi rute dan lokasi kunjungan sales berdasarkan
                                check-in di lapangan.</p>
                        </div>
                        <form action="{{ route('sales-tracking.index') }}" method="GET"
                            class="d-flex align-items-center gap-2">
                            <label class="text-secondary small fw-semibold text-nowrap mb-0">Filter Tanggal:</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm w-auto"
                                value="{{ $tanggal }}" onchange="this.form.submit()">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Map Container -->
            <div class="col-lg-8">
                <div class="card shadow border-0 rounded-4 p-3 h-100" style="background-color: #1A1D27;">
                    <div id="map"></div>
                </div>
            </div>

            <!-- Checkin List Table -->
            <div class="col-lg-4">
                <div class="card shadow border-0 rounded-4 h-100" style="background-color: #1A1D27; min-height: 550px;">
                    <div class="card-header bg-transparent border-white-10 py-3">
                        <h6 class="fw-bold text-white mb-0">Daftar Kunjungan Hari Ini</h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 480px; overflow-y: auto;">
                        @if ($checkins->isEmpty())
                            <div class="p-5 text-center text-secondary small">
                                <i class="fa-solid fa-location-crosshairs d-block fs-3 mb-2 opacity-50"></i>
                                Tidak ada data kunjungan / GPS check-in untuk tanggal terpilih.
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($checkins as $c)
                                    @php
                                        // Calculate duration
                                        $duration = '-';
                                        if ($c->checkin && $c->checkout) {
                                            $diff = strtotime($c->checkout) - strtotime($c->checkin);
                                            $minutes = round($diff / 60);
                                            $duration = $minutes . ' menit';
                                        } elseif ($c->checkin && !$c->checkout) {
                                            $duration = 'Sedang Berlangsung';
                                        }
                                    @endphp
                                    <div class="list-group-item bg-transparent border-white-10 py-3 px-4 checkin-row"
                                        data-lat="{{ $c->latitude }}" data-lng="{{ $c->longitude }}"
                                        data-sales="{{ $c->sales->name ?? 'Sales' }}"
                                        data-toko="{{ $c->pelanggan->nama_pelanggan ?? 'Toko' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="fw-bold text-white small">{{ $c->sales->name ?? 'Sales' }}</span>
                                            <span
                                                class="badge bg-{{ $c->checkout ? 'success' : 'warning' }}-subtle text-{{ $c->checkout ? 'success' : 'warning' }} fs-9">
                                                {{ $c->checkout ? 'Selesai' : 'Aktif' }}
                                            </span>
                                        </div>
                                        <p class="text-secondary mb-1" style="font-size: 0.78rem;">
                                            Toko: <strong
                                                class="text-light-indigo">{{ $c->pelanggan->nama_pelanggan ?? 'Toko' }}</strong>
                                        </p>
                                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                            <span><i
                                                    class="fa-regular fa-clock me-1"></i>{{ date('H:i', strtotime($c->checkin)) }}
                                                {{ $c->checkout ? '- ' . date('H:i', strtotime($c->checkout)) : '' }}</span>
                                            <span><i class="fa-solid fa-hourglass-half me-1"></i>{{ $duration }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        $(document).ready(function() {
            // Default center of map (Surabaya/Indonesia or center of marker group)
            let defaultLat = -7.2575;
            let defaultLng = 112.7521;
            let markers = [];

            // Initialize Map
            const map = L.map('map').setView([defaultLat, defaultLng], 12);

            // Add Leaflet Tile Layer (Dark Theme Map Style)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            // Populate Markers
            const checkinData = @json($checkins);
            const markerGroup = new L.featureGroup();

            checkinData.forEach(function(c, index) {
                if (c.latitude && c.longitude) {
                    const lat = parseFloat(c.latitude);
                    const lng = parseFloat(c.longitude);

                    // Marker styling depending on active/done status
                    const color = c.checkout ? '#10b981' : '#f59e0b';
                    const markerHtml = `
                        <div style="background-color: ${color}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>
                    `;

                    const customIcon = L.divIcon({
                        html: markerHtml,
                        className: 'custom-div-icon',
                        iconSize: [14, 14],
                        iconAnchor: [7, 7]
                    });

                    // Prepare Popup HTML content
                    let checkinTime = new Date(c.checkin).toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    let checkoutTime = c.checkout ? new Date(c.checkout).toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : 'Masih Berkunjung';

                    let popupHtml = `
                        <div style="font-family: sans-serif; font-size: 11px; min-width: 160px; padding: 2px;">
                            <strong style="color: #6C63FF; font-size: 12px; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px; margin-bottom: 6px;">${c.sales ? c.sales.name : 'Sales'}</strong>
                            <div style="margin-bottom: 4px;"><strong>Toko:</strong> ${c.pelanggan ? c.pelanggan.nama_pelanggan : 'Toko'}</div>
                            <div style="margin-bottom: 4px;"><strong>Jam Check-in:</strong> ${checkinTime}</div>
                            <div style="margin-bottom: 4px;"><strong>Jam Check-out:</strong> ${checkoutTime}</div>
                            ${c.catatan ? `<div style="margin-top: 6px; padding-top: 4px; border-top: 1px dashed rgba(255,255,255,0.1); color: #cbd5e1; font-style: italic;">"${c.catatan}"</div>` : ''}
                        </div>
                    `;

                    const marker = L.marker([lat, lng], {
                            icon: customIcon
                        })
                        .bindPopup(popupHtml)
                        .addTo(map);

                    markers.push({
                        lat: lat,
                        lng: lng,
                        marker: marker
                    });

                    markerGroup.addLayer(marker);
                }
            });

            // If there are check-ins, fit bounds of map to show all pins nicely
            if (checkinData.length > 0) {
                map.addLayer(markerGroup);
                map.fitBounds(markerGroup.getBounds().pad(0.1));
            }

            // Click Row to fly to that marker on the map and open its popup
            $('.checkin-row').on('click', function() {
                const lat = parseFloat($(this).data('lat'));
                const lng = parseFloat($(this).data('lng'));

                if (lat && lng) {
                    // Find the matched marker
                    const match = markers.find(m => m.lat === lat && m.lng === lng);
                    if (match) {
                        map.flyTo([lat, lng], 16, {
                            animate: true,
                            duration: 1.2
                        });
                        setTimeout(() => {
                            match.marker.openPopup();
                        }, 1300);
                    }
                }
            });
        });
    </script>
@endpush
