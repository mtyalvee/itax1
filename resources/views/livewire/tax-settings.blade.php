@php
    $keyIndices = [];
    foreach ($settings as $index => $setting) {
        $keyIndices[$setting['key']] = $index;
    }
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-slate-900 border border-slate-800 p-6 rounded-2xl gap-4 shadow-lg">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight font-sans">Global Tax System Settings</h2>
            <p class="text-xs text-slate-400 mt-1 font-sans">Adjust global PAYE, USC, and PRSI rates, bands, and computational thresholds</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="$dispatch('set-tab', 'dashboard')" class="py-2 px-4 active:scale-95 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 font-sans" style="background-color: #ffe4e6 !important; color: #e11d48 !important; border: 1px solid #fecdd3 !important;">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #e11d48 !important;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="font-bold" style="color: #e11d48 !important;">Back to Dashboard</span>
            </button>
            <button wire:click="save" class="py-2.5 px-5 bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2 font-sans">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span>Save All Changes</span>
            </button>
        </div>
    </div>

    <!-- Feedback Message -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-950/50 border border-emerald-800 text-emerald-300 rounded-xl text-xs flex items-center gap-2 font-sans">
            <svg class="h-4 w-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="p-4 bg-rose-950/50 border border-rose-800 text-rose-300 rounded-xl text-xs font-sans space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="h-4 w-4 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Please fix the following validation errors:</span>
            </div>
            <ul class="list-disc pl-6 mt-1 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Groups -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- PAYE (Income Tax) Settings -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg flex flex-col gap-5">
            <div class="border-b border-slate-850 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2 font-sans">
                    <span class="p-1.5 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span>1. PAYE (Pay As You Earn)</span>
                </h3>
                <p class="text-slate-450 text-[10px] mt-1.5">Configure standard rates, annual tax credits, and cutoffs</p>
            </div>

            <div class="space-y-4">
                @foreach($settings as $index => $setting)
                    @if($setting['category'] === 'paye')
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 mb-1.5 font-sans">{{ $setting['display_name'] }}</label>
                            <div class="relative rounded-lg shadow-sm">
                                <input type="number" step="any" wire:model="settings.{{ $index }}.value" class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-3 pr-8 py-2 text-slate-200 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-slate-500 text-[10px] font-mono">
                                        @if($setting['type'] === 'percentage') % @elseif($setting['type'] === 'amount') € @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- USC (Universal Social Charge) Settings -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg flex flex-col gap-5">
            <div class="border-b border-slate-850 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2 font-sans">
                    <span class="p-1.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </span>
                    <span>2. Universal Social Charge (USC)</span>
                </h3>
                <p class="text-slate-450 text-[10px] mt-1.5">Manage progressive slabs, limits, and rates for USC bands</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-slate-300 font-sans text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] font-semibold text-slate-450 font-mono uppercase">
                            <th class="py-2.5 px-2 text-center">Band</th>
                            <th class="py-2.5 px-2">From</th>
                            <th class="py-2.5 px-2">To</th>
                            <th class="py-2.5 px-2">%age</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850 font-mono">
                        <!-- Band 1 -->
                        <tr class="hover:bg-slate-950/20">
                            <td class="py-3 px-2 text-center font-bold text-slate-200">1</td>
                            <td class="py-3 px-2 text-slate-400">-</td>
                            <td class="py-1 px-1">
                                <input type="number" step="any" wire:model="settings.{{ $keyIndices['usc_band_1_limit'] }}.value" class="w-28 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" step="any" wire:model="settings.{{ $keyIndices['usc_band_1_rate'] }}.value" class="w-20 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </td>
                        </tr>
                        <!-- Band 2 -->
                        <tr class="hover:bg-slate-950/20">
                            <td class="py-3 px-2 text-center font-bold text-slate-200">2</td>
                            <td class="py-3 px-2 text-slate-450">
                                {{ number_format(floatval($settings[$keyIndices['usc_band_1_limit']]['value'] ?? 0) + 1, 2) }}
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" step="any" wire:model="settings.{{ $keyIndices['usc_band_2_limit'] }}.value" class="w-28 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" step="any" wire:model="settings.{{ $keyIndices['usc_band_2_rate'] }}.value" class="w-20 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </td>
                        </tr>
                        <!-- Band 3 -->
                        <tr class="hover:bg-slate-950/20">
                            <td class="py-3 px-2 text-center font-bold text-slate-200">3</td>
                            <td class="py-3 px-2 text-slate-450">
                                {{ number_format(floatval($settings[$keyIndices['usc_band_2_limit']]['value'] ?? 0) + 1, 2) }}
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" step="any" wire:model="settings.{{ $keyIndices['usc_band_3_limit'] }}.value" class="w-28 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" step="any" wire:model="settings.{{ $keyIndices['usc_band_3_rate'] }}.value" class="w-20 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </td>
                        </tr>
                        <!-- Band 4 -->
                        <tr class="hover:bg-slate-950/20">
                            <td class="py-3 px-2 text-center font-bold text-slate-200">4</td>
                            <td class="py-3 px-2 text-slate-450">
                                {{ number_format(floatval($settings[$keyIndices['usc_band_3_limit']]['value'] ?? 0), 2) }}
                            </td>
                            <td class="py-3 px-2 text-slate-400">
                                -
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" step="any" wire:model="settings.{{ $keyIndices['usc_band_4_rate'] }}.value" class="w-20 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PRSI (Social Insurance) Settings -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg flex flex-col gap-5">
            <div class="border-b border-slate-850 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2 font-sans">
                    <span class="p-1.5 bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                    <span>3. PRSI (Social Insurance)</span>
                </h3>
                <p class="text-slate-450 text-[10px] mt-1.5">Taper thresholds, maximum weekly credits, and employer rates</p>
            </div>

            <div class="space-y-4">
                @foreach($settings as $index => $setting)
                    @if($setting['category'] === 'prsi')
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 mb-1.5 font-sans">{{ $setting['display_name'] }}</label>
                            <div class="relative rounded-lg shadow-sm">
                                <input type="number" step="any" wire:model="settings.{{ $index }}.value" class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-3 pr-8 py-2 text-slate-200 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-slate-500 text-[10px] font-mono">
                                        @if($setting['type'] === 'percentage') % @elseif($setting['type'] === 'amount') € @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

    </div>
</div>
