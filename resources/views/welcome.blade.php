<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="Liga JBFA 2026 | Johor Bahru Football Association" />
    <meta property="og:description" content="Portal rasmi Liga JBFA 2026. Jadual perlawanan, keputusan, kedudukan & pendaftaran pasukan. 30 pasukan, 4 pertandingan." />
    <meta property="og:image" content="{{ asset('images/og-image.png') }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Liga JBFA 2026" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Liga JBFA 2026 | Johor Bahru Football Association" />
    <meta name="twitter:description" content="Portal rasmi Liga JBFA 2026. Jadual perlawanan, keputusan, kedudukan & pendaftaran pasukan. 30 pasukan, 4 pertandingan." />
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}" />
    <meta name="description" content="Portal rasmi Liga JBFA 2026. Jadual perlawanan, keputusan, kedudukan & pendaftaran pasukan. 30 pasukan, 4 pertandingan." />
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <title>{{ config('app.name', 'JBFA Football League') }}</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="{{ asset('css/fontawesome.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --jb-green: #198754;
            --jb-green-dark: #146c43;
        }

        .hero-section {
            background:
                radial-gradient(ellipse 120% 60% at 50% 0%, rgba(30, 80, 140, 0.5) 0%, transparent 70%),
                radial-gradient(ellipse 80% 50% at 20% 20%, rgba(200, 164, 21, 0.15) 0%, transparent 60%),
                radial-gradient(ellipse 80% 50% at 80% 20%, rgba(200, 164, 21, 0.15) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 50% 100%, rgba(25, 135, 84, 0.2) 0%, transparent 60%),
                linear-gradient(180deg, #050a18 0%, #0a1628 30%, #0d1f3c 60%, #081225 100%);
            min-height: 80vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                linear-gradient(135deg, transparent 40%, rgba(200, 164, 21, 0.08) 45%, transparent 50%),
                linear-gradient(225deg, transparent 40%, rgba(200, 164, 21, 0.08) 45%, transparent 50%),
                radial-gradient(circle at 50% 50%, transparent 30%, rgba(0,0,0,0.3) 100%);
            pointer-events: none;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            right: -50%;
            bottom: -50%;
            background:
                conic-gradient(from 0deg at 50% 35%, transparent 0deg, rgba(200, 164, 21, 0.03) 30deg, transparent 60deg, transparent 120deg, rgba(200, 164, 21, 0.03) 150deg, transparent 180deg, transparent 240deg, rgba(200, 164, 21, 0.03) 270deg, transparent 300deg);
            animation: slowRotate 60s linear infinite;
            pointer-events: none;
        }

        @keyframes slowRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .hero-glow {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            pointer-events: none;
        }

        .hero-glow-1 {
            top: -100px;
            left: 10%;
            background: #c8a415;
        }

        .hero-glow-2 {
            top: -80px;
            right: 10%;
            background: #c8a415;
        }

        .hero-glow-3 {
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: #198754;
            width: 500px;
            opacity: 0.1;
        }

        .hero-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(200, 164, 21, 0.2), rgba(200, 164, 21, 0.05));
            border: 1px solid rgba(200, 164, 21, 0.3);
            border-radius: 50px;
            padding: 6px 20px;
            font-size: 0.85rem;
            color: #c8a415;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .hero-divider {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #c8a415, transparent);
            margin: 0 auto 1.5rem;
            border-radius: 2px;
        }

        .hero-logo-ring {
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            padding: 12px;
            border-radius: 50%;
            border: 2px solid rgba(200, 164, 21, 0.3);
            background: rgba(200, 164, 21, 0.05);
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
        }

        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.5rem;
        }

        .section-title {
            position: relative;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--jb-green);
            border-radius: 2px;
        }

        .section-title.text-center::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .standings-highlight {
            background-color: rgba(25, 135, 84, 0.05);
        }

        .match-card {
            border-left: 4px solid var(--jb-green);
        }

        .footer {
            background-color: #1a1e24;
            color: #adb5bd;
            padding: 0;
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
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/jbfa_logo.png') }}" alt="JBFA" style="height:30px;" class="me-2">JBFA
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarWelcome"
                    aria-controls="navbarWelcome" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarWelcome">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#fixtures">
                            <i class="fas fa-calendar-days me-1"></i> {{ __('app.fixtures') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#standings">
                            <i class="fas fa-ranking-star me-1"></i> {{ __('app.standings') }}
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/dashboard') }}">
                                <i class="fas fa-tachometer-alt me-1"></i> {{ __('app.dashboard') }}
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i> {{ __('app.login') }}
                            </a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="btn btn-success btn-sm ms-2 px-3" href="{{ route('register') }}">
                                    <i class="fas fa-user-plus me-1"></i> {{ __('app.register') }}
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-white">
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>
        <div class="hero-glow hero-glow-3"></div>
        <div class="container position-relative" style="z-index:1;">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="hero-logo-ring">
                        <img src="{{ asset('images/jbfa_logo.png') }}" alt="JBFA" style="height:100px;">
                    </div>
                    <div class="hero-badge">Official League System</div>
                    <h1 class="hero-title mb-3">{{ __('app.jbfa_name') }}</h1>
                    <div class="hero-divider"></div>
                    <h3 class="fw-bold mb-3" style="color: #c8a415; letter-spacing: 4px;">JBFA</h3>
                    <p class="hero-subtitle mb-4" style="max-width: 600px; margin: 0 auto;">
                        {{ __('app.hero_description') }}
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="#fixtures" class="btn btn-success btn-lg px-4">
                            <i class="fas fa-calendar-days me-2"></i> {{ __('app.view_fixtures') }}
                        </a>
                        <a href="#standings" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-ranking-star me-2"></i> {{ __('app.standings') }}
                        </a>
                        <a href="{{ route('matches.index') }}" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-futbol me-2"></i> {{ __('app.results') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="feature-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h5 class="fw-bold">{{ __('app.competitions') }}</h5>
                            <p class="text-muted mb-3">{{ __('app.competitions_desc') }}</p>
                            <a href="{{ route('competitions.index') }}" class="btn btn-outline-success btn-sm">
                                {{ __('app.view_competitions') }} <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <h5 class="fw-bold">{{ __('app.teams') }}</h5>
                            <p class="text-muted mb-3">{{ __('app.teams_desc') }}</p>
                            <a href="{{ route('teams.index') }}" class="btn btn-outline-primary btn-sm">
                                {{ __('app.view_teams') }} <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="feature-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="fw-bold">{{ __('app.players') }}</h5>
                            <p class="text-muted mb-3">{{ __('app.players_desc') }}</p>
                            <a href="{{ route('players.index') }}" class="btn btn-outline-info btn-sm">
                                {{ __('app.view_players') }} <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Fixtures -->
    <section id="fixtures" class="py-5 bg-light">
        <div class="container">
            <h3 class="section-title fw-bold">
                <i class="fas fa-calendar-days text-success me-2"></i>{{ __('app.upcoming_fixtures') }}
            </h3>

            @if($upcomingMatches->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-calendar-xmark fa-3x mb-3 d-block"></i>
                    <p class="fs-5">{{ __('app.no_upcoming_fixtures') }}</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($upcomingMatches as $match)
                        <div class="col-md-6">
                            <div class="card match-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($match->match_date)->format('D, d M Y') }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($match->match_date)->format('h:i A') }}
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-center flex-fill">
                                            <h6 class="fw-bold mb-0">{{ $match->homeTeam->name }}</h6>
                                            <small class="text-muted">{{ __('app.home') }}</small>
                                        </div>
                                        <div class="text-center px-3">
                                            @if($match->isLive())
                                            <div class="bg-success text-white rounded px-3 py-2">
                                                <h5 class="fw-bold mb-0">{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</h5>
                                            </div>
                                            <span class="badge bg-danger mt-1"><i class="fas fa-circle me-1" style="font-size:6px;"></i>LIVE {{ $match->match_minute }}</span>
                                        @elseif($match->status === "half_time")
                                            <div class="bg-dark text-white rounded px-3 py-2">
                                                <h5 class="fw-bold mb-0">{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</h5>
                                            </div>
                                            <span class="badge bg-warning text-dark mt-1">HT</span>
                                        @elseif($match->isFinished())
                                            <div class="bg-dark text-white rounded px-3 py-2">
                                                <h5 class="fw-bold mb-0">{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</h5>
                                            </div>
                                            <span class="badge bg-secondary mt-1">FT</span>
                                        @else
                                            <span class="badge bg-dark fs-6 px-3 py-2">VS</span>
                                        @endif
                                        </div>
                                        <div class="text-center flex-fill">
                                            <h6 class="fw-bold mb-0">{{ $match->awayTeam->name }}</h6>
                                            <small class="text-muted">{{ __('app.away') }}</small>
                                        </div>
                                    </div>
                                    @if($match->venue)
                                        <div class="mt-2 text-center">
                                            <small class="text-muted">
                                                <i class="fas fa-location-dot me-1"></i> {{ $match->venue }}
                                            </small>
                                        </div>
                                    @endif
                                    @if($match->competition)
                                        <div class="mt-1 text-center">
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                {{ $match->competition->name }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('matches.index') }}" class="btn btn-success">
                        {{ __('app.view_all_matches') }} <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Current Standings -->
    <section id="standings" class="py-5 bg-white">
        <div class="container">
            <h3 class="section-title fw-bold">
                <i class="fas fa-ranking-star text-success me-2"></i>{{ __('app.current_standings') }}
            </h3>

            @if($standings->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-table fa-3x mb-3 d-block"></i>
                    <p class="fs-5">{{ __('app.no_standings_data') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">Pos</th>
                                <th>{{ __('app.team') }}</th>
                                <th class="text-center">{{ __('app.played') }}</th>
                                <th class="text-center">{{ __('app.won') }}</th>
                                <th class="text-center">{{ __('app.drawn') }}</th>
                                <th class="text-center">{{ __('app.lost') }}</th>
                                <th class="text-center">GF</th>
                                <th class="text-center">GA</th>
                                <th class="text-center">GD</th>
                                <th class="text-center fw-bold">{{ __('app.points') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($standings as $index => $standing)
                                <tr class="{{ $index < 3 ? 'standings-highlight' : '' }}">
                                    <td>
                                        @if($index === 0)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-crown me-1"></i>1
                                            </span>
                                        @elseif($index < 3)
                                            <span class="badge bg-success">{{ $index + 1 }}</span>
                                        @else
                                            <span class="text-muted fw-semibold">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">{{ $standing->team->name }}</td>
                                    <td class="text-center">{{ $standing->played }}</td>
                                    <td class="text-center">{{ $standing->won }}</td>
                                    <td class="text-center">{{ $standing->drawn }}</td>
                                    <td class="text-center">{{ $standing->lost }}</td>
                                    <td class="text-center">{{ $standing->goals_for }}</td>
                                    <td class="text-center">{{ $standing->goals_against }}</td>
                                    <td class="text-center">
                                        @php $gd = $standing->goals_for - $standing->goals_against; @endphp
                                        <span class="{{ $gd > 0 ? 'text-success' : ($gd < 0 ? 'text-danger' : 'text-muted') }}">
                                            {{ $gd > 0 ? '+' : '' }}{{ $gd }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold fs-5">{{ $standing->points }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('standings.index') }}" class="btn btn-outline-success">
                        {{ __('app.full_standings_table') }} <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

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
                            <p><i class="fas fa-fax text-success me-2"></i>07-2233132</p>
                            <p><i class="fas fa-envelope text-success me-2"></i><a href="mailto:johorbahru.fa.jbfa@gmail.com">johorbahru.fa.jbfa@gmail.com</a></p>
                        </div>
                        <div class="footer-social mt-3">
                            <a href="https://www.facebook.com/p/Johor-Bahru-Football-Association-100085283033451/" target="_blank" title="JBFA Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://www.instagram.com/ligajohorbahru/" target="_blank" title="JBFA League Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.facebook.com/p/Liga-Johor-Bahru-100077198288508/" target="_blank" title="Liga JB Facebook"><i class="fab fa-facebook"></i></a>
                            
                        </div>
                    </div>

                    <!-- Middle: Quick Links (public pages only) -->
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
</body>
</html>
