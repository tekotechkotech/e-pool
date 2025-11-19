<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F8F5] scroll-smooth scroll-pt-36 sm:scroll-pt-40">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Survey Kelayakan Lapangan - e-Pool BAZNAS</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Memuat file CSS/JS utama melalui Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-[#F4F8F5] text-[#0F172A] min-h-full selection:bg-emerald-500 selection:text-white">
    @yield('content')

    <!-- Global Confirm / Alert Dialog Modal -->
    <x-confirm-dialog />
</body>

</html>