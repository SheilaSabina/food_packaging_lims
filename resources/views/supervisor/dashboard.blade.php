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

        @if(session('success'))
    <div class="mb-6 rounded-xl bg-green-950 border border-green-800 p-4 text-green-300 animate-bounce">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    </div>
@endif

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
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Klien</th>
                                <th class="px-4 py-3">Teknisi</th>
                                <th class="px-4 py-3">Tanggal Kirim</th>
                                <th class="px-4 py-3">Ringkasan Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                <tr class="border-b border-slate-800 hover:bg-slate-900/60">
                                    <td class="px-4 py-4 font-semibold text-white">{{ $session->id }}</td>
                                    <td class="px-4 py-4">{{ $session->order?->order_number ?? 'N/A' }}</td>
                                    <td class="px-4 py-4">{{ $session->order?->client_name ?? 'Tidak tersedia' }}</td>
                                    <td class="px-4 py-4">{{ $session->technician?->name ?? 'Tidak tersedia' }}</td>
                                    <td class="px-4 py-4">{{ $session->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-4">
                                        @php
                                            $summary = $session->getStatusSummary();
                                        @endphp
                                        <div class="flex gap-2">
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold bg-green-950 text-green-300 border border-green-800">
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                                                PASS: {{ $summary['pass'] }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold bg-red-950 text-red-300 border border-red-800">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                                FAIL: {{ $summary['fail'] }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold bg-yellow-950 text-yellow-300 border border-yellow-800">
                                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-400"></span>
                                                INCONCLUSIVE: {{ $summary['unknown'] }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex gap-2">
                                            <button onclick="openApprovalModal({{ $session->id }}, 'approve')"
                                                    class="rounded-xl bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-500 transition">
                                                Approve
                                            </button>
                                            <button onclick="openApprovalModal({{ $session->id }}, 'reject')"
                                                    class="rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-500 transition">
                                                Reject
                                            </button>
                                        </div>
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

<!-- Modal untuk Approval/Reject -->
<div id="approvalModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 id="modalTitle" class="text-lg font-semibold text-white">Konfirmasi Aksi</h3>
                <button onclick="closeApprovalModal()" class="text-slate-400 hover:text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="approvalForm" method="POST">
                @csrf
                <input type="hidden" id="sessionId" name="session_id">
                <input type="hidden" id="action" name="action">

                <div id="rejectReasonSection" class="mb-4 hidden">
                    <label for="rejection_reason" class="block text-sm font-medium text-slate-300 mb-2">
                        Alasan Penolakan <span class="text-red-400">*</span>
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3"
                              class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-400 focus:border-amber-500 focus:outline-none"
                              placeholder="Jelaskan alasan penolakan hasil pengujian..."></textarea>
                    <p class="mt-1 text-xs text-slate-400">Alasan penolakan wajib diisi untuk aksi Reject.</p>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeApprovalModal()"
                            class="flex-1 rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm text-slate-300 hover:border-slate-600 hover:bg-slate-700 transition">
                        Batal
                    </button>
                    <button type="submit" id="confirmButton"
                            class="flex-1 rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500 transition">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal functions
function openApprovalModal(sessionId, action) {
    document.getElementById('sessionId').value = sessionId;
    document.getElementById('action').value = action;
    document.getElementById('approvalForm').action = `/test-sessions/${sessionId}/approve-reject`;

    const modal = document.getElementById('approvalModal');
    const modalTitle = document.getElementById('modalTitle');
    const confirmButton = document.getElementById('confirmButton');
    const rejectReasonSection = document.getElementById('rejectReasonSection');

    if (action === 'approve') {
        modalTitle.textContent = 'Approve Hasil Pengujian';
        confirmButton.textContent = 'Approve';
        confirmButton.className = 'flex-1 rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500 transition';
        rejectReasonSection.classList.add('hidden');
    } else {
        modalTitle.textContent = 'Reject Hasil Pengujian';
        confirmButton.textContent = 'Reject';
        confirmButton.className = 'flex-1 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 transition';
        rejectReasonSection.classList.remove('hidden');
    }

    modal.classList.remove('hidden');
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('rejection_reason').value = '';
}

// Form validation
document.getElementById('approvalForm').addEventListener('submit', function(e) {
    const action = document.getElementById('action').value;
    const rejectionReason = document.getElementById('rejection_reason').value;

    if (action === 'reject' && !rejectionReason.trim()) {
        e.preventDefault();
        alert('Alasan penolakan wajib diisi!');
        return false;
    }

    // Show loading state
    const confirmButton = document.getElementById('confirmButton');
    confirmButton.disabled = true;
    confirmButton.textContent = 'Memproses...';
});

// Close modal when clicking outside
document.getElementById('approvalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApprovalModal();
    }
});
</script>
</body>
</html>
