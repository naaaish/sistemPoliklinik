<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - HETORICA</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
    --blue-main:#316BA1;
    --blue-soft:#3f7fbf;
    --blue-light:#eaf3fb;
    --blue-dark:#1a3a52;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins','Segoe UI',sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    position: relative;
    overflow: hidden;
}

/* ========== BACKGROUND IMAGE ========== */

.bg-image{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    z-index:1;
    
    /* PAKAI GAMBAR - upload ke public/images/login-bg.jpg */
    background-image: url('/assets/home/login.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    
    /* Atau pakai gradient kalau tidak ada gambar */
    /* background: linear-gradient(135deg, #1a3a52 0%, #2c5f7f 50%, #1a3a52 100%); */
}

/* OVERLAY BIRU di atas gambar - ini yang bikin efek kebiruan */
.bg-overlay-blue{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(26, 58, 82, 0.45); /* Blue overlay - bisa diatur transparansinya */
    z-index:2;
}

/* Dark Overlay tambahan */
.bg-overlay{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.25);
    z-index:3;
}

/* Radial Overlay untuk depth */
.radial-overlay{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: radial-gradient(circle at 50% 50%, transparent 0%, rgba(0,0,0,0.2) 100%);
    z-index:4;
}

/* Floating Circles - buat dekorasi */
.bg-circle{
    position:absolute;
    border-radius:50%;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
    z-index:5;
}

.bg-circle.circle-1{
    width:500px;
    height:500px;
    top:-150px;
    right:-150px;
    animation: float 8s ease-in-out infinite;
}

.bg-circle.circle-2{
    width:400px;
    height:400px;
    bottom:-100px;
    left:-100px;
    animation: float 10s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-30px) scale(1.05);
    }
}

/* ========== LOGIN CONTAINER ========== */

