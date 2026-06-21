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
        /* Custom Premium Center-Card Login Style */
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
            display: flex;
            align-items: center;
            justify-content: center;
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

        .auth-card-container {
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .auth-card-wrapper {
            background: rgba(17, 22, 37, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 24px;
            padding: 2.75rem 2.25rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(30px);
            animation: slideUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .auth-card-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .auth-brand-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .auth-brand-icon {
            width: 64px;
            height: 64px;
            background: var(--primary-gradient);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: white;
            margin-bottom: 0.75rem;
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.35);
        }

        .auth-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
        }

        .auth-subtitle {
            font-size: 0.85rem;
            color: #94A3B8;
            margin: 0.25rem 0 0 0;
        }

        .welcome-back {
            font-size: 1.4rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin: 0 0 0.5rem 0;
        }

        .welcome-sub {
            font-size: 0.88rem;
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
            text-align: left;
        }

        .form-label-custom {
            font-size: 0.8rem;
            font-weight: 600;
            color: #94A3B8;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
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

    <div class="auth-card-container">
        <div class="auth-card-wrapper">
            <div class="auth-card-header">
                <div class="auth-brand-header">
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
