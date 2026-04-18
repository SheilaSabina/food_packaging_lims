<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Staff Lab | LIMS Kemasan Food Safety Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md p-8 space-y-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl shadow-slate-950/40">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300 ring-1 ring-emerald-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M12 11v10m-7-6h14" />
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-semibold text-white">Staff Lab Login</h1>
                <p class="text-sm text-slate-400">Akses Teknisi dan Supervisor untuk menjalankan pengujian.</p>
            </div>
        </div>

        @if ($errors->has('locked'))
            <div class="flex items-start gap-3 rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-rose-100">
                <div class="mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M6.938 12.5a6.002 6.002 0 0011.124 0M12 3C8.134 3 5 6.134 5 10v2h14v-2c0-3.866-3.134-7-7-7z" />
                    </svg>
                </div>
                <div class="text-sm leading-6">
                    <p class="font-semibold">Akun Terkunci</p>
                    <p>{{ $errors->first('locked') }}</p>
                </div>
            </div>
        @elseif ($errors->any())
            <div class="rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-rose-100">
                <div class="flex items-center gap-2 text-sm font-semibold">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-500/20 text-rose-200">!</span>
                    <span>Login gagal</span>
                </div>
                <ul class="mt-3 space-y-1 text-sm text-rose-100">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full mt-2 rounded-2xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                <input id="password" name="password" type="password" required class="w-full mt-2 rounded-2xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20" />
            </div>

            <button type="submit" class="w-full rounded-2xl bg-emerald-500 px-5 py-3 text-base font-semibold text-slate-950 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">Masuk</button>
        </form>

        <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4 text-sm text-slate-400">
            <p>Gunakan akun teknisi atau supervisor untuk mengakses dashboard dan review pengujian laboratorium.</p>
        </div>

        <p class="text-center text-sm text-slate-500">Kembali ke <a class="text-emerald-400 hover:text-emerald-300" href="{{ url('/') }}">beranda</a>.</p>
    </div>
</body>
</html>
