<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Madrasa Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link href="/css/theme.css" rel="stylesheet">
</head>
<body>
    <div class="app-shell d-flex">
        <aside class="app-sidebar d-none d-lg-flex">
            <div class="app-sidebar-theme">
                @include('partials.sidebar-nav')
            </div>
        </aside>

        <div class="offcanvas offcanvas-start app-sidebar-offcanvas app-sidebar-theme" tabindex="-1" id="sidebarOffcanvas">
            @include('partials.sidebar-nav')
        </div>

        <div class="app-main">
            <div class="app-topbar d-flex d-lg-none align-items-center justify-content-between px-3 py-2 border-bottom bg-white">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                    &#9776; Menu
                </button>
                <span class="navbar-brand mb-0">Madrasa Portal</span>
            </div>

            <div class="container-fluid mt-4 mb-5 px-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
