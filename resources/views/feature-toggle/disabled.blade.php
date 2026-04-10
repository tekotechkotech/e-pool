<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fitur Tidak Tersedia - {{ config('app.name', 'E-POOL BAZNAS') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f4f9f6 0%, #eaf5ef 50%, #d5ebd9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .feature-disabled-card {
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            max-width: 500px;
            text-align: center;
        }
        .feature-icon-wrapper {
            width: 80px;
            height: 80px;
            background: #eaf5ef;
            color: #118243;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .feature-title {
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .feature-message {
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        .btn-survey {
            background: #118243;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(17, 130, 67, 0.25);
        }
        .btn-survey:hover {
            background: #0e6d38;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(17, 130, 67, 0.3);
        }
        .status-badge {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-top: 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }
    </style>
</head>
<body>
    <div class="feature-disabled-card">
        <div class="feature-icon-wrapper">
            <x-heroicon-o-wrench-screwdriver style="width: 40px; height: 40px;" />
        </div>
        
        <h1 class="feature-title">Fitur Pengajuan Bantuan</h1>
        
        <p class="feature-message">
            Fitur pengajuan bantuan sosial sementara tidak tersedia.
            Silakan gunakan fitur survey untuk melanjutkan proses penilaian kelayakan.
        </p>
        
        <div>
            <a href="{{ $faqUrl }}" class="btn-survey">
                <x-heroicon-o-clipboard-document-check style="width: 20px; height: 20px;" />
                <span>Lanjutkan ke Survey Penilaian</span>
            </a>
        </div>
        
        <div>
            <div class="status-badge">
                <x-heroicon-o-information-circle style="width: 16px; height: 16px;" />
                <span>Status: Non-Aktif</span>
            </div>
        </div>
        
        <hr class="my-4" style="border-color: #e2e8f0;">
        
        <small class="text-muted">
            Silakan hubungi administrator jika Anda memerlukan bantuan lebih lanjut.
        </small>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
