<span class="navbar-brand">
    <img class="brand-icon" src="/images/darul-arqam-logo.png" alt="Darul Arqam">
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
