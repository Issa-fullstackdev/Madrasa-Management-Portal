<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login — Madrasa Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link href="/css/theme.css" rel="stylesheet">
</head>
<body class="mosque-auth-bg">
    <div class="auth-intro">
        <img src="/images/darul-arqam-logo.jpg" alt="Darul Arqam Islamic Centre">
        <h1>Darul Arqam Madrasa</h1>
        <div class="auth-intro-sub">Management Portal</div>
    </div>

    <div class="card auth-card text-center">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger text-start">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/login" class="text-start">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button class="btn btn-primary w-100">Log in</button>
            </form>
        </div>
    </div>
</body>
</html>
