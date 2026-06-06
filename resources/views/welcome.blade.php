<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'JB Football League') }}</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --jb-green: #198754;
            --jb-green-dark: #146c43;
        }

        .hero-section {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3d0f 100%);
            min-height: 70vh;
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
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23198754' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
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
            background-color: #212529;
            color: #adb5bd;
        }

        .footer a {
            color: #198754;
            text-decoration: none;
        }

        .footer a:hover {
            color: #20c997;
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
                            <i class="fas fa-calendar-days me-1"></i> Fixtures
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#standings">
                            <i class="fas fa-ranking-star me-1"></i> Standings
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/dashboard') }}">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="btn btn-success btn-sm ms-2 px-3" href="{{ route('register') }}">
                                    <i class="fas fa-user-plus me-1"></i> Register
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
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="mb-3">
                        <img src="{{ asset('images/jbfa_logo.png') }}" alt="JBFA" style="height:120px;" class="mb-3">
                    </div>
                    <h1 class="hero-title mb-2">Persatuan Bola Sepak<br>Johor Bahru</h1>
                    <h3 class="text-success fw-bold mb-3">JBFA</h3>
                    <p class="hero-subtitle mb-4">
                        The premier football competition in Johor Bahru.
                        Follow your favourite teams, track fixtures, and stay updated with live standings.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="#fixtures" class="btn btn-success btn-lg px-4">
                            <i class="fas fa-calendar-days me-2"></i> View Fixtures
                        </a>
                        <a href="#standings" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-ranking-star me-2"></i> Standings
                        </a>
                        <a href="{{ route('matches.index') }}" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-futbol me-2"></i> Results
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
                            <h5 class="fw-bold">Competitions</h5>
                            <p class="text-muted mb-3">Browse all active leagues and cup tournaments.</p>
                            <a href="{{ route('competitions.index') }}" class="btn btn-outline-success btn-sm">
                                View Competitions <i class="fas fa-arrow-right ms-1"></i>
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
                            <h5 class="fw-bold">Teams</h5>
                            <p class="text-muted mb-3">Discover all participating teams and their rosters.</p>
                            <a href="{{ route('teams.index') }}" class="btn btn-outline-primary btn-sm">
                                View Teams <i class="fas fa-arrow-right ms-1"></i>
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
                            <h5 class="fw-bold">Players</h5>
                            <p class="text-muted mb-3">View player profiles, stats, and top performers.</p>
                            <a href="{{ route('players.index') }}" class="btn btn-outline-info btn-sm">
                                View Players <i class="fas fa-arrow-right ms-1"></i>
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
                <i class="fas fa-calendar-days text-success me-2"></i>Upcoming Fixtures
            </h3>

            @if($upcomingMatches->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-calendar-xmark fa-3x mb-3 d-block"></i>
                    <p class="fs-5">No upcoming fixtures scheduled at the moment.</p>
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
                                            <small class="text-muted">Home</small>
                                        </div>
                                        <div class="text-center px-3">
                                            <span class="badge bg-dark fs-6 px-3 py-2">VS</span>
                                        </div>
                                        <div class="text-center flex-fill">
                                            <h6 class="fw-bold mb-0">{{ $match->awayTeam->name }}</h6>
                                            <small class="text-muted">Away</small>
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
                        View All Matches <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Current Standings -->
    <section id="standings" class="py-5 bg-white">
        <div class="container">
            <h3 class="section-title fw-bold">
                <i class="fas fa-ranking-star text-success me-2"></i>Current Standings
            </h3>

            @if($standings->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-table fa-3x mb-3 d-block"></i>
                    <p class="fs-5">No standings data available yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">Pos</th>
                                <th>Team</th>
                                <th class="text-center">Played</th>
                                <th class="text-center">Won</th>
                                <th class="text-center">Drawn</th>
                                <th class="text-center">Lost</th>
                                <th class="text-center">GF</th>
                                <th class="text-center">GA</th>
                                <th class="text-center">GD</th>
                                <th class="text-center fw-bold">Points</th>
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
                        Full Standings Table <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">
                        <img src="{{ asset('images/jbfa_logo.png') }}" alt="JBFA" style="height:20px;" class="me-1">
                        &copy; {{ date('Y') }} JBFA. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>Johor Bahru Football League Management System</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
