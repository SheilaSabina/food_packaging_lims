<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Supervisor | LIMS Kemasan Food Safety Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b', // Amber-500
                            600: '#d97706', // Amber-600 (Warna tombol utama)
                            700: '#b45309',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; }
        ::-webkit-scrollbar-thumb:hover { background: #d97706; }
    </style>
</head>
<body class="h-full antialiased text-slate-300">
<div class="flex h-full">
    @include('supervisor._sidebar', ['active' => 'dashboard'])

    <main class="flex-1 overflow-y-auto bg-slate-950 p-8 xl:p-12">
        <header class="mb-10 flex items-center justify-between pb-6 border-b border-slate-800">
            <div>
                <p class="text-sm text-slate-500">Supervisor > <span class="text-amber-500">Dashboard Verifikasi</span></p>
                <h1 class="mt-2 text-3xl font-bold text-white">Dashboard Supervisor</h1>
                <p class="mt-1 text-slate-400">Review dan verifikasi hasil pengujian yang sudah dikirim teknisi.</p>
            </div>
            <div class="flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900 px-4 py-2">
                <div class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></div>
                <span class="text-sm text-slate-300">Menunggu Verifikasi: <span class="font-bold text-white">{{ $sessions->count() }}</span></span>
            </div>
        </header>

        <section class="grid gap-8">
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl overflow-x-auto">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                    <h2 class="text-xl font-semibold text-white">Sesi Menunggu Verifikasi</h2>
                    <span class="rounded-full bg-amber-600 px-3 py-1 text-xs text-white">{{ $sessions->count() }} menunggu</span>
                </div>
                @if($sessions->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-800/50 p-8 text-center text-slate-400">
                        Tidak ada sesi yang menunggu verifikasi saat ini.
                    </div>
                @else
                    <table class="min-w-full text-left text-sm text-slate-300">
                        <thead class="border-b border-slate-700 text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Sesi ID</th>
                                <th class="px-4 py-3">Klien</th>
                                <th class="px-4 py-3">Teknisi</th>
                                <th class="px-4 py-3">Ringkasan</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                <tr class="border-b border-slate-800 hover:bg-slate-900/60">
                                    <td class="px-4 py-4 font-semibold text-white">{{ $session->id }}</td>
                                    <td class="px-4 py-4">{{ $session->order?->client_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4">{{ $session->technician?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4">
                                        @php
                                            $summary = $session->getStatusSummary();
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $summary['overall_status'] === 'PASS' ? 'bg-green-950 text-green-300 border border-green-800' : ($summary['overall_status'] === 'FAIL' ? 'bg-red-950 text-red-300 border border-red-800' : 'bg-slate-800 text-slate-300 border border-slate-700') }}">
                                                {{ $summary['overall_status'] }}
                                            </span>
                                            <span class="text-xs text-slate-500">
                                                ({{ $summary['pass'] }} Pass, {{ $summary['fail'] }} Fail, {{ $summary['unknown'] }} Unknown)
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('test.verify.dashboard', ['session' => $session->id]) }}" class="rounded-xl bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-500 transition">
                                            Review Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>

        <footer class="mt-12 pt-6 border-t border-slate-800 text-center text-xs text-slate-600">
            Sistem Manajemen Laboratorium LIMS Kemasan V1.0 | Departemen Quality Control | © 2026 Perusahaan Besar Indonesia Tbk.
        </footer>
    </main>
</div>
</body>
</html>
