<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - e-pool BAZNAS</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Memuat file CSS/JS utama melalui Vite agar Tailwind dan Turbo aktif -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-[#f4f7f6] text-slate-800 min-h-screen grid place-items-center px-4 sm:px-6 lg:px-8">
    @yield('content')
    {{ $slot ?? '' }}

    <!-- Global Confirm / Alert Dialog Modal -->
    <x-confirm-dialog />
</body>

</html>
