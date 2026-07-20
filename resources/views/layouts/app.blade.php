<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="og:title" content="@yield('og_title', 'Liga JBFA 2026 | Johor Bahru Football Association')" />
    <meta property="og:description" content="@yield('og_description', 'Portal rasmi Liga JBFA 2026. Jadual perlawanan, keputusan, kedudukan & pendaftaran pasukan. 30 pasukan, 4 pertandingan.')" />
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.png'))" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="@yield('og_url', url('/'))" />
    <meta property="og:type" content="@yield('og_type', 'website')" />
    <meta property="og:site_name" content="Liga JBFA 2026" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('og_title', 'Liga JBFA 2026 | Johor Bahru Football Association')" />
    <meta name="twitter:description" content="@yield('og_description', 'Portal rasmi Liga JBFA 2026. Jadual perlawanan, keputusan, kedudukan & pendaftaran pasukan.')" />
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-image.png'))" />
    <meta name="description" content="@yield('og_description', 'Portal rasmi Liga JBFA 2026. Jadual perlawanan, keputusan, kedudukan & pendaftaran pasukan. 30 pasukan, 4 pertandingan.')" />
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    <title>{{ config('app.name', 'JBFA Football League') }} - @yield('title', 'Home')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="{{ asset('css/fontawesome.min.css') }}" rel="stylesheet">

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
            white-space: nowrap;
            font-size: 0.85rem;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .nav-link:hover {
            color: #198754 !important;
        }

        .nav-link.active {
            color: #198754 !important;
            border-bottom: 2px solid #198754;
        }

        .dropdown-menu-dark .dropdown-item.active,
        .dropdown-menu-dark .dropdown-item:active {
            background-color: #198754;
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
            background-color: #1a1e24;
            color: #adb5bd;
            padding: 0;
            margin-top: 2rem;
        }

        .footer-main {
            padding: 2.5rem 0 1.5rem;
        }

        .footer a {
            color: #adb5bd;
            text-decoration: none;
        }

        .footer a:hover {
            color: #ffc107;
        }

        .footer h6 {
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .footer-links li {
            margin-bottom: 0.4rem;
        }

        .footer-links a {
            font-size: 0.85rem;
        }

        .footer-contact p {
            font-size: 0.82rem;
            margin-bottom: 0.3rem;
        }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: #adb5bd;
            margin-right: 6px;
            transition: all 0.2s;
        }

        .footer-social a:hover {
            background: #198754;
            color: #fff;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 1rem 0;
            font-size: 0.78rem;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>

    @stack('styles')
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-HMZYK68Y8R"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag("js", new Date());
  gtag("config", "G-HMZYK68Y8R");
</script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/jbfa_logo.png') }}" alt="JBFA" style="height:30px;" class="me-2">JBFA
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                @auth
                    @if(auth()->user()->isSuper() || auth()->user()->isLeagueAdmin())
                        {{-- ============================================= --}}
                        {{-- ADMIN / LEAGUE ADMIN NAVIGATION (unchanged)   --}}
                        {{-- ============================================= --}}
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                   href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i> {{ __('app.dashboard') }}
                                </a>
                            </li>
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
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('top-scorers') ? 'active' : '' }}"
                                   href="{{ route('top-scorers') }}">
                                    <i class="fas fa-futbol me-1"></i> Top Scorers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('liga-info.*') ? 'active' : '' }}"
                                   href="{{ route('liga-info.index') }}">
                                    <i class="fas fa-circle-info me-1"></i> {{ __('app.liga_info') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}"
                                   href="{{ route('news.index') }}">
                                    <i class="fas fa-newspaper me-1"></i> News
                                </a>
                            </li>
                        </ul>

                        <!-- Admin Right Nav -->
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cogs me-1"></i> Admin Settings
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                    <li class="dropdown-header text-uppercase small" style="letter-spacing:0.5px;">Management</li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('news.admin') }}">
                                            <i class="fas fa-newspaper me-2"></i> Manage News
                                        </a>
                                        <a class="dropdown-item" href="{{ route('liga-info.index') }}">
                                            <i class="fas fa-circle-info me-2"></i> {{ __('app.liga_info') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('lineup-submissions.index') }}">
                                            <i class="fas fa-clipboard-check me-2"></i> {{ __('app.lineup_submissions') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('registration.index') }}">
                                            <i class="fas fa-clipboard-list me-2"></i> Pra Registration
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header text-uppercase small" style="letter-spacing:0.5px;">Administration</li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.payments') }}">
                                            <i class="fas fa-credit-card me-2"></i> {{ __('app.payments') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('disciplinary.index') }}">
                                            <i class="fas fa-gavel me-2"></i> {{ __('app.disciplinary') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('affiliate-fees.index') }}">
                                            <i class="fas fa-id-card me-2"></i> {{ __('app.affiliate_fees') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('promotions.index') }}">
                                            <i class="fas fa-trophy me-2"></i> Promotions
                                        </a>
                                    </li>
                                    @if(auth()->user()->isSuper())
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header text-uppercase small" style="letter-spacing:0.5px;">{{ __('app.match_commissioners') }}</li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('mc-assignment.index') }}">
                                            <i class="fas fa-user-tag me-2"></i> {{ __('app.mc_assignment') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('head-mc.dashboard') }}">
                                            <i class="fas fa-clipboard-check me-2"></i> {{ __('app.head_mc_dashboard') }}
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                            <i class="fas fa-users-cog me-2"></i> {{ __('app.user_management') }}
                                        </a>
                                    </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header text-uppercase small" style="letter-spacing:0.5px;">Account</li>
                                    <li>
                                        <span class="dropdown-item-text text-light">
                                            <i class="fas fa-user-circle me-2"></i> {{ Auth::user()->name }}
                                        </span>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i> {{ __('app.logout') }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>

                    @else
                        {{-- ============================================= --}}
                        {{-- TEAM MANAGER NAVIGATION (new grouped layout)  --}}
                        {{-- ============================================= --}}
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <!-- Dashboard -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                   href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i> {{ __('app.dashboard') }}
                                </a>
                            </li>

                            @if(auth()->user()->isHeadMatchCommissioner())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mc-assignment.*') ? 'active' : '' }}"
                                   href="{{ route('mc-assignment.index') }}">
                                    <i class="fas fa-user-tag me-1"></i> {{ __('app.mc_assignment') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('head-mc.*') ? 'active' : '' }}"
                                   href="{{ route('head-mc.dashboard') }}">
                                    <i class="fas fa-clipboard-check me-1"></i> {{ __('app.head_mc_dashboard') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('disciplinary.*') ? 'active' : '' }}"
                                   href="{{ route('disciplinary.index') }}">
                                    <i class="fas fa-gavel me-1"></i> {{ __('app.disciplinary') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->isMatchCommissioner() || auth()->user()->isHeadMatchCommissioner())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('matches.*') ? 'active' : '' }}"
                                   href="{{ route('matches.index') }}">
                                    <i class="fas fa-calendar-days me-1"></i> {{ __('app.matches') }}
                                </a>
                            </li>
                            @endif

                            <!-- Pra Registration (direct link) -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('registration.*') ? 'active' : '' }}"
                                   href="{{ route('registration.index') }}">
                                    <i class="fas fa-clipboard-list me-1"></i> {{ __('app.pra_registration') }}
                                </a>
                            </li>

                            <!-- League Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('competitions.*', 'matches.*', 'standings.*', 'top-scorers') ? 'active' : '' }}"
                                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-trophy me-1"></i> {{ __('app.league') }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('competitions.*') ? 'active' : '' }}"
                                           href="{{ route('competitions.index') }}">
                                            <i class="fas fa-trophy me-2"></i> {{ __('app.competitions') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('teams.index') ? 'active' : '' }}"
                                           href="{{ route('teams.index') }}">
                                            <i class="fas fa-shield-halved me-2"></i> {{ __('app.teams') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('matches.*') ? 'active' : '' }}"
                                           href="{{ route('matches.index') }}">
                                            <i class="fas fa-calendar-days me-2"></i> {{ __('app.matches') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('standings.*') ? 'active' : '' }}"
                                           href="{{ route('standings.index') }}">
                                            <i class="fas fa-ranking-star me-2"></i> {{ __('app.standings') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('top-scorers') ? 'active' : '' }}"
                                           href="{{ route('top-scorers') }}">
                                            <i class="fas fa-futbol me-2"></i> Top Scorers
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- Liga Info (direct link) -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('liga-info.*') ? 'active' : '' }}"
                                   href="{{ route('liga-info.index') }}">
                                    <i class="fas fa-circle-info me-1"></i> {{ __('app.liga_info') }}
                                </a>
                            </li>

                            <!-- News -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}"
                                   href="{{ route('news.index') }}">
                                    <i class="fas fa-newspaper me-1"></i> News
                                </a>
                            </li>

                            <!-- Line-Up Submissions (direct link) -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('lineup-submissions.*') ? 'active' : '' }}"
                                   href="{{ route('lineup-submissions.index') }}">
                                    <i class="fas fa-clipboard-check me-1"></i> {{ __('app.lineup_submissions') }}
                                </a>
                            </li>

                            <!-- My Payments (direct link) -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('my.payments') ? 'active' : '' }}"
                                   href="{{ route('my.payments') }}">
                                    <i class="fas fa-receipt me-1"></i> {{ __('app.my_payments') }}
                                </a>
                            </li>

                            <!-- My Fines (direct link) -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('my.fines') ? 'active' : '' }}"
                                   href="{{ route('my.fines') }}">
                                    <i class="fas fa-gavel me-1"></i> {{ __('app.my_fines') }}
                                </a>
                            </li>

                            <!-- My Teams Dropdown -->
                            @php $myTeams = auth()->user()->managedTeams()->with('competition')->orderBy('name')->get(); @endphp
                            @if($myTeams->count() > 0)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('teams.show', 'teams.edit', 'players.*', 'officials.*') ? 'active' : '' }}"
                                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-shield-halved me-1"></i> {{ __('app.my_team') }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                    @foreach($myTeams as $mt)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('teams.show', $mt->id) }}">
                                            <i class="fas fa-shield me-2"></i> {{ $mt->short_name ?: $mt->name }}
                                            @if($mt->competition)
                                            <br><small class="text-muted ps-4">{{ $mt->competition->short_name ?: $mt->competition->name }}</small>
                                            @endif
                                        </a>
                                    </li>
                                    @if(!$loop->last)<li><hr class="dropdown-divider"></li>@endif
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                        </ul>

                        <!-- Team Manager Right Nav -->
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <!-- ENG / BM Language Toggle -->
                            <li class="nav-item me-2">
                                <div class="nav-link d-flex align-items-center py-2" style="cursor:default;">
                                    <a href="{{ route('language.switch', 'en') }}"
                                       class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-light' : 'btn-outline-light' }} px-2 py-0"
                                       style="font-size:0.8rem; line-height:1.6;">ENG</a>
                                    <span class="text-muted mx-1">|</span>
                                    <a href="{{ route('language.switch', 'ms') }}"
                                       class="btn btn-sm {{ app()->getLocale() === 'ms' ? 'btn-light' : 'btn-outline-light' }} px-2 py-0"
                                       style="font-size:0.8rem; line-height:1.6;">BM</a>
                                </div>
                            </li>

                            <!-- Account Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                    <li>
                                        <span class="dropdown-item-text text-muted small">
                                            <i class="fas fa-envelope me-2"></i> {{ Auth::user()->email }}
                                        </span>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i> {{ __('app.logout') }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    @endif
                @else
                    {{-- ============================================= --}}
                    {{-- GUEST NAVIGATION                              --}}
                    {{-- ============================================= --}}
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('competitions.*') ? 'active' : '' }}"
                               href="{{ route('competitions.index') }}">
                                <i class="fas fa-trophy me-1"></i> {{ __('app.competitions') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('standings.*') ? 'active' : '' }}"
                               href="{{ route('standings.index') }}">
                                <i class="fas fa-ranking-star me-1"></i> {{ __('app.standings') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('top-scorers') ? 'active' : '' }}"
                               href="{{ route('top-scorers') }}">
                                <i class="fas fa-futbol me-1"></i> Top Scorers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('liga-info.*') ? 'active' : '' }}"
                               href="{{ route('liga-info.index') }}">
                                <i class="fas fa-circle-info me-1"></i> {{ __('app.liga_info') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}"
                               href="{{ route('news.index') }}">
                                <i class="fas fa-newspaper me-1"></i> News
                            </a>
                        </li>
                    </ul>

                    <!-- Guest Right Nav -->
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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
                    </ul>
                @endauth

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
        <div class="footer-main">
            <div class="container">
                <div class="row g-4">
                    <!-- Left: Contact Info -->
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset('images/jbfa_logo.png') }}" alt="JBFA" style="height:40px;" class="me-2">
                            <div>
                                <strong class="text-white" style="font-size:0.9rem;">PERSATUAN BOLASEPAK</strong><br>
                                <strong class="text-white" style="font-size:0.9rem;">JOHOR BAHRU (JBFA)</strong>
                            </div>
                        </div>
                        <div class="footer-contact">
                            <p><i class="fas fa-map-marker-alt text-success me-2"></i>D/A Pejabat Daerah Johor Bahru</p>
                            <p class="ps-4">Jalan Datin Halimah</p>
                            <p class="ps-4">80350 Johor Bahru, Johor</p>
                            <p class="mt-2"><i class="fas fa-phone text-success me-2"></i>07-2331963</p>
                            <p><i class="fas fa-fax text-success me-2"></i>07-2333132</p>
                            <p><i class="fas fa-envelope text-success me-2"></i><a href="mailto:johorbahru.fa.jbfa@gmail.com">johorbahru.fa.jbfa@gmail.com</a></p>
                        </div>
                        <div class="footer-social mt-3">
                            <a href="https://www.facebook.com/p/Johor-Bahru-Football-Association-100085283033451/" target="_blank" title="JBFA Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://www.instagram.com/ligajohorbahru/" target="_blank" title="JBFA League Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.facebook.com/p/Liga-Johor-Bahru-100077198288508/" target="_blank" title="Liga JB Facebook"><i class="fab fa-facebook"></i></a>

                        </div>
                    </div>

                    <!-- Middle: Quick Links -->
                    <div class="col-lg-3 col-md-6">
                        <h6><i class="fas fa-link me-1"></i> Quick Links</h6>
                        <ul class="list-unstyled footer-links">
                            <li><a href="{{ route('home') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem;"></i> Home</a></li>
                            <li><a href="{{ route('competitions.index') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem;"></i> Competitions</a></li>
                            <li><a href="{{ route('matches.index') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem;"></i> Fixtures & Results</a></li>
                            <li><a href="{{ route('standings.index') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem;"></i> Standings</a></li>
                            <li><a href="{{ route('teams.index') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem;"></i> Teams</a></li>
                            <li><a href="{{ route('players.index') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem;"></i> Players</a></li>
                            <li><a href="{{ route('top-scorers') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem;"></i> Top Scorers</a></li>
                        </ul>
                    </div>

                    <!-- Right: Google Maps -->
                    <div class="col-lg-5 col-md-12">
                        <h6><i class="fas fa-map-marked-alt me-1"></i> Our Location</h6>
                        <div style="border-radius:8px;overflow:hidden;border:2px solid rgba(255,255,255,0.1);">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.3766!2d103.7553!3d1.4740!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da177b62e1d985%3A0x5e4a3eff60753c7d!2sPejabat%20Daerah%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1718500000000!5m2!1sen!2smy"
                                width="100%"
                                height="220"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                        <a href="https://maps.google.com/?q=Pejabat+Daerah+Johor+Bahru,+Jalan+Datin+Halimah,+80350+Johor+Bahru" target="_blank" class="btn btn-sm btn-outline-success mt-2">
                            <i class="fas fa-external-link-alt me-1"></i> Open in Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0">
                            &copy; {{ date('Y') }} Persatuan Bolasepak Johor Bahru (JBFA). {{ __('app.all_rights_reserved') }}.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small>JBFA Football League {{ __('app.management_system') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