.login-container{
    background:rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    width:450px;
    max-width:92%;
    border-radius:24px;
    
    /* SHADOW - bukan outline/border */
    box-shadow:
        0 30px 80px rgba(0,0,0,.4),
        0 15px 40px rgba(0,0,0,.3),
        0 5px 15px rgba(0,0,0,.2);
    
    overflow:hidden;
    position:relative;
    z-index:10;
    
    /* Border dihapus - diganti shadow aja */
    /* border: none; */
    
    animation: slideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ========== HEADER - LEBIH PUDAR ========== */

.login-header{
    /* Background lebih soft/pudar dengan opacity */
    background: linear-gradient(135deg, 
        rgba(26, 58, 82, 0.85), 
        rgba(44, 95, 127, 0.85)
    );
    
    /* Atau pakai warna solid yang lebih soft */
    /* background: linear-gradient(135deg, #405a6f, #5a7f97); */
    
    color:white;
    padding:40px 35px;
    text-align:center;
    position:relative;
    overflow:hidden;
}

/* Dekorasi di header - lebih subtle */
.login-header::before{
    content:"";
    position:absolute;
    top:-50%;
    right:-20%;
    width:300px;
    height:300px;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    border-radius:50%;
}

.login-header::after{
    content:"";
    position:absolute;
    bottom:-30%;
    left:-10%;
    width:200px;
    height:200px;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
    border-radius:50%;
}

.login-header h2{
    font-size:30px;
    font-weight:700;
    letter-spacing:.8px;
    position:relative;
    z-index:2;
    text-shadow: 0 2px 8px rgba(0,0,0,0.15);
    /* Opacity sedikit dikurangi supaya lebih soft */
    opacity: 0.98;
}

.login-header p{
    margin-top:10px;
    opacity:.88; /* Dikurangi dari .92 supaya lebih soft */
    font-size:15px;
    position:relative;
    z-index:2;
}

/* ========== BODY ========== */

.login-body{
    padding:40px 35px;
}

.form-group{
    margin-bottom:24px;
}

label{
    font-size:14px;
    font-weight:600;
    color:var(--blue-dark);
    display:block;
    margin-bottom:10px;
}

.input-wrapper{
    position:relative;
}

.input-icon{
    position:absolute;
    left:16px;
    top:50%;
    transform:translateY(-50%);
    color:#94a3b8;
    width:20px;
    height:20px;
}

input{
    width:100%;
    padding:16px 18px 16px 50px;
    border-radius:12px;
    border:2px solid #e2e8f0;
    font-size:15px;
    background:#f8fafc;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color:#1e293b;
}

input:focus{
    outline:none;
    border-color:var(--blue-main);
    background:white;
    box-shadow:
        0 0 0 4px rgba(49,107,161,.12),
        0 4px 12px rgba(0,0,0,.08);
    transform:translateY(-1px);
}

input::placeholder{
    color:#cbd5e1;
}

/* ========== REMEMBER ME & FORGOT PASSWORD ========== */

.form-options{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:24px;
    margin-top:8px;
}

.remember-me{
    display:flex;
    align-items:center;
    gap:8px;
    cursor:pointer;
    user-select:none;
}

.remember-me input[type="checkbox"]{
    width:18px;
    height:18px;
    cursor:pointer;
    accent-color:var(--blue-main);
    margin:0;
    padding:0;
}

.remember-me span{
    font-size:13px;
    font-weight:400;
    color:#475569;
    margin:0;
    cursor:pointer;
}

.forgot-password{
    font-size:13px;
    color:var(--blue-main);
    text-decoration:none;
    font-weight:500;
    transition: all 0.3s ease;
    position:relative;
}

.forgot-password::after{
    content:'';
    position:absolute;
    bottom:-2px;
    left:0;
    width:0;
    height:2px;
    background:var(--blue-main);
    transition: width 0.3s ease;
}

.forgot-password:hover::after{
    width:100%;
}

.forgot-password:hover{
    color:#2c4a5e;
}

/* ========== BUTTON ========== */

.login-btn{
    width:100%;
    padding:17px;
    background:linear-gradient(135deg, #405a6f 0%, #2c4a5e 100%);
    border:none;
    border-radius:12px;
    color:white;
    font-weight:600;
    font-size:16px;
    cursor:pointer;
    margin-top:0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow:
        0 8px 20px rgba(64, 90, 111,.35),
        0 2px 8px rgba(0,0,0,.15);
    position:relative;
    overflow:hidden;
}

.login-btn::before{
    content:'';
    position:absolute;
    top:0;
    left:-100%;
    width:100%;
    height:100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.login-btn:hover::before{
    left:100%;
}

.login-btn:hover{
    background:linear-gradient(135deg, #2c4a5e 0%, #1a3a52 100%);
    transform:translateY(-3px);
    box-shadow:
        0 12px 28px rgba(64, 90, 111,.45),
        0 4px 12px rgba(0,0,0,.2);
}

.login-btn:active{
    transform:translateY(-1px);
}

/* ========== ALERTS ========== */

.error-message{
    background:linear-gradient(135deg, #ffeaea 0%, #ffdddd 100%);
    color:#c0392b;
    padding:14px 16px;
    border-radius:12px;
    margin-bottom:20px;
    font-size:14px;
    font-weight:500;
    border-left:4px solid #c0392b;
    animation: fadeIn 0.4s ease;
}

.success-message{
    background:linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    color:#2e7d32;
    padding:14px 16px;
    border-radius:12px;
    margin-bottom:20px;
    font-size:14px;
    font-weight:500;
    border-left:4px solid #2e7d32;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ========== FOOTER ========== */

.back-link{
    text-align:center;
    margin-top:24px;
}

.back-link a{
    text-decoration:none;
    color:#64748b;
    font-weight:500;
    font-size:14px;
    transition: all 0.3s ease;
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.back-link a:hover{
    color:var(--blue-main);
    transform:translateX(-3px);
}

/* ========== RESPONSIVE ========== */

@media (max-width: 768px) {
    .login-container{
        width:95%;
        border-radius:20px;
    }

    .login-header{
        padding:35px 28px;
    }

    .login-header h2{
        font-size:26px;
    }

    .login-body{
        padding:35px 28px;
    }

    .bg-circle{
        display:none;
    }

    .form-options{
        flex-direction:column;
        align-items:flex-start;
        gap:12px;
    }
}

@media (max-width: 480px) {
    .login-header{
        padding:30px 24px;
    }

    .login-header h2{
        font-size:24px;
    }

    .login-body{
        padding:30px 24px;
    }

    input{
        padding:14px 16px 14px 46px;
        font-size:14px;
    }

    .login-btn{
        padding:15px;
        font-size:15px;
    }

    .form-options{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }
}
</style>
</head>
<body>

<!-- Background Image -->
<div class="bg-image"></div>

<!-- Blue Overlay - ini yang bikin efek kebiruan di gambar -->
<div class="bg-overlay-blue"></div>

<!-- Dark Overlay -->
<div class="bg-overlay"></div>

<!-- Radial Overlay -->
<div class="radial-overlay"></div>

<!-- Floating Circles -->
<div class="bg-circle circle-1"></div>
<div class="bg-circle circle-2"></div>

<div class="login-container">

    <div class="login-header">
        <h2>HETORICA</h2>
        <p>Silakan login untuk melanjutkan</p>
    </div>

    <div class="login-body">

        @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
        @endif

        @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                </div>
            </div>

            <button class="login-btn">Masuk</button>
        </form>

        <div class="back-link">
            <a href="{{ route('home') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

</body>
</html>