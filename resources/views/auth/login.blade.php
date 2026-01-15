<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - SISTEM POLIKLINIK</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
    --blue-main:#316BA1;
    --blue-soft:#3f7fbf;
    --blue-light:#eaf3fb;
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

    /* GRADIENT BLUE SOFT */
    background:
        radial-gradient(circle at top left, #5fa3e6 0%, transparent 45%),
        radial-gradient(circle at bottom right, #7fc8ff 0%, transparent 50%),
        linear-gradient(135deg, #316BA1, #3f7fbf);
}


/* BACKGROUND WAVES */
body::before{
    content:"";
    position:absolute;
    bottom:-120px;
    left:0;
    width:100%;
    height:300px;
    background:var(--blue-light);
    border-top-left-radius:100% 120px;
    border-top-right-radius:100% 120px;
    z-index:-1;
}

/* LOGIN BOX */
.login-container{
    background:white;
    width:420px;
    border-radius:18px;
    box-shadow:0 20px 50px rgba(0,0,0,.25);
    overflow:hidden;
}

/* HEADER */
.login-header{
    background:linear-gradient(135deg,var(--blue-main),var(--blue-soft));
    color:white;
    padding:35px 30px;
    text-align:center;
}

.login-header h2{
    font-size:26px;
    font-weight:700;
    letter-spacing:.6px;
}

.login-header p{
    margin-top:8px;
    opacity:.9;
}

/* BODY */
.login-body{
    padding:35px 30px;
}

.form-group{
    margin-bottom:20px;
}

label{
    font-size:13px;
    font-weight:600;
    color:var(--blue-main);
    display:block;
    margin-bottom:6px;
}

input{
    width:100%;
    padding:13px 14px;
    border-radius:10px;
    border:2px solid #dde7f3;
    font-size:14px;
    transition:.2s;
}

input:focus{
    outline:none;
    border-color:var(--blue-main);
    box-shadow:0 0 0 3px rgba(49,107,161,.15);
}

/* BUTTON */
.login-btn{
    width:100%;
    padding:14px;
    background:var(--blue-main);
    border:none;
    border-radius:12px;
    color:white;
    font-weight:700;
    cursor:pointer;
    margin-top:10px;
    transition:.25s;
    box-shadow:0 8px 20px rgba(49,107,161,.4);
}

.login-btn:hover{
    background:var(--blue-soft);
    transform:translateY(-2px);
    box-shadow:0 14px 30px rgba(49,107,161,.5);
}

/* ERROR */
.error-message{
    background:#ffeaea;
    color:#c0392b;
    padding:12px;
    border-radius:10px;
    margin-bottom:16px;
    font-size:13px;
}

/* FOOTER */
.back-link{
    text-align:center;
    margin-top:20px;
}

.back-link a{
    text-decoration:none;
    color:var(--blue-main);
    font-weight:600;
}

.back-link a:hover{
    text-decoration:underline;
}
</style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2>SISTEM POLIKLINIK</h2>
        <p>Silakan login untuk melanjutkan</p>
    </div>

    <div class="login-body">

        @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
        @endif

        @if(session('success'))
        <div class="error-message" style="background:#e8f5e9;color:#2e7d32;">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button class="login-btn">Masuk</button>
        </form>

        <div class="back-link">
            <a href="{{ route('home') }}">← Kembali ke Beranda</a>
        </div>
    </div>
</div>

</body>
</html>
