<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login — Madrasa Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link href="/css/theme.css" rel="stylesheet">
</head>
<body class="mosque-auth-bg geo-pattern">
    <div class="container" style="max-width: 400px;">
        <div class="mosque-arch">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2a7 7 0 1 0 5.6 11.2A8 8 0 1 1 12 2z"/>
                <path d="M18.5 5.5l.7 1.6 1.6.7-1.6.7-.7 1.6-.7-1.6-1.6-.7 1.6-.7z"/>
            </svg>
        </div>

        <div class="card auth-card text-center">
            <div class="card-body">
                <div class="auth-subtitle mb-1">Darul Arqam Islamic Centre</div>
                <h3 class="mb-4">Madrasa Management Portal</h3>

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
    </div>
</body>
</html>
