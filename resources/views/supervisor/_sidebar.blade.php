@php
    $current = $active ?? '';
    $hasSession = isset($session) && $session instanceof \App\Models\TestSession;
    $sidebarLinkClass = fn($isActive) => $isActive
        ? 'flex items-center gap-3 rounded-xl bg-slate-800 px-3 py-2.5 text-white transition'
        : 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-slate-400 hover:bg-slate-800 hover:text-white transition';
@endphp

<aside class="flex w-64 flex-col border-r border-slate-800 bg-slate-900 px-5 py-6">
    <div class="flex items-center gap-3 px-2">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-600 text-white shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
        </div>
        <div class="flex flex-col">
            <span class="text-xl font-bold text-white">LIMS Supervisor</span>
            <span class="text-xs text-slate-400">QUALITY CONTROL</span>
        </div>
    </div>

    <nav class="mt-10 flex-1 space-y-2">
        <p class="px-3 text-xs uppercase text-slate-500">Menu Supervisor</p>
        <a href="{{ route('supervisor.dashboard') }}" class="{{ $sidebarLinkClass($current === 'dashboard') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
            Dashboard Verifikasi
        </a>
        <a href="#" class="{{ $sidebarLinkClass($current === 'reports') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            Laporan Hasil
        </a>
        <a href="#" class="{{ $sidebarLinkClass($current === 'analytics') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
            Analytics
        </a>
    </nav>

    <div class="mt-auto border-t border-slate-800 pt-5">
        <div class="flex items-center gap-3 rounded-2xl bg-slate-800 p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-700 font-semibold text-amber-500">
                S
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-white">Supervisor</span>
                <span class="text-xs text-slate-400">Quality Control</span>
            </div>
        </div>
    </div>
</aside>
