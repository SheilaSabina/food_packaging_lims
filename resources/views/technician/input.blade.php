<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Input Hasil Pengujian | LIMS Kemasan Food Safety Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Menggunakan Teal/Cyan sebagai warna aksen sesuai Gambar 5
                        accent: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6', // Teal-500
                            600: '#0d9488', // Teal-600 (Warna tombol utama)
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
        /* Custom scrollbar untuk tema gelap */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; }
        ::-webkit-scrollbar-thumb:hover { background: #0d9488; }
    </style>
</head>
<body class="h-full antialiased text-slate-300">

<div class="flex h-full">
    @include('technician._sidebar', ['active' => 'input', 'session' => $session])

    <main class="flex-1 overflow-y-auto bg-slate-950 p-8 xl:p-12">
        <header class="mb-10 flex items-center justify-between pb-6 border-b border-slate-800">
            <div>
                <p class="text-sm text-slate-500">Dashboard > Order Pengujian > <span class="text-accent-500">Input Hasil</span></p>
                <h1 class="mt-2 text-3xl font-bold text-white">Pelaksanaan Uji Laboratorium</h1>
                <p class="mt-1 text-slate-400">
                    Masukkan nilai pengukuran untuk sampel: 
                    {{ $session->order->order_number }} ({{ $session->order->client_name }})
                </p>
            </div>
            <div class="flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900 px-4 py-2">
                <div class="h-2 w-2 rounded-full bg-accent-500 animate-pulse"></div>
                <span class="text-sm text-slate-300">Sesi ID: <span class="font-bold text-white">{{ $session->id }}</span></span>
            </div>
        </header>

        <div class="grid gap-8 xl:grid-cols-[2fr_1fr]">
            
            <section class="space-y-8">
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                        <h2 class="text-xl font-semibold text-white">Detail Perangkat & Sesi</h2>
                        <span id="sessionStatus" class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-400">Memuat...</span>
                    </div>
                    <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                        <div class="space-y-1">
                            <p class="text-xs uppercase text-slate-500">Klien</p>
                            <p class="font-medium text-white">{{ $session->order->client_name }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs uppercase text-slate-500">Tipe Produk</p>
                            <p class="font-medium text-white">{{ ucfirst($session->order->product_type) }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs uppercase text-slate-500">ID Alat Utama</p>
                            <p class="font-medium text-accent-500">{{ $session->equipment_id }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs uppercase text-slate-500">Status Kalibrasi</p>
                            <p id="equipmentInfo" class="text-sm text-white">Memuat...</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl">
                    <h2 class="text-xl font-semibold text-white mb-6">Input Nilai Pengukuran</h2>
                    
                    <form id="measurementForm" class="space-y-6">
                        @csrf
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="test_parameter_id" class="text-sm font-medium text-slate-400">Parameter Pengujian</label>
                                <select name="test_parameter_id" id="testParameter" required 
                                    class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-accent-500 focus:ring-accent-500 transition">
                                    <option value="" disabled selected>-- Pilih Parameter --</option>
                                    @foreach($parameters_not_input as $param)
                                        <option value="{{ $param->id }}">
                                            {{ $param->name }} ({{ $param->unit }}) - {{ $param->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="measured_value" class="text-sm font-medium text-slate-400">Nilai Pengukuran</label>
                                <div class="relative">
                                    <input type="number" step="any" name="measured_value" id="measuredValue" required placeholder="0.00"
                                        class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-accent-500 focus:ring-accent-500 transition">
                                    <span class="absolute right-4 top-3 text-slate-500" id="paramUnit"></span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="notes" class="text-sm font-medium text-slate-400">Catatan Kondisi (Opsional)</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Contoh: Pengukuran dilakukan pada suhu simulan 40°C"
                                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-accent-500 focus:ring-accent-500 transition"></textarea>
                        </div>

                        <div id="formMessages" class="hidden rounded-xl p-4 text-sm transition-all duration-300"></div>

                        <button type="submit" id="submitBtn"
                            class="flex w-full items-center justify-center gap-3 rounded-xl bg-accent-600 px-6 py-3.5 font-semibold text-white hover:bg-accent-700 focus:ring-4 focus:ring-accent-500/50 transition duration-150 disabled:opacity-60 disabled:cursor-not-allowed">
                            <span id="btnText">Simpan & Bandingkan Otomatis (US-2.5)</span>
                            <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </form>
                </div>
            </section>

            <aside class="space-y-8">
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl">
                    <h3 class="text-lg font-semibold text-white mb-5">Ringkasan Hasil Uji</h3>
                    
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="rounded-xl bg-green-950 border border-green-800 p-4">
                            <p class="text-xs text-green-300">LULUS</p>
                            <p id="summaryPass" class="text-xl font-bold text-white">0</p>
                        </div>
                        <div class="rounded-xl bg-red-950 border border-red-800 p-4">
                            <p class="text-xs text-red-300">GAGAL</p>
                            <p id="summaryFail" class="text-xl font-bold text-white">0</p>
                        </div>
                        <div class="rounded-xl bg-slate-800 border border-slate-700 p-4">
                            <p class="text-xs text-slate-400">TIDAK PASTI</p>
                            <p id="summaryUnknown" class="text-xl font-bold text-white">0</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl">
                    <h3 class="text-lg font-semibold text-white mb-5">Riwayat Input Sesi Ini</h3>
                    <div id="historyContainer" class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                        @if($inputted_results->isEmpty())
                            <div class="text-center py-6 rounded-xl bg-slate-800/50 border border-dashed border-slate-700">
                                <p class="text-sm text-slate-500">Belum ada data yang diinput.</p>
                            </div>
                        @else
                            @foreach($inputted_results as $result)
                                <div class="rounded-xl bg-slate-800 p-4 border border-slate-700 transition hover:border-accent-700">
                                    <div class="flex items-center justify-between">
                                        <p class="font-medium text-white text-sm">{{ $result->parameter->name }}</p>
                                        @if($result->result_status === 'PASS')
                                            <span class="rounded-full bg-green-950 px-2 py-0.5 text-xs text-green-300 border border-green-800">✅ LULUS</span>
                                        @elseif($result->result_status === 'FAIL')
                                            <span class="rounded-full bg-red-950 px-2 py-0.5 text-xs text-red-300 border border-red-800">❌ GAGAL</span>
                                        @else
                                            <span class="rounded-full bg-slate-700 px-2 py-0.5 text-xs text-slate-300 border border-slate-600">⚠️ TIDAK PASTI</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-lg font-semibold text-accent-500">{{ $result->measured_value }} <span class="text-sm font-normal text-slate-400">{{ $result->unit }}</span></p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $result->created_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl">
                    <h3 class="text-lg font-semibold text-white mb-5">Upload Bukti Foto</h3>
                    <form id="evidenceForm" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-sm text-slate-400">Pilih Hasil Uji</label>
                            <select id="evidenceResultSelect" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:ring-accent-500 focus:border-accent-500 transition">
                                <option value="" disabled selected>-- Pilih Hasil Uji --</option>
                                @foreach($inputted_results as $result)
                                    <option value="{{ $result->id }}">{{ $result->parameter->name }} - {{ $result->measured_value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-slate-400">File Foto (Max 10MB)</label>
                            <input type="file" name="file" accept="image/*" required class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-accent-500 hover:file:bg-slate-700 cursor-pointer">
                        </div>
                        <button type="submit" id="uploadEvidenceBtn" class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600 transition disabled:opacity-60 disabled:cursor-not-allowed">
                            <span id="uploadBtnText">Unggah Bukti</span>
                            <svg id="uploadLoadingSpinner" class="hidden h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </aside>
        </div>

        <footer class="mt-12 pt-6 border-t border-slate-800 text-center text-xs text-slate-600">
            Sistem Manajemen Laboratorium LIMS Kemasan V1.0 | Departemen Food Safety | © 2026 Perusahaan Besar Indonesia Tbk.
        </footer>
    </main>
</div>

<script>
    const sessionId = '{{ $session->id }}';
    const csrfToken = '{{ csrf_token() }}';

    const sessionStatusBadge = document.getElementById('sessionStatus');
    const equipmentInfoContainer = document.getElementById('equipmentInfo');

    const measuredInput = document.getElementById('measuredValue');

    measuredInput.addEventListener('input', function () {
        const value = this.value;

        if (value.trim() === '' || isNaN(Number(value))) {
            this.classList.add('border-red-500');
        } else {
            this.classList.remove('border-red-500');
        }
    });


    function disableForm(message = "Form tidak dapat digunakan") {
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const measuredInput = document.getElementById('measuredValue');
        const parameterSelect = document.getElementById('testParameter');
        const notes = document.getElementById('notes');

        submitBtn.disabled = true;
        btnText.textContent = message;

        measuredInput.disabled = true;
        parameterSelect.disabled = true;
        notes.disabled = true;
        document.getElementById('measurementForm').classList.add('opacity-60', 'pointer-events-none');
    }

    // =============================
    // LOAD DATA SESSION
    // =============================
    function populateSessionHeader(data) {
        const status = data.status || 'Draft';
        sessionStatusBadge.textContent = status;

        sessionStatusBadge.className = `rounded-full px-3 py-1 text-xs font-medium border ${
            status === 'Draft' ? 'bg-slate-800 text-slate-400 border-slate-700' :
            status === 'In-Progress' ? 'bg-blue-950 text-blue-300 border-blue-800' :
            status === 'Verified' ? 'bg-green-950 text-green-300 border-green-800' :
            'bg-slate-700 text-slate-300 border-slate-600'
        }`;

        if (data.equipment_is_calibrated) {
            const expires = new Date(data.equipment_calibration_expires_at);
            const now = new Date();

            if (expires < now) {
                equipmentInfoContainer.innerHTML = '❌ <span class="text-red-400 font-medium">Kalibrasi Kadaluarsa</span>';
                disableForm();
            } else {
                equipmentInfoContainer.innerHTML = '✅ <span class="text-green-400 font-medium">Terkalibrasi (OK)</span>';
            }
        } else {
            equipmentInfoContainer.innerHTML = '❌ <span class="text-red-400 font-medium">Belum Kalibrasi</span>';
            disableForm();
        }

        // CEK STATUS SESSION
        if (data.status === 'Verified') {
            disableForm("Sesi sudah diverifikasi");
        }
    }

    fetch(`/test-sessions/${sessionId}/data`)
        .then(res => res.json())
        .then(data => {
            console.log(data); // DEBUG
            
        if (data.success && data.session) {
            populateSessionHeader(data.session);
        }

        })
        .catch(err => {
            console.error("ERROR FETCH SESSION:", err);
        });

        fetch(`/test-sessions/${sessionId}/summary`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.summary) {
                updateSummary(data.summary);
            }
        });

    // =============================
    // UPDATE UNIT
    // =============================
    document.getElementById('testParameter').addEventListener('change', function(e) {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const unit = selectedOption.text.match(/\(([^)]+)\)/);
        document.getElementById('paramUnit').textContent = unit ? unit[1] : '';
    });

    // =============================
    // SUBMIT FORM (US-2.4 & 2.5)
    // =============================
    document.getElementById('measurementForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const value = measuredInput.value;

        // VALIDASI TAMBAHAN
        if (value.trim() === '' || isNaN(Number(value))) {
            alert("Nilai harus berupa angka valid!");
            return;
        }

        const formData = new FormData(this);

        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const spinner = document.getElementById('loadingSpinner');
        const msgContainer = document.getElementById('formMessages');

        submitBtn.disabled = true;
        btnText.textContent = "Menghitung...";
        spinner.classList.remove('hidden');
        msgContainer.classList.add('hidden');

        fetch(`/test-sessions/${sessionId}/input-numeric`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            btnText.textContent = "Simpan & Bandingkan Otomatis";
            spinner.classList.add('hidden');
            msgContainer.classList.remove('hidden');

            if (data.success && data.data) {

                if (data.status_summary) {
                    updateSummary(data.status_summary);
                }
                // HIGHLIGHT FIELD (FIX US-2.5)
                if (data.data.result_status === 'FAIL') {
                    measuredInput.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                } else {
                    measuredInput.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                }

                msgContainer.className = "rounded-xl p-4 text-sm bg-green-950 border border-green-800 text-green-200";
                const messages = data.messages || [];
                msgContainer.innerHTML = `<strong>Berhasil!</strong><br>${messages.join('<br>')}`;
                // TAMBAHAN ALERT CERDAS
                if (data.data.result_status === 'FAIL') {
                    msgContainer.innerHTML += "<br><span class='text-red-400 font-semibold'>⚠️ Melebihi ambang batas keamanan!</span>";
                }

                addToHistory(data.data);

                // RESET FORM
                const select = document.getElementById('testParameter');

                // hapus parameter yang sudah dipilih
                select.remove(select.selectedIndex);

                // cek kalau masih ada parameter
                if (select.options.length > 1) {
                    select.selectedIndex = 0;
                } else {
                    select.disabled = true;

                    msgContainer.innerHTML += `
                        <br><span class='text-yellow-400 font-semibold'>
                        ⚠️ Semua parameter sudah diinput
                        </span>
                    `;
                }

                // reset unit
                document.getElementById('paramUnit').textContent = '';

                measuredInput.value = '';
                document.getElementById('notes').value = '';

            } else {
                msgContainer.className = "rounded-xl p-4 text-sm bg-red-950 border border-red-800 text-red-200";
                msgContainer.innerHTML = `<strong>Error:</strong> ${data.message}`;
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            btnText.textContent = "Simpan & Bandingkan Otomatis";
            spinner.classList.add('hidden');

            msgContainer.className = "rounded-xl p-4 text-sm bg-red-950 border border-red-800 text-red-200";
            msgContainer.innerHTML = "Terjadi kesalahan koneksi ke server.";
            msgContainer.classList.remove('hidden');
        });
    });

    // =============================
    // UPLOAD BUKTI FOTO ASYNC
    // =============================
    document.getElementById('evidenceForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const resultSelect = document.getElementById('evidenceResultSelect');
        const resultId = resultSelect.value;
        const fileInput = this.querySelector('input[name="file"]');
        const formMessages = document.getElementById('formMessages');
        const uploadBtn = document.getElementById('uploadEvidenceBtn');
        const uploadBtnText = document.getElementById('uploadBtnText');
        const uploadSpinner = document.getElementById('uploadLoadingSpinner');

        if (!resultId) {
            formMessages.className = "rounded-xl p-4 text-sm bg-red-950 border border-red-800 text-red-200";
            formMessages.innerHTML = "Pilih hasil uji yang akan diunggah terlebih dahulu.";
            formMessages.classList.remove('hidden');
            return;
        }

        if (!fileInput.files.length) {
            formMessages.className = "rounded-xl p-4 text-sm bg-red-950 border border-red-800 text-red-200";
            formMessages.innerHTML = "Silakan pilih file bukti sebelum mengunggah.";
            formMessages.classList.remove('hidden');
            return;
        }

        const uploadFormData = new FormData(this);
        uploadFormData.append('test_result_id', resultId);

        uploadBtn.disabled = true;
        uploadBtnText.textContent = 'Mengunggah...';
        uploadSpinner.classList.remove('hidden');
        formMessages.classList.add('hidden');

        fetch(`/test-results/${resultId}/upload-evidence`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: uploadFormData
        })
        .then(res => res.json())
        .then(data => {
            uploadBtn.disabled = false;
            uploadBtnText.textContent = 'Unggah Bukti';
            uploadSpinner.classList.add('hidden');

            if (data.success) {
                formMessages.className = "rounded-xl p-4 text-sm bg-green-950 border border-green-800 text-green-200";
                formMessages.innerHTML = `Bukti berhasil diunggah.`;
                if (data.message) {
                    formMessages.innerHTML += `<br>${data.message}`;
                }
                this.reset();
                resultSelect.selectedIndex = 0;
            } else {
                const errorText = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Unggah bukti gagal.');
                formMessages.className = "rounded-xl p-4 text-sm bg-red-950 border border-red-800 text-red-200";
                formMessages.innerHTML = `<strong>Error:</strong> ${errorText}`;
            }

            formMessages.classList.remove('hidden');
        })
        .catch(err => {
            uploadBtn.disabled = false;
            uploadBtnText.textContent = 'Unggah Bukti';
            uploadSpinner.classList.add('hidden');

            formMessages.className = "rounded-xl p-4 text-sm bg-red-950 border border-red-800 text-red-200";
            formMessages.innerHTML = "Terjadi kesalahan koneksi saat mengunggah bukti.";
            formMessages.classList.remove('hidden');
        });
    });

    // =============================
    // HISTORY FIXED (NO BUG)
    // =============================
    function addToHistory(result) {
        const container = document.getElementById('historyContainer');
        
        if (container.querySelector('.text-center')) {
            container.innerHTML = '';
        }

        const badgeClass = result.result_status === 'PASS'
            ? 'bg-green-500/10 text-green-500 border-green-500/20'
            : result.result_status === 'FAIL'
            ? 'bg-red-500/10 text-red-500 border-red-500/20'
            : 'bg-slate-800 text-slate-400 border-slate-700';

        const badgeText = result.result_status === 'PASS' ? '✅ LULUS' : 
                        result.result_status === 'FAIL' ? '❌ GAGAL' : '⚠️ TIDAK PASTI';

        const html = `
            <div class="rounded-2xl bg-slate-800 p-4 border border-slate-700 transition hover:border-accent-700 animate-fade-in-up">
                <div class="flex items-center justify-between">
                    <p class="font-medium text-white text-sm">${result.parameter.name}</p>
                    <span class="rounded-full ${badgeClass} px-2 py-0.5 text-[10px] font-bold border">${badgeText}</span>
                </div>
                <p class="mt-1 text-xl font-bold text-accent-500">
                    ${result.measured_value} 
                    <span class="text-sm font-normal text-slate-500">${result.unit}</span>
                </p>
            </div>
        `;

        container.insertAdjacentHTML('afterbegin', html);
    }

    function updateSummary(summary) {
    document.getElementById('summaryPass').textContent = summary.pass ?? 0;
    document.getElementById('summaryFail').textContent = summary.fail ?? 0;
    document.getElementById('summaryUnknown').textContent = summary.unknown ?? 0;
}
</script>

<style>
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 0.4s ease-out; }
</style>

</body>
</html>