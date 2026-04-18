@php
    $current = $active ?? '';
    $hasSession = isset($session) && $session instanceof \App\Models\TestSession;
    $sidebarLinkClass = fn($isActive) => $isActive
        ? 'flex items-center gap-3 rounded-xl bg-slate-800 px-3 py-2.5 text-white transition'
        : 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-slate-400 hover:bg-slate-800 hover:text-white transition';
@endphp

<aside class="flex w-64 flex-col border-r border-slate-800 bg-slate-900 px-5 py-6">
    <div class="flex items-center gap-3 px-2">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-600 text-white shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.26m4.5-5.26v5.26m-5.403 10.34C10.122 19.16 11.021 20 12 20s1.878-.84 2.153-1.556m-9.557-4.433c-1.332-.122-2.436-1.21-2.436-2.567V7.22c0-1.357 1.104-2.445 2.436-2.567m14.708 0c1.332.122 2.436 1.21 2.436 2.567v3.226c0 1.357-1.104 2.445-2.436 2.567m-14.708 0a1.516 1.516 0 000 3.034m14.708 0a1.516 1.516 0 000 3.034M6.126 8.25h11.748" />
            </svg>
        </div>
        <div class="flex flex-col">
            <span class="text-xl font-bold text-white">LIMS Kemasan</span>
            <span class="text-xs text-slate-400">FOOD SAFETY LAB</span>
        </div>
    </div>

    <nav class="mt-10 flex-1 space-y-2">
        <p class="px-3 text-xs uppercase text-slate-500">Menu Utama</p>
        <a href="{{ route('technician.dashboard') }}" class="{{ $sidebarLinkClass($current === 'dashboard') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
            Dashboard
        </a>
        <a href="{{ $hasSession ? route('test.input', ['session' => $session->id]) : '#' }}" class="{{ $sidebarLinkClass($current === 'input') }} {{ $hasSession ? '' : 'cursor-not-allowed opacity-70' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM4.125 6.75H6.75m0 0v12.75m0-12.75H3.375c-.621 0-1.125.504-1.125 1.125V19.5c0 .621.504 1.125 1.125 1.125h13.5c.621 0 1.125-.504 1.125-1.125V14.25" /></svg>
            Pelaksanaan Uji
        </a>
        <a href="{{ $hasSession ? route('test.review', ['session' => $session->id]) : '#' }}" class="{{ $sidebarLinkClass($current === 'review') }} {{ $hasSession ? '' : 'cursor-not-allowed opacity-70' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-7.5A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15m-6-3h6m-6 4h3" /></svg>
            Verifikasi Mandiri
        </a>
    </nav>

    <div class="mt-auto border-t border-slate-800 pt-5">
        <div class="flex items-center gap-3 rounded-2xl bg-slate-800 p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-700 font-semibold text-accent-500">
                {{ isset($session) ? strtoupper(substr($session->technician->name, 0, 1)) : 'T' }}
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-white">{{ isset($session) ? $session->technician->name : 'Teknisi Lab' }}</span>
                <span class="text-xs text-slate-400">Teknisi Lab</span>
            </div>
        </div>
    </div>
</aside>
