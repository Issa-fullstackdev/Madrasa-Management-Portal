<span class="navbar-brand">
    <svg class="brand-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path fill="#fff" d="M12 2a7 7 0 1 0 5.6 11.2A8 8 0 1 1 12 2z"/>
        <path fill="#fff" d="M18.5 5.5l.7 1.6 1.6.7-1.6.7-.7 1.6-.7-1.6-1.6-.7 1.6-.7z"/>
    </svg>
    Madrasa Portal
</span>

<div class="app-sidebar-nav">
    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Dashboard</a>
    <a class="nav-link {{ request()->is('subjects*') ? 'active' : '' }}" href="/subjects">Subjects</a>
    <a class="nav-link {{ request()->is('attendance*') ? 'active' : '' }}" href="/attendance">Attendance</a>
    @if (auth()->check() && auth()->user()->role === 'principal')
        <a class="nav-link {{ request()->is('students*') ? 'active' : '' }}" href="/students">Students</a>
        <a class="nav-link {{ request()->is('payments*') ? 'active' : '' }}" href="/payments">Payments</a>
    @endif
</div>

@auth
    <div class="app-sidebar-footer">
        <div class="mb-2">
            {{ auth()->user()->name }}
            <span class="badge">{{ ucfirst(auth()->user()->role) }}</span>
        </div>
        <form method="POST" action="/logout">
            @csrf
            <button class="btn btn-outline-light btn-sm w-100">Log out</button>
        </form>
    </div>
@endauth
