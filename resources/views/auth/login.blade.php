<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login ke DIS - Sistem Manajemen Distributor Terpadu">
    <title>Login | DIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Custom Premium Split-Screen Login Style */
        :root {
            --primary-gradient: linear-gradient(135deg, #6C63FF, #8B5CF6);
            --glow-color: rgba(108, 99, 255, 0.15);
            --transition-smooth: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body.auth-body {
            background-color: #06080F;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .auth-bg {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
        }

        .auth-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.25;
            mix-blend-mode: screen;
        }

        .orb-1 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(108, 99, 255, 0.4) 0%, rgba(0, 0, 0, 0) 70%);
            top: -200px;
            left: -150px;
            animation: float-slow 25s ease-in-out infinite;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.3) 0%, rgba(0, 0, 0, 0) 70%);
            bottom: -150px;
            right: -100px;
            animation: float-slow 20s ease-in-out infinite alternate;
        }

        .orb-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.25) 0%, rgba(0, 0, 0, 0) 70%);
            top: 40%;
            left: 30%;
            animation: float-slow 30s ease-in-out infinite 2s;
        }

        .orb-4 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.2) 0%, rgba(0, 0, 0, 0) 70%);
            top: 10%;
            right: 25%;
            animation: float-slow 22s ease-in-out infinite alternate 1s;
        }

        @keyframes float-slow {
            0% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(40px, -60px) scale(1.1);
            }

            100% {
                transform: translate(0, 0) scale(1);
            }
        }

        /* Split layout container */
        .split-container {
            display: flex;
            min-height: 100vh;
            width: 100vw;
            position: relative;
            z-index: 2;
        }

        /* Left Side: Brand Panel */
        .brand-panel {
            display: none;
        }

        @media (min-width: 1024px) {
            .brand-panel {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                flex: 1.2;
                padding: 4.5rem;
                background: linear-gradient(135deg, rgba(10, 13, 23, 0.8) 0%, rgba(15, 17, 28, 0.6) 100%);
                border-right: 1px solid rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(20px);
                position: relative;
                overflow: hidden;
            }

            .brand-panel::before {
                content: '';
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at top left, rgba(108, 99, 255, 0.05), transparent 40%);
                pointer-events: none;
            }
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            background: var(--primary-gradient);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.3);
        }

        .brand-name-group {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .brand-tagline {
            font-size: 0.75rem;
            color: #94A3B8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .brand-content {
            max-width: 580px;
            margin: auto 0;
            padding: 3rem 0;
        }

        .brand-headline {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
            margin-bottom: 1.25rem;
            background: linear-gradient(to right, #ffffff, #CBD5E1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-description {
            font-size: 1rem;
            color: #94A3B8;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .feature-showcase {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .showcase-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: var(--transition-smooth);
        }

        .showcase-card:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(108, 99, 255, 0.25);
            transform: translateX(6px);
        }

        .showcase-icon {
            width: 44px;
            height: 44px;
            background: rgba(108, 99, 255, 0.1);
            border: 1px solid rgba(108, 99, 255, 0.2);
            color: #8B5CF6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .showcase-info h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #f1f5f9;
            margin: 0 0 0.25rem 0;
        }

        .showcase-info p {
            font-size: 0.8rem;
            color: #64748B;
            margin: 0;
        }

        .brand-footer {
            font-size: 0.8rem;
            color: #64748B;
        }

        /* Right Side: Form Panel */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            background-color: #06080F;
            position: relative;
        }

        .auth-card-wrapper {
            width: 100%;
            max-width: 400px;
            background: rgba(17, 22, 37, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 24px;
            padding: 2.75rem 2.25rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(30px);
            z-index: 10;
        }

        .auth-card-header {
            margin-bottom: 2rem;
            text-align: left;
        }

        /* Mobile Logo (only shown on smaller screens) */
        .mobile-logo-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 2rem;
        }

        .mobile-logo-header .auth-brand-icon {
            width: 64px;
            height: 64px;
            background: var(--primary-gradient);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: white;
            margin: 0 auto 0.75rem;
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.35);
        }

        .mobile-logo-header .auth-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
        }

        .mobile-logo-header .auth-subtitle {
            font-size: 0.85rem;
            color: #94A3B8;
            margin: 0.25rem 0 0 0;
        }

        @media (min-width: 1024px) {
            .mobile-logo-header {
                display: none;
                /* Hidden on desktop since branding panel is visible */
            }
        }

        .welcome-back {
            font-size: 1.6rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin: 0 0 0.5rem 0;
        }

        .welcome-sub {
            font-size: 0.9rem;
            color: #94A3B8;
            margin: 0;
            line-height: 1.5;
        }

        /* Form styling */
        .auth-form-custom {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group-custom {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .form-label-custom {
            font-size: 0.8rem;
            font-weight: 600;
            color: #94A3B8;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .input-wrapper-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-custom {
            position: absolute;
            left: 1.1rem;
            color: #64748B;
            font-size: 0.95rem;
            pointer-events: none;
            transition: var(--transition-smooth);
            z-index: 5;
        }

        .form-input-custom {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 0.85rem 1.1rem 0.85rem 2.8rem;
            color: #ffffff;
            font-size: 0.95rem;
            font-family: inherit;
            transition: var(--transition-smooth);
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-input-custom:focus {
            border-color: rgba(108, 99, 255, 0.6);
            background: rgba(108, 99, 255, 0.03);
            box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-input-custom:focus+.input-icon-custom {
            color: #6C63FF;
        }

        .toggle-password-custom {
            position: absolute;
            right: 1.1rem;
            background: none;
            border: none;
            color: #64748B;
            cursor: pointer;
            font-size: 0.95rem;
            transition: var(--transition-smooth);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            padding: 0.2rem;
        }

        .toggle-password-custom:hover {
            color: #ffffff;
        }

        /* Checkbox styling */
        .form-options-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-check-custom {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
        }

        .check-input-custom {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.03);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-smooth);
        }

        .check-input-custom:checked {
            border-color: #6C63FF;
            background: #6C63FF;
        }

        .check-input-custom:checked::after {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 0.65rem;
            color: white;
        }

        .check-label-custom {
            font-size: 0.85rem;
            color: #94A3B8;
            cursor: pointer;
            user-select: none;
        }

        /* Submit Button */
        .btn-auth-custom {
            margin-top: 0.5rem;
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 0.95rem;
            font-weight: 700;
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: var(--transition-smooth);
            box-shadow: 0 6px 20px rgba(108, 99, 255, 0.25);
            position: relative;
            overflow: hidden;
        }

        .btn-auth-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-auth-custom:hover::before {
            left: 100%;
        }

        .btn-auth-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(108, 99, 255, 0.35);
        }

        .btn-auth-custom:active {
            transform: translateY(0);
        }

        .icon-arrow {
            transition: var(--transition-smooth);
        }

        .btn-auth-custom:hover .icon-arrow {
            transform: translateX(4px);
        }

        /* Alert Styling */
        .auth-alert-custom {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 0.9rem 1.1rem;
            color: #FCA5A5;
            font-size: 0.85rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            line-height: 1.4;
        }

        .auth-alert-custom i {
            margin-top: 0.15rem;
            color: #EF4444;
        }

        /* Animation Classes */
        .animate-fade-in {
            animation: fadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-slide-up {
            animation: slideUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-slide-up-form {
            animation: slideUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="auth-body">

    <!-- Animated background orbs -->
    <div class="auth-bg">
        <div class="auth-orb orb-1"></div>
        <div class="auth-orb orb-2"></div>
        <div class="auth-orb orb-3"></div>
        <div class="auth-orb orb-4"></div>
    </div>

    <div class="split-container">
        <!-- Brand Section (Left Side, Desktop Only) -->
        <div class="brand-panel">
            <div class="brand-header animate-fade-in">
                <div class="brand-logo">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="brand-name-group">
                    <span class="brand-title">DIS</span>
                    <span class="brand-tagline">Integrated System</span>
                </div>
            </div>

            <div class="brand-content animate-slide-up">
                <h2 class="brand-headline">Kelola Distribusi dengan Presisi Digital</h2>
                <p class="brand-description">Platform ERP terpadu untuk memantau rantai pasok, mengelola kas & bank,
                    melacak kunjungan sales, dan menganalisis performa bisnis secara realtime.</p>

                <!-- Micro-feature list / Glassmorphic widgets -->
                <div class="feature-showcase">
                    <div class="showcase-card">
                        <div class="showcase-icon"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="showcase-info">
                            <h4>Real-Time Analytics</h4>
                            <p>Pantau penjualan & laba rugi instan</p>
                        </div>
                    </div>
                    <div class="showcase-card">
                        <div class="showcase-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div class="showcase-info">
                            <h4>Inventory Control</h4>
                            <p>Manajemen stok opname & mutasi akurat</p>
                        </div>
                    </div>
                    <div class="showcase-card">
                        <div class="showcase-icon"><i class="fa-solid fa-wallet"></i></div>
                        <div class="showcase-info">
                            <h4>Keuangan Terpadu</h4>
                            <p>Arus kas, bank & mutasi rekening tercatat</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="brand-footer animate-fade-in">
                <p>© {{ date('Y') }} DIS. Hak Cipta Dilindungi Undang-Undang.</p>
            </div>
        </div>

        <!-- Form Section (Right Side) -->
        <div class="form-panel">
            <div class="auth-card-wrapper animate-slide-up-form">
                <div class="auth-card-header">
                    <!-- Brand info visible on mobile/tablets only -->
                    <div class="mobile-logo-header">
                        <div class="auth-brand-icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <h1 class="auth-title">DIS</h1>
                        <p class="auth-subtitle">Sistem Manajemen Distributor Terpadu</p>
                    </div>

                    <h2 class="welcome-back">Selamat Datang</h2>
                    <p class="welcome-sub">Masukkan kredensial Anda untuk mengakses sistem</p>
                </div>

                <form action="/login" method="POST" class="auth-form-custom">
                    @csrf

                    @if ($errors->any())
                        <div class="auth-alert-custom">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <div class="form-group-custom">
                        <label for="username" class="form-label-custom">Username</label>
                        <div class="input-wrapper-custom">
                            <i class="fas fa-user input-icon-custom"></i>
                            <input type="text" id="username" name="username" class="form-input-custom"
                                placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <div class="label-row">
                            <label for="password" class="form-label-custom">Password</label>
                        </div>
                        <div class="input-wrapper-custom">
                            <i class="fas fa-lock input-icon-custom"></i>
                            <input type="password" id="password" name="password" class="form-input-custom"
                                placeholder="••••••••" required>
                            <button type="button" class="toggle-password-custom" onclick="togglePassword()">
                                <i class="fas fa-eye" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options-custom">
                        <div class="form-check-custom">
                            <input type="checkbox" id="remember" name="remember" class="check-input-custom">
                            <label for="remember" class="check-label-custom">Ingat saya</label>
                        </div>
                    </div>

                    <button type="submit" id="btn-login" class="btn-auth-custom">
                        <span>Masuk ke Dashboard</span>
                        <i class="fas fa-arrow-right icon-arrow"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>

</html>
