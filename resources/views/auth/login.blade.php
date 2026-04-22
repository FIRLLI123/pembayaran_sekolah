<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Pembayaran Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:      #0d1117;
            --paper:    #f5f0e8;
            --gold:     #c9922a;
            --gold-lt:  #e8b84b;
            --red:      #c0392b;
            --muted:    #6b6560;
            --border:   #ddd5c3;
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--paper);
            overflow: hidden;
        }

        /* ── Animated Background ── */
        .bg-layer {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 90%, rgba(201,146,42,.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 90% 10%, rgba(13,17,23,.08) 0%, transparent 60%),
                var(--paper);
        }

        /* Grid lines */
        .bg-layer::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(13,17,23,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(13,17,23,.04) 1px, transparent 1px);
            background-size: 48px 48px;
            animation: gridShift 20s linear infinite;
        }

        @keyframes gridShift {
            from { background-position: 0 0; }
            to   { background-position: 48px 48px; }
        }

        /* Floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(70px);
            opacity: .35;
            animation: orbFloat linear infinite;
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 { width:420px; height:420px; background:var(--gold); top:-80px; left:-100px; animation-duration:18s; }
        .orb-2 { width:320px; height:320px; background:#c0392b; bottom:-60px; right:-80px; animation-duration:24s; animation-direction:reverse; }
        .orb-3 { width:220px; height:220px; background:var(--ink); top:50%; right:10%; animation-duration:14s; }

        @keyframes orbFloat {
            0%,100% { transform: translate(0,0) scale(1); }
            33%      { transform: translate(30px,-40px) scale(1.05); }
            66%      { transform: translate(-20px,30px) scale(.95); }
        }

        /* ── Layout ── */
        .stage {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 480px 1fr;
            grid-template-rows: 1fr;
            align-items: center;
        }

        /* Left decorative panel */
        .deco-panel {
            grid-column: 1;
            padding: 60px 40px 60px 80px;
            animation: fadeSlideLeft .9s cubic-bezier(.16,1,.3,1) both;
        }

        .deco-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--gold);
            border: 1px solid var(--gold);
            padding: 5px 14px;
            border-radius: 2px;
            margin-bottom: 28px;
        }

        .deco-headline {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.4rem, 4vw, 3.8rem);
            font-weight: 900;
            line-height: 1.1;
            color: var(--ink);
            margin-bottom: 20px;
        }

        .deco-headline em {
            font-style: italic;
            color: var(--gold);
        }

        .deco-desc {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 320px;
        }

        .deco-line {
            width: 60px; height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt));
            margin: 28px 0;
            border-radius: 2px;
        }

        .deco-stat {
            display: flex;
            gap: 36px;
            margin-top: 40px;
        }

        .stat-item {}
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--ink);
        }
        .stat-label {
            font-size: 12px;
            color: var(--muted);
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        /* ── Card ── */
        .card-wrap {
            grid-column: 2;
            animation: fadeSlideUp .8s cubic-bezier(.16,1,.3,1) .15s both;
        }

        .login-card {
            background: rgba(255,255,255,.72);
            backdrop-filter: blur(24px) saturate(1.4);
            -webkit-backdrop-filter: blur(24px) saturate(1.4);
            border: 1px solid rgba(255,255,255,.9);
            border-radius: 24px;
            padding: 52px 48px;
            box-shadow:
                0 2px 0 rgba(255,255,255,.8) inset,
                0 32px 80px rgba(13,17,23,.12),
                0 8px 24px rgba(13,17,23,.07);
            position: relative;
            overflow: hidden;
        }

        /* top gold stripe */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--ink), var(--gold), var(--gold-lt), var(--gold), var(--ink));
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }

        @keyframes shimmer {
            from { background-position: 200% 0; }
            to   { background-position: -200% 0; }
        }

        .card-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            margin-bottom: 28px;
            box-shadow: 0 8px 24px rgba(201,146,42,.35);
            animation: iconBounce .6s cubic-bezier(.34,1.56,.64,1) .5s both;
        }

        @keyframes iconBounce {
            from { transform: scale(0) rotate(-20deg); opacity: 0; }
            to   { transform: scale(1) rotate(0); opacity: 1; }
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .card-sub {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 36px;
        }

        /* Alerts */
        .alert-success-custom {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            margin-bottom: 24px;
            animation: fadeIn .4s ease;
        }

        .alert-danger-custom {
            background: #fff1f0;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            margin-bottom: 20px;
            animation: shake .4s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }

        /* Form fields */
        .field-group {
            margin-bottom: 20px;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            pointer-events: none;
            transition: color .2s;
        }

        .field-input {
            width: 100%;
            background: rgba(13,17,23,.04);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 14px 16px 14px 44px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            color: var(--ink);
            outline: none;
            transition: all .25s;
        }

        .field-input::placeholder { color: #b0a898; }

        .field-input:focus {
            border-color: var(--gold);
            background: rgba(201,146,42,.05);
            box-shadow: 0 0 0 4px rgba(201,146,42,.12);
        }

        .field-input:focus ~ .field-line {
            width: 100%;
        }

        /* Password toggle */
        .toggle-pw {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--muted);
            transition: color .2s;
            line-height: 1;
            padding: 4px;
        }
        .toggle-pw:hover { color: var(--ink); }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 15px;
            margin-top: 8px;
            background: linear-gradient(135deg, var(--ink) 0%, #1e2a3a 100%);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: .04em;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(13,17,23,.25);
        }

        .btn-login::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            opacity: 0;
            transition: opacity .3s;
        }

        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(13,17,23,.3); }
        .btn-login:hover::before { opacity: 1; }
        .btn-login:active { transform: translateY(0); }

        .btn-login span { position: relative; z-index: 1; }

        .btn-loader {
            display: none;
            position: absolute; inset: 0; z-index: 2;
            align-items: center; justify-content: center;
            background: inherit; border-radius: inherit;
        }

        .spinner {
            width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .btn-login.loading .btn-loader { display: flex; }
        .btn-login.loading > span { opacity: 0; }

        /* Footer */
        .card-footer-text {
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            margin-top: 28px;
        }

        /* Right deco */
        .deco-right {
            grid-column: 3;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 80px;
            animation: fadeSlideRight .9s cubic-bezier(.16,1,.3,1) .1s both;
        }

        .circle-deco {
            width: 280px; height: 280px;
            border-radius: 50%;
            border: 1.5px solid var(--border);
            position: relative;
            display: flex; align-items: center; justify-content: center;
            animation: rotateSlow 30s linear infinite;
        }

        .circle-deco::before, .circle-deco::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            border: 1.5px solid var(--border);
        }

        .circle-deco::before { inset: 30px; }
        .circle-deco::after  { inset: 60px; }

        .circle-dot {
            position: absolute;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 12px var(--gold);
        }

        .circle-dot:nth-child(1) { top: -5px; left: 50%; transform: translateX(-50%); }
        .circle-dot:nth-child(2) { bottom: -5px; left: 50%; transform: translateX(-50%); }
        .circle-dot:nth-child(3) { left: -5px; top: 50%; transform: translateY(-50%); }
        .circle-dot:nth-child(4) { right: -5px; top: 50%; transform: translateY(-50%); }

        .circle-inner {
            font-family: 'Playfair Display', serif;
            font-size: 13px;
            color: var(--muted);
            text-align: center;
            line-height: 1.6;
            animation: rotateSlow 30s linear infinite reverse;
        }

        @keyframes rotateSlow { to { transform: rotate(360deg); } }

        /* Animations */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeSlideLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeSlideRight {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; } to { opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 1100px) {
            .stage { grid-template-columns: 1fr 460px; }
            .deco-right { display: none; }
            .deco-panel { padding-left: 40px; }
        }

        @media (max-width: 768px) {
            .stage { grid-template-columns: 1fr; padding: 24px; }
            .deco-panel { display: none; }
            .card-wrap { grid-column: 1; }
            .login-card { padding: 36px 28px; }
            body { overflow: auto; }
        }
    </style>
