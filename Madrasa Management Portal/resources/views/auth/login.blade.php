<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Madrasa Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link href="/css/theme.css" rel="stylesheet">
</head>
<body>
    <div class="auth-split d-flex">
        <div class="auth-form-panel">
            <div class="auth-brand-row">
                <img src="/images/darul-arqam-logo.jpg" alt="Darul Arqam Islamic Centre">
                <div>
                    <div class="auth-brand-eyebrow">Darul Arqam Islamic Centre</div>
                    <div class="auth-brand-name">Madrasa Management Portal</div>
                </div>
            </div>

            <h1>Log in to your account</h1>
            <div class="auth-subtitle">Welcome back! Please enter your details.</div>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/login">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button class="btn btn-primary w-100">Log in</button>
            </form>
        </div>

        <div class="auth-image-panel d-none d-lg-flex" style="background-image: url('/images/darul-arqam-building.webp');">
            <h2>Darul Arqam Islamic Centre</h2>
            <p>Managing admissions, attendance, and Quran &amp; Hifdh progress for our students, all in one place.</p>
            <div class="auth-stats">
                <div class="auth-stat">
                    <div class="auth-stat-value">{{ $totalStudents }}</div>
                    <div class="auth-stat-label">Students</div>
                </div>
                <div class="auth-stat">
                    <div class="auth-stat-value">{{ $totalTeachers }}</div>
                    <div class="auth-stat-label">Teachers</div>
                </div>
                <div class="auth-stat">
                    <div class="auth-stat-value">{{ $totalSubjects }}</div>
                    <div class="auth-stat-label">Classes</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
