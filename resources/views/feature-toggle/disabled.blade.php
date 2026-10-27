<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fitur Tidak Tersedia - {{ config('app.name', 'E-POOL BAZNAS') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .feature-disabled-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            text-align: center;
        }
        .feature-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        .feature-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .feature-message {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-survey {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-survey:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .status-badge {
            background: #e9ecef;
            color: #6c757d;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            margin-top: 1rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="feature-disabled-card">
        <div class="feature-icon">
            <i class="bi bi-tools"></i>
        </div>
        
        <h1 class="feature-title">Fitur Pengajuan Bantuan</h1>
        
        <p class="feature-message">
            Fitur pengajuan bantuan sosial sementara tidak tersedia.
            Silakan gunakan fitur survey untuk melanjutkan proses penilaian kelayakan.
        </p>
        
        <a href="{{ $faqUrl }}" class="btn btn-primary btn-survey">
            <i class="bi bi-clipboard-check me-2"></i>
            Lanjutkan ke Survey Penilaian
        </a>
        
        <div class="status-badge">
            <i class="bi bi-info-circle me-1"></i>
            Status: Non-Aktif
        </div>
        
        <hr class="my-4">
        
        <small class="text-muted">
            Silakan hubungi administrator jika Anda memerlukan bantuan lebih lanjut.
        </small>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