</head>
<body>

<div class="bg-layer"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="stage">

    <!-- Left Deco -->
    <div class="deco-panel">
        <div class="deco-tag">Sistem Pembayaran Sekolah</div>
        <h1 class="deco-headline">
            Kelola <em>Tagihan</em><br>
            dengan Mudah &<br>
            Tepat Waktu.
        </h1>
        <div class="deco-line"></div>
        <p class="deco-desc">
            Platform terpadu untuk manajemen SPP, tagihan, dan laporan keuangan sekolah secara real-time.
        </p>
        <div class="deco-stat">
            <div class="stat-item">
                <div class="stat-num">500+</div>
                <div class="stat-label">Siswa Aktif</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">99%</div>
                <div class="stat-label">Akurasi Data</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">24/7</div>
                <div class="stat-label">Tersedia</div>
            </div>
        </div>
    </div>

    <!-- Login Card -->
    <div class="card-wrap">
        <div class="login-card">

            <div class="card-icon">⚡</div>
            <h2 class="card-title">Selamat Datang</h2>
            <p class="card-sub">Masuk ke akun Anda untuk melanjutkan</p>

            @if(session('success'))
                <div class="alert-success-custom">
                    ✓ &nbsp;{{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}" id="loginForm">
                @csrf

                <div class="field-group">
                    <label class="field-label" for="email">Email</label>
                    <div class="field-wrap">
                        <span class="field-icon">✉</span>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="field-input"
                            placeholder="nama@sekolah.ac.id"
                            required
                            autofocus
                            value="{{ old('email') }}"
                        >
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <span class="field-icon">🔒</span>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="field-input"
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" class="toggle-pw" onclick="togglePassword()" id="toggleBtn">
                            👁
                        </button>
                    </div>
                </div>

                @error('email')
                    <div class="alert-danger-custom">
                        ⚠ &nbsp;{{ $message }}
                    </div>
                @enderror

                <button type="submit" class="btn-login" id="loginBtn">
                    <span>Masuk ke Sistem &nbsp;→</span>
                    <div class="btn-loader"><div class="spinner"></div></div>
                </button>

            </form>

            <div class="card-footer-text">
                &copy; {{ date('Y') }} Sistem Pembayaran Sekolah - @Firlli &nbsp;·&nbsp; All rights reserved
            </div>

        </div>
    </div>

    <!-- Right Deco -->
    <div class="deco-right">
        <div class="circle-deco">
            <span class="circle-dot"></span>
            <span class="circle-dot"></span>
            <span class="circle-dot"></span>
            <span class="circle-dot"></span>
            <div class="circle-inner">
                Sistem<br>
                Terpercaya<br>
                ✦
            </div>
        </div>
    </div>

</div>

<script>
    function togglePassword() {
        const pw = document.getElementById('password');
        const btn = document.getElementById('toggleBtn');
        if (pw.type === 'password') {
            pw.type = 'text';
            btn.textContent = '🙈';
        } else {
            pw.type = 'password';
            btn.textContent = '👁';
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('loading');
    });

    // Staggered field animation on load
    document.querySelectorAll('.field-group').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(16px)';
        el.style.transition = 'opacity .5s ease, transform .5s ease';
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 400 + i * 120);
    });
</script>

</body>
</html>