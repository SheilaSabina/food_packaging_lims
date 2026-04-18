<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Mandiri | LIMS Kemasan Food Safety Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
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
        ::-webkit-scrollbar-thumb:hover { background: #0d9488; }
    </style>
</head>
<body class="h-full antialiased text-slate-300">
<div class="flex h-full">
    @include('technician._sidebar', ['active' => 'review', 'session' => $session])

    <main class="flex-1 overflow-y-auto bg-slate-950 p-8 xl:p-12">
        <header class="mb-10 flex items-center justify-between pb-6 border-b border-slate-800">
            <div>
                <p class="text-sm text-slate-500">Dashboard > Pelaksanaan Uji > <span class="text-accent-500">Verifikasi Mandiri</span></p>
                <h1 class="mt-2 text-3xl font-bold text-white">Verifikasi Mandiri</h1>
                <p class="mt-1 text-slate-400">Review hasil pengujian sebelum mengunci sesi.</p>
            </div>
            <div class="flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900 px-4 py-2">
                <div class="h-2 w-2 rounded-full bg-accent-500 animate-pulse"></div>
                <span class="text-sm text-slate-300">Sesi ID: <span class="font-bold text-white">{{ $session->id }}</span></span>
            </div>
        </header>

        <section class="grid gap-8 xl:grid-cols-[2fr_1fr]">
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Ringkasan Hasil Semua Parameter</h2>
                        <p class="mt-1 text-sm text-slate-500">Lihat status, nilai dan bukti sebelum di-lock.</p>
                    </div>
                    <span class="rounded-full bg-accent-600 px-3 py-1 text-xs text-white">{{ $summary['overall_status'] }}</span>
                </div>

                @if($results->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-800/50 p-8 text-center text-slate-400">
                        Belum ada hasil uji yang bisa direview. Silakan input nilai pada halaman pelaksanaan uji.
                    </div>
                @else
                    <div class="grid gap-4">
                        @foreach($results as $result)
                            <div class="rounded-2xl border border-slate-700 bg-slate-950 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm text-slate-400">Parameter</p>
                                        <p class="mt-1 text-lg font-semibold text-white">{{ $result->parameter->name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-slate-400">Status</p>
                                        <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $result->result_status === 'PASS' ? 'bg-green-950 text-green-300 border border-green-800' : ($result->result_status === 'FAIL' ? 'bg-red-950 text-red-300 border border-red-800' : 'bg-slate-800 text-slate-300 border border-slate-700') }}">
                                            {{ $result->result_status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl bg-slate-800 p-4">
                                        <p class="text-xs uppercase text-slate-500">Nilai</p>
                                        <p class="mt-2 text-lg font-semibold text-white">{{ $result->measured_value }} {{ $result->unit }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-800 p-4">
                                        <p class="text-xs uppercase text-slate-500">Standar</p>
                                        <p class="mt-2 text-lg font-semibold text-white">
                                            {{ $result->appliedStandard?->reference_document ?? 'Belum ditentukan' }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-800 p-4">
                                        <p class="text-xs uppercase text-slate-500">Bukti</p>
                                        <p class="mt-2 text-lg font-semibold text-white">{{ $result->evidences()->count() }} file</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl">
                <h3 class="text-lg font-semibold text-white mb-5">Ringkasan Sesi</h3>
                <div class="space-y-3 text-sm text-slate-400">
                    <p><span class="font-semibold text-white">Order:</span> {{ $session->order?->order_number ?? 'N/A' }}</p>
                    <p><span class="font-semibold text-white">Klien:</span> {{ $session->order?->client_name ?? 'N/A' }}</p>
                    <p><span class="font-semibold text-white">Teknisi:</span> {{ $session->technician?->name ?? 'N/A' }}</p>
                    <p><span class="font-semibold text-white">Status sesi:</span> {{ $session->status }}</p>
                </div>

                <div id="verifyMessages" class="hidden rounded-xl border p-4 text-sm transition-all duration-300"></div>

                <div class="mt-6 grid gap-3">
                    <div class="rounded-2xl bg-slate-950 p-4 border border-slate-700">
                        <p class="text-xs uppercase text-slate-500">Total Parameter</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $summary['total'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-950 p-4 border border-slate-700 grid gap-2">
                        <div class="flex items-center justify-between text-slate-400 text-xs">
                            <span>Pass</span><span class="text-green-300">{{ $summary['pass'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400 text-xs">
                            <span>Fail</span><span class="text-red-300">{{ $summary['fail'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400 text-xs">
                            <span>Tidak pasti</span><span class="text-slate-300">{{ $summary['unknown'] }}</span>
                        </div>
                    </div>
                </div>

                @if($session->status === 'In-Progress')
                    <div class="mt-6">
                        <button id="submitForVerificationBtn" type="button" class="w-full rounded-2xl bg-accent-600 px-4 py-3 text-sm font-semibold text-white hover:bg-accent-500 transition focus:outline-none focus:ring-4 focus:ring-accent-500/30">
                            Kirim untuk Verifikasi
                        </button>
                    </div>
                @endif
            </aside>
        </section>

        <footer class="mt-12 pt-6 border-t border-slate-800 text-center text-xs text-slate-600">
            Sistem Manajemen Laboratorium LIMS Kemasan V1.0 | Departemen Food Safety | © 2026 Perusahaan Besar Indonesia Tbk.
        </footer>
    </main>
</div>

<div id="confirmationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4 py-8">
    <div class="w-full max-w-xl rounded-3xl bg-slate-900 border border-slate-800 p-7 shadow-2xl">
        <h2 class="text-xl font-semibold text-white">Konfirmasi Kirim Verifikasi</h2>
        <p class="mt-3 text-sm leading-6 text-slate-300">Apakah Anda yakin ingin mengunci data ini? Data tidak dapat diubah setelah dikirim ke Supervisor.</p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button id="cancelSubmitBtn" type="button" class="rounded-2xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm font-semibold text-slate-200 hover:border-slate-500 hover:text-white transition">
                Batal
            </button>
            <button id="confirmSubmitBtn" type="button" class="rounded-2xl bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-500 transition">
                Ya, kunci dan kirim
            </button>
        </div>
    </div>
</div>

<script>
    const reviewSessionId = '{{ $session->id }}';
    const reviewCsrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const reviewModal = document.getElementById('confirmationModal');
    const reviewMessages = document.getElementById('verifyMessages');
    const reviewSubmitBtn = document.getElementById('submitForVerificationBtn');
    const reviewConfirmBtn = document.getElementById('confirmSubmitBtn');
    const reviewCancelBtn = document.getElementById('cancelSubmitBtn');

    function toggleReviewModal(show) {
        if (!reviewModal) return;
        reviewModal.classList.toggle('hidden', !show);
    }

    if (reviewSubmitBtn) {
        reviewSubmitBtn.addEventListener('click', () => toggleReviewModal(true));
    }

    if (reviewCancelBtn) {
        reviewCancelBtn.addEventListener('click', () => toggleReviewModal(false));
    }

    if (reviewConfirmBtn) {
        reviewConfirmBtn.addEventListener('click', () => {
            reviewConfirmBtn.disabled = true;
            reviewConfirmBtn.textContent = 'Mengirim...';

            fetch(`/test-sessions/${reviewSessionId}/approve-reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': reviewCsrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'submit' })
            })
            .then(res => res.json())
            .then(data => {
                reviewConfirmBtn.disabled = false;
                reviewConfirmBtn.textContent = 'Ya, kunci dan kirim';
                toggleReviewModal(false);

                if (data.success) {
                    reviewMessages.className = 'rounded-xl border border-green-700 bg-green-950 p-4 text-sm text-green-200';
                    reviewMessages.innerHTML = `<strong>Berhasil:</strong> ${data.message ?? 'Sesi dikirim untuk verifikasi.'}`;
                    reviewMessages.classList.remove('hidden');
                    if (reviewSubmitBtn) reviewSubmitBtn.remove();
                } else {
                    reviewMessages.className = 'rounded-xl border border-red-700 bg-red-950 p-4 text-sm text-red-200';
                    reviewMessages.innerHTML = `<strong>Error:</strong> ${data.message || 'Gagal mengirim untuk verifikasi.'}`;
                    reviewMessages.classList.remove('hidden');
                }
            })
            .catch(() => {
                reviewConfirmBtn.disabled = false;
                reviewConfirmBtn.textContent = 'Ya, kunci dan kirim';
                toggleReviewModal(false);
                reviewMessages.className = 'rounded-xl border border-red-700 bg-red-950 p-4 text-sm text-red-200';
                reviewMessages.innerHTML = 'Terjadi kesalahan saat mengirim data. Silakan coba lagi.';
                reviewMessages.classList.remove('hidden');
            });
        });
    }
</script>
</body>
</html>
