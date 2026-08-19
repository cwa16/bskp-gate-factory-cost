<nav class="navbar navbar-light bg-white border-bottom px-3">
    <span class="navbar-brand mb-0 h6">
        @yield('page-title', 'Dashboard')
    </span>

    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                data-bs-toggle="dropdown">
            {{ auth()->user()->name ?? 'User' }}
        </button>

        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="#">Profile</a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="#">
                    @csrf
                    <button class="dropdown-item text-danger">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
