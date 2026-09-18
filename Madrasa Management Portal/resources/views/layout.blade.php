<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Madrasa Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand navbar-dark bg-dark px-3">
        <span class="navbar-brand">Madrasa Management Portal</span>
        <div class="navbar-nav">
            <a class="nav-link text-white" href="/subjects">Subjects</a>
            <a class="nav-link text-white" href="/students">Students</a>
            <a class="nav-link text-white" href="/attendance">Attendance</a>
        </div>
    </nav>
    <div class="container mt-4">
        @yield('content')
    </div>
</body>
</html>