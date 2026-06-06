<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'JB Football League') }} - @yield('title', 'Home')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --jb-green: #198754;
            --jb-green-dark: #146c43;
            --jb-dark: #212529;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        main {
            flex: 1;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 0.5px;
        }

        .navbar-brand i {
            color: #198754;
        }

        .nav-link {
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: #198754 !important;
        }

        .nav-link.active {
            color: #198754 !important;
            border-bottom: 2px solid #198754;
        }

        .btn-success {
            background-color: var(--jb-green);
            border-color: var(--jb-green);
        }

        .btn-success:hover {
            background-color: var(--jb-green-dark);
            border-color: var(--jb-green-dark);
        }

        .footer {
            background-color: var(--jb-dark);
            color: #adb5bd;
            padding: 1.5rem 0;
            margin-top: 2rem;
        }

        .footer a {
            color: #198754;
            text-decoration: none;
        }

        .footer a:hover {
            color: #20c997;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-futbol me-2"></i>JB Football League
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Left Nav -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                               href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-1"></i> {{ __('app.dashboard') }}
                            </a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('competitions.*') ? 'active' : '' }}"
                           href="{{ route('competitions.index') }}">
                            <i class="fas fa-trophy me-1"></i> {{ __('app.competitions') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('teams.*') ? 'active' : '' }}"
                           href="{{ route('teams.index') }}">
                            <i class="fas fa-shield-halved me-1"></i> {{ __('app.teams') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('players.*') ? 'active' : '' }}"
                           href="{{ route('players.index') }}">
                            <i class="fas fa-users me-1"></i> {{ __('app.players') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('matches.*') ? 'active' : '' }}"
                           href="{{ route('matches.index') }}">
                            <i class="fas fa-calendar-days me-1"></i> {{ __('app.matches') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('standings.*') ? 'active' : '' }}"
                           href="{{ route('standings.index') }}">
                            <i class="fas fa-ranking-star me-1"></i> {{ __('app.standings') }}
                        </a>
                    </li>
                </ul>

                <!-- Right Nav -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <!-- Language Switcher -->
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i>
                            {{ app()->getLocale() === 'ms' ? 'BM' : 'EN' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                                   href="{{ route('language.switch', 'en') }}">
                                    English (EN)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'ms' ? 'active' : '' }}"
                                   href="{{ route('language.switch', 'ms') }}">
                                    Bahasa Malaysia (BM)
                                </a>
                            </li>
                        </ul>
                    </li>

                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i> {{ __('app.login') }}
                            </a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">
                                    <i class="fas fa-user-plus me-1"></i> {{ __('app.register') }}
                                </a>
                            </li>
                        @endif
                    @else
                        @if(auth()->user()->role === 'team_manager' && auth()->user()->team_id)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('teams.show', auth()->user()->team_id) }}">
                                    <i class="fas fa-shield me-1"></i> {{ __('app.my_team') }}
                                </a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-1"></i> {{ __('app.logout') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="container py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">
                        <i class="fas fa-futbol me-1 text-success"></i>
                        &copy; {{ date('Y') }} JB Football League. {{ __('app.all_rights_reserved') }}.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>Johor Bahru Football League {{ __('app.management_system') }}</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
