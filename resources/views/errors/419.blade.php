<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Expired - JBFA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 550px; text-align: center; }
        .error-icon { font-size: 4rem; color: #0d6efd; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div class="error-icon mb-3">
                    <i class="fas fa-clock"></i>
                </div>
                <h2 class="fw-bold text-primary mb-3">Page Expired</h2>
                <p class="text-muted mb-4">Your session has expired. Please refresh the page and try again.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-primary">
                        <i class="fas fa-redo me-1"></i> Try Again
                    </a>
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>