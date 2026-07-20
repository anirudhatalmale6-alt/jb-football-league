<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found - JBFA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 550px; text-align: center; }
        .error-icon { font-size: 4rem; color: #ffc107; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="error-icon mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="fw-bold text-warning mb-3">Page Not Found</h2>
                <p class="text-muted mb-4">The page you are looking for does not exist or may have been moved.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ url('/dashboard') }}" class="btn btn-success">
                        <i class="fas fa-tachometer-alt me-1"></i> Back to Dashboard
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-1"></i> Go to Home
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <img src="{{ asset('images/logo.png') }}" alt="JBFA" style="height: 40px; opacity: 0.5;" onerror="this.style.display='none'">
        </div>
    </div>
</body>
</html>