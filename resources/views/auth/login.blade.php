<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - SISTEM POLIKLINIK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body{
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(135deg,#4a6fa5,#7699c9);
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .login-container{
            background:white;
            border-radius:20px;
            width:100%;
            max-width:420px;
            box-shadow:0 15px 40px rgba(0,0,0,.2);
            overflow:hidden;
        }

        .login-header{
            background:linear-gradient(135deg,#4a6fa5,#5b7db1);
            padding:30px;
            color:white;
            text-align:center;
        }

        .login-body{ padding:30px; }

        .form-group{ margin-bottom:18px; }

        label{
            display:block;
            font-weight:600;
            color:#333;
            margin-bottom:6px;
        }

        input{
            width:100%;
            padding:12px 14px;
            border:2px solid #ddd;
            border-radius:8px;
            font-size:15px;
        }

        input:focus{
            outline:none;
            border-color:#4a6fa5;
        }

        .login-btn{
            width:100%;
            padding:14px;
            background:#4a6fa5;
            border:none;
            color:white;
            font-weight:700;
            border-radius:10px;
            cursor:pointer;
            margin-top:10px;
        }

        .login-btn:hover{ background:#3d5f8f; }

        .error-message{
            background:#ffeaea;
            color:#c0392b;
            padding:10px;
            border-radius:8px;
            margin-bottom:15px;
        }

        .back-link{
            text-align:center;
            margin-top:18px;
        }

        .back-link a{
            color:#4a6fa5;
            text-decoration:none;
            font-weight:600;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2>🏥 SISTEM POLIKLINIK</h2>
        <p>Silakan login</p>
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
