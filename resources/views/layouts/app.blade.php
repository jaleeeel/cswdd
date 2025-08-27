<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Admin System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div id="app">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Admin System') }}
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            @if(auth()->user()->isSuperAdmin())
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('super-admin.dashboard') }}">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('super-admin.users') }}">Users</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('super-admin.departments') }}">Departments</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('super-admin.reports') }}">Reports</a>
                                </li>
                            @elseif(auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.staff') }}">Staff</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.programs') }}">Programs</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.services') }}">Services</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.reports') }}">Reports</a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('staff.dashboard') }}">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('staff.clients') }}">Clients</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('staff.registrations') }}">Registrations</a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    {{ Auth::user()->name }}
                                    <span class="badge bg-light text-dark ms-1">{{ Auth::user()->role->name }}</span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="dropdown-item-text">
                                        <small class="text-muted">
                                            @if(Auth::user()->department)
                                                {{ Auth::user()->department->name }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-4">
            @if(session('success'))
                <div class="container">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @yield('scripts')
</body>
</html>
