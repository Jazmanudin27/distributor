@extends('layouts.app')

@section('title', 'Peta Pemetaan Pelanggan')

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #map {
            height: 580px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .customer-card-item {
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        }

        .customer-card-item:hover {
            background-color: rgba(99, 102, 241, 0.08) !important;
        }

        .customer-card-item.active {
            background-color: rgba(99, 102, 241, 0.15) !important;
            border-left: 3px solid #6366f1 !important;
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

        /* Marker Pulse Animation */
        @keyframes marker-pulse {
            0% {
                transform: scale(0.6);
                opacity: 1;
            }
            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        .marker-pulse-effect {
            position: absolute;
            top: -4px;
            left: -4px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            pointer-events: none;
            z-index: -1;
            animation: marker-pulse 1.8s infinite;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-3">
                        <div>
                            <h5 class="fw-bold text-white mb-1">
                                <i class="fa-solid fa-map-location-dot text-primary me-2"></i>Pemetaan Lokasi Pelanggan
                            </h5>
                            <p class="text-secondary small mb-0">Visualisasi persebaran dan data geolokasi pelanggan terdaftar.</p>
                        </div>
                        
                        <!-- Filter Form -->
                        <form action="{{ route('pelanggan.map') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                            <select name="kode_wilayah" class="form-select form-select-sm w-auto select2-init" onchange="this.form.submit()">
                                <option value="">-- Semua Wilayah --</option>
                                @foreach($wilayahs as $w)
                                    <option value="{{ $w->kode_wilayah }}" {{ request('kode_wilayah') == $w->kode_wilayah ? 'selected' : '' }}>
                                        {{ $w->nama_wilayah }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="sub_wilayah" class="form-select form-select-sm w-auto select2-init" onchange="this.form.submit()">
                                <option value="">-- Semua Sub Wilayah --</option>
                                @foreach($subWilayahs as $sw)
                                    <option value="{{ $sw->kode_wilayah }}" {{ request('sub_wilayah') == $sw->kode_wilayah ? 'selected' : '' }}>
                                        {{ $sw->nama_wilayah }}
                                    </option>
                                @endforeach
                            </select>

                            @if(request('kode_wilayah') || request('sub_wilayah') || request('search'))
                                <a href="{{ route('pelanggan.map') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-arrows-rotate me-1"></i>Reset
                                </a>
                            @endif
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

            <!-- List Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow border-0 rounded-4 d-flex flex-column h-100" style="background-color: #1A1D27; min-height: 610px;">
                    <div class="card-header bg-transparent border-white-10 py-3">
                        <h6 class="fw-bold text-white mb-2">Daftar Pelanggan Terpetakan ({{ $pelanggans->count() }})</h6>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary text-secondary">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" id="sidebar-search" class="form-control bg-dark border-secondary text-white" placeholder="Cari nama atau kode toko...">
                        </div>
                    </div>
                    
                    <div class="card-body p-0 flex-grow-1" style="max-height: 470px; overflow-y: auto;" id="customer-list-container">
                        @if($pelanggans->isEmpty())
                            <div class="p-5 text-center text-secondary small">
                                <i class="fa-solid fa-location-dot d-block fs-3 mb-2 opacity-50"></i>
                                Tidak ada data pelanggan dengan koordinat GPS untuk filter terpilih.
                            </div>
                        @else
                            <div class="list-group list-group-flush" id="customer-list-group">
                                @foreach($pelanggans as $p)
                                    <div class="list-group-item bg-transparent py-3 px-4 customer-card-item" 
                                         data-code="{{ $p->kode_pelanggan }}"
                                         data-name="{{ strtolower($p->nama_pelanggan) }}"
                                         data-lat="{{ $p->latitude }}" 
                                         data-lng="{{ $p->longitude }}">
                                        
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="fw-bold text-white small">{{ $p->nama_pelanggan }}</span>
                                            <span class="badge bg-{{ $p->status == 1 ? 'success' : 'secondary' }}-subtle text-{{ $p->status == 1 ? 'success' : 'secondary' }} fs-9">
                                                {{ $p->status == 1 ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                        <p class="text-secondary mb-1" style="font-size: 0.75rem;">
                                            Kode: <strong>{{ $p->kode_pelanggan }}</strong> | Wilayah: {{ $p->wilayah->nama_wilayah ?? '-' }}
                                        </p>
                                        <p class="text-muted mb-0 small text-truncate" style="max-width: 320px;">
                                            <i class="fa-solid fa-location-dot me-1"></i>{{ $p->alamat_pelanggan ?: '-' }}
                                        </p>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        $(document).ready(function() {
            // Default center of map (Surabaya/Indonesia or center of marker group)
            let defaultLat = -7.2575;
            let defaultLng = 112.7521;
            let markers = {};

            // Initialize Map
            const map = L.map('map').setView([defaultLat, defaultLng], 12);

            // Add Leaflet Tile Layer (Dark Theme Map Style)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            // Populate Markers
            const customersData = @json($pelanggans);
            const markerGroup = new L.featureGroup();

            customersData.forEach(function(p) {
                if (p.latitude && p.longitude) {
                    const lat = parseFloat(p.latitude);
                    const lng = parseFloat(p.longitude);

                    // Marker color coding: Inactive (Gray), Active (Indigo)
                    const color = p.status == 1 ? '#6366f1' : '#64748b';
                    const pulseBg = p.status == 1 ? 'rgba(99, 102, 241, 0.4)' : 'rgba(100, 116, 139, 0.4)';
                    
                    const markerHtml = `
                        <div style="background-color: ${color}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 8px rgba(0,0,0,0.5); position: relative;">
                            <div class="marker-pulse-effect" style="background-color: ${pulseBg}"></div>
                        </div>
                    `;

                    const customIcon = L.divIcon({
                        html: markerHtml,
                        className: 'custom-div-icon',
                        iconSize: [14, 14],
                        iconAnchor: [7, 7]
                    });

                    // Prepare Popup HTML content
                    const editUrl = `{{ url('/pelanggan') }}/${p.kode_pelanggan}/edit`;
                    const waLink = p.no_hp_pelanggan ? `https://wa.me/${p.no_hp_pelanggan.replace(/[^0-9]/g, '')}` : null;
                    const wilayahText = (p.wilayah ? p.wilayah.nama_wilayah : '-') + (p.sub_wilayah ? ` / ${p.sub_wilayah.nama_wilayah}` : '');

                    let popupHtml = `
                        <div style="font-family: sans-serif; font-size: 11px; min-width: 180px; padding: 2px;">
                            <strong style="color: #6366f1; font-size: 12px; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px; margin-bottom: 6px;">
                                ${p.nama_pelanggan}
                            </strong>
                            <div style="margin-bottom: 4px;"><strong>Kode:</strong> ${p.kode_pelanggan}</div>
                            <div style="margin-bottom: 4px;"><strong>Alamat:</strong> ${p.alamat_pelanggan || '-'}</div>
                            <div style="margin-bottom: 4px;"><strong>Wilayah:</strong> ${wilayahText}</div>
                            <div style="margin-bottom: 4px;"><strong>Metode Bayar:</strong> ${p.metode_bayar || '-'}</div>
                            <div style="margin-bottom: 6px;"><strong>HP/WA:</strong> ${p.no_hp_pelanggan || '-'}</div>
                            <div style="display: flex; gap: 5px; margin-top: 6px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 6px;">
                                <a href="${editUrl}" target="_blank" class="btn btn-xs btn-primary text-white" style="font-size: 9px; padding: 2px 6px; border-radius: 4px; text-decoration: none;">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                </a>
                                ${waLink ? `
                                <a href="${waLink}" target="_blank" class="btn btn-xs btn-success text-white" style="font-size: 9px; padding: 2px 6px; border-radius: 4px; text-decoration: none; background-color: #25d366; border-color: #25d366;">
                                    <i class="fa-brands fa-whatsapp me-1"></i>WhatsApp
                                </a>` : ''}
                            </div>
                        </div>
                    `;

                    const marker = L.marker([lat, lng], { icon: customIcon })
                        .bindPopup(popupHtml)
                        .addTo(map);

                    markers[p.kode_pelanggan] = {
                        lat: lat,
                        lng: lng,
                        marker: marker
                    };

                    markerGroup.addLayer(marker);
                }
            });

            // Fit bounds of map to show all pins nicely
            if (Object.keys(markers).length > 0) {
                map.addLayer(markerGroup);
                map.fitBounds(markerGroup.getBounds().pad(0.1));
            }

            // Click sidebar list item to focus map on that customer
            $('.customer-card-item').on('click', function() {
                const code = $(this).data('code');
                const lat = parseFloat($(this).data('lat'));
                const lng = parseFloat($(this).data('lng'));

                $('.customer-card-item').removeClass('active');
                $(this).addClass('active');

                if (lat && lng && markers[code]) {
                    map.flyTo([lat, lng], 16, {
                        animate: true,
                        duration: 1.2
                    });
                    setTimeout(() => {
                        markers[code].marker.openPopup();
                    }, 1300);
                }
            });

            // Instant sidebar search & marker filter
            $('#sidebar-search').on('input', function() {
                const query = $(this).val().toLowerCase().trim();
                
                $('.customer-card-item').each(function() {
                    const name = $(this).data('name');
                    const code = $(this).data('code').toString().toLowerCase();
                    
                    if (name.includes(query) || code.includes(query)) {
                        $(this).removeClass('d-none');
                        
                        // Show marker on map
                        if (markers[code]) {
                            map.addLayer(markers[code].marker);
                        }
                    } else {
                        $(this).addClass('d-none');
                        
                        // Hide marker on map
                        if (markers[code]) {
                            map.removeLayer(markers[code].marker);
                        }
                    }
                });
            });
        });
    </script>
@endpush
