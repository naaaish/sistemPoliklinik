<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login | Poliklinik IP</title>

<style>
* {
    box-sizing: border-box;
    font-family: "Segoe UI", sans-serif;
}

body {
    margin: 0;
    background: #dffafa;
}

.login-page {
    width: 100%;
    height: 100vh;
    position: relative;
    background: linear-gradient(to bottom, #eaffff 0%, #eaffff 60%, #b8f3f6 100%);
    overflow: hidden;
}

/* Wave Background */
.bg-wave {
    position: absolute;
    bottom: -120px;            
    left: 0;
    width: 100%;
    height: 520px;             
    background-image: url("{{ asset('assets/bg1.png') }}");
    background-repeat: no-repeat;
    background-position: bottom center;
    background-size: cover;   
    z-index: 1;
}


/* Card */
.login-card {
    width: 760px;
    height: 400px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -55%);
    display: flex;
    overflow: hidden;
    z-index: 5;
}

/* Left panel */
.left-panel {
    width: 45%;
    background: #e9ffff;
}

/* Right panel */
.right-panel {
    width: 55%;
    padding: 40px 50px;
}

.right-panel h2 {
    margin: 0;
    color: #1ba9b5;
    font-size: 28px;
}

.right-panel p {
    margin-top: 6px;
    margin-bottom: 30px;
    color: #666;
    font-size: 14px;
}

.right-panel label {
    font-size: 13px;
    color: #444;
}

.right-panel input {
    width: 100%;
    padding: 12px;
    margin-top: 6px;
    margin-bottom: 22px;
    border: 1px solid #cce7ea;
    border-radius: 6px;
    outline: none;
    font-size: 14px;
}

.right-panel input:focus {
    border-color: #1ba9b5;
}

/* Button */
.right-panel button {
    width: 100%;
    padding: 12px;
    background: #1ba9b5;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
}

.right-panel button:hover {
    background: #1597a3;
}

/* Footer */
.footer {
    position: absolute;
    bottom: 15px;
    width: 100%;
    text-align: center;
    font-size: 12px;
    color: #777;
    z-index: 5;
}
</style>

</head>
<body>

<div class="login-page">

    <div class="bg-wave"></div>

    <div class="login-card">
        <div class="left-panel"></div>

        <div class="right-panel">
            <h2>Selamat Datang</h2>
            <p>Silahkan masukkan detail akun Anda</p>

            <form>
                <label>Username</label>
                <input type="text" placeholder="Masukkan username">

                <label>Password</label>
                <input type="password" placeholder="Masukkan password">

                <button type="submit">Masuk</button>
            </form>
        </div>
    </div>

    <div class="footer">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>

</div>

</body>
</html>
