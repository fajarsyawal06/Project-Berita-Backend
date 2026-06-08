<!DOCTYPE html>
<html>
<head>
    <title>Informasi Kredensial Akun Baru</title>
</head>
<body>
    <h2>Halo, {{ $user->nama_lengkap }}</h2>
    <p>Akun Anda telah berhasil dibuat di sistem kami.</p>
    <p>Berikut adalah informasi login Anda:</p>
    <ul>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
    </ul>
    <p>Silakan login dan segera ganti password Anda demi keamanan.</p>
    <p>Terima kasih.</p>
</body>
</html>
