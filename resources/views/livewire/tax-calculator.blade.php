<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-slate-900 border border-slate-800 p-6 rounded-2xl gap-4 shadow-lg">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight font-sans">Tax Computation & Payroll Register</h2>
            <p class="text-xs text-slate-400 mt-1 font-sans">Audit, sort, filter, and run payroll computations across all employee slips</p>
        </div>
        <button wire:click="startNewComputation" class="py-2.5 px-5 bg-emerald-100 hover:bg-emerald-200 active:scale-95 text-emerald-800 border border-emerald-300/50 font-bold rounded-xl text-sm transition-all flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Process New Computation</span>
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-950/50 border border-emerald-800 text-emerald-300 rounded-xl text-xs flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-950/50 border border-rose-800 text-rose-300 rounded-xl text-xs flex items-center gap-2">
            <svg class="h-4 w-4 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- The Grid Sheet Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto max-h-[75vh] relative">
            <table class="w-full text-left border-collapse min-w-[1300px]">
                <thead class="sticky top-0 z-20 bg-slate-950 shadow-md">
                    <!-- Column Headers (Sortable) -->
                    <tr class="bg-slate-950 border-b border-slate-850 text-slate-400 text-xs uppercase font-extrabold tracking-wider font-mono">
                        <th class="px-4 py-3 text-center min-w-[170px]">Actions</th>
                        <th wire:click="sortBy('name')" class="px-4 py-3 cursor-pointer hover:text-white transition-colors">
                            Employee Name
                            @if($sortField === 'name')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('department')" class="px-4 py-3 cursor-pointer hover:text-white transition-colors">
                            Department
                            @if($sortField === 'department')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('pps_number')" class="px-4 py-3 cursor-pointer hover:text-white transition-colors">
                            PPSN
                            @if($sortField === 'pps_number')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3">Week No</th>
                        <th wire:click="sortBy('period_start')" class="px-4 py-3 cursor-pointer hover:text-white transition-colors">
                            Period From
                            @if($sortField === 'period_start')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('period_end')" class="px-4 py-3 cursor-pointer hover:text-white transition-colors">
                            Period To
                            @if($sortField === 'period_end')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('gross_pay')" class="px-4 py-3 text-right cursor-pointer hover:text-white transition-colors">
                            Gross Pay
                            @if($sortField === 'gross_pay')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('paye')" class="px-4 py-3 text-right cursor-pointer hover:text-white transition-colors">
                            PAYE
                            @if($sortField === 'paye')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('usc')" class="px-4 py-3 text-right cursor-pointer hover:text-white transition-colors">
                            USC
                            @if($sortField === 'usc')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('prsi')" class="px-4 py-3 text-right cursor-pointer hover:text-white transition-colors">
                            EE PRSI
                            @if($sortField === 'prsi')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('employer_prsi')" class="px-4 py-3 text-right cursor-pointer hover:text-white transition-colors">
                            ER PRSI
                            @if($sortField === 'employer_prsi')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-right">Deductions</th>
                        <th class="px-4 py-3 text-right">Pensions</th>
                        <th wire:click="sortBy('net_pay')" class="px-4 py-3 text-right cursor-pointer hover:text-white transition-colors">
                            Net Pay
                            @if($sortField === 'net_pay')
                                <span class="ml-1 text-emerald-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                    </tr>
                    
                    <!-- Filtering Inputs Row -->
                    <tr class="bg-slate-950/40 border-b border-slate-850">
                        <td class="p-2"></td> <!-- Actions column spacer -->
                        <td class="p-2"><input wire:model.live="searchName" type="text" placeholder="Filter Name..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500"></td>
                        <td class="p-2"><input wire:model.live="searchDepartment" type="text" placeholder="Filter Dept..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500"></td>
                        <td class="p-2"><input wire:model.live="searchPpsn" type="text" placeholder="Filter PPSN..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500"></td>
                        <td class="p-2"></td> <!-- Week No -->
                        <td class="p-2"><input wire:model.live="searchPeriodFrom" type="text" placeholder="YYYY-MM-DD..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500"></td>
                        <td class="p-2"><input wire:model.live="searchPeriodTo" type="text" placeholder="YYYY-MM-DD..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500"></td>
                        <td class="p-2"><input wire:model.live="searchGross" type="text" placeholder="Gross..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-right"></td>
                        <td class="p-2"><input wire:model.live="searchPaye" type="text" placeholder="PAYE..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-right"></td>
                        <td class="p-2"><input wire:model.live="searchUsc" type="text" placeholder="USC..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-right"></td>
                        <td class="p-2"><input wire:model.live="searchEePrsi" type="text" placeholder="EE PRSI..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-right"></td>
                        <td class="p-2"><input wire:model.live="searchErPrsi" type="text" placeholder="ER PRSI..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-right"></td>
                        <td class="p-2"><input wire:model.live="searchDeductions" type="text" placeholder="Deduct..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-right"></td>
                        <td class="p-2"></td> <!-- Pensions -->
                        <td class="p-2"><input wire:model.live="searchNet" type="text" placeholder="Net..." class="w-full bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-right"></td>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850 text-xs font-mono">
                    @forelse($payslips as $ps)
                        @php
                            $deductions = $ps->paye + $ps->usc + $ps->prsi;
                            $weekNo = date('W', strtotime($ps->period_end));
                        @endphp
                        <tr class="odd:bg-slate-900 even:bg-slate-950/40 hover:bg-slate-800/30 text-slate-300 transition-colors">
                            <td class="px-4 py-3 text-center whitespace-nowrap space-x-1 font-sans">
                                <a href="{{ route('pdf.payslip', $ps->id) }}" target="_blank" class="inline-flex p-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 border border-blue-500/20 rounded transition-all align-middle" title="Download PDF">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                                <button wire:click="editPayslip({{ $ps->id }})" class="px-2.5 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300/50 rounded text-xs font-bold transition-all align-middle">
                                    Edit
                                </button>
                                <button wire:click="deletePayslip({{ $ps->id }})" wire:confirm="Are you sure you want to delete this payslip?" class="px-2.5 py-1 bg-rose-100 hover:bg-rose-200 text-rose-800 border border-rose-300/50 rounded text-xs font-bold transition-all align-middle">
                                    Delete
                                </button>
                            </td>
                            <td class="px-4 py-3 font-sans font-medium text-white">{{ $ps->employee->name }}</td>
                            <td class="px-4 py-3 font-sans">{{ $ps->employee->department }}</td>
                            <td class="px-4 py-3">{{ $ps->employee->pps_number }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $weekNo }}</td>
                            <td class="px-4 py-3">{{ $ps->period_start->format('d-m-Y') }}</td>
                            <td class="px-4 py-3">{{ $ps->period_end->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-right text-slate-200 font-semibold">€{{ number_format($ps->gross_pay, 2) }}</td>
                            <td class="px-4 py-3 text-right text-rose-400/80">€{{ number_format($ps->paye, 2) }}</td>
                            <td class="px-4 py-3 text-right text-rose-400/80">€{{ number_format($ps->usc, 2) }}</td>
                            <td class="px-4 py-3 text-right text-rose-400/80">€{{ number_format($ps->prsi, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-400">€{{ number_format($ps->employer_prsi, 2) }}</td>
                            <td class="px-4 py-3 text-right text-rose-500 font-bold">€{{ number_format($deductions, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">€0.00</td>
                            <td class="px-4 py-3 text-right text-emerald-400 font-bold">€{{ number_format($ps->net_pay, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-4 py-12 text-center text-slate-500 font-sans">
                                No records match the search filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Computation Modal Dialog (Process New Computation) -->
    @if($showProcessModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto" x-data="{ activeStep: 1 }">
                <!-- Modal Header -->
                <div class="flex justify-between items-center px-6 py-4 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white font-sans">{{ $editingPayslipId ? 'Edit Payroll Computation' : 'Process New Payroll Computation' }}</h3>
                    <button wire:click="$set('showProcessModal', false)" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column: Inputs & Steps -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Input Form -->
                             <div class="border border-slate-200 p-5 rounded-xl space-y-4 shadow-sm" style="background-color: #f8fafc !important;">
                                 <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                     <div>
                                         <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 font-sans">Select Employee</label>
                                         <select wire:model.live="selectedEmployeeId" class="w-full rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500" style="background-color: #ffffff !important; color: #1e293b !important; border: 1px solid #cbd5e1 !important;">
                                             <option value="">-- Select --</option>
                                             @foreach($employees as $emp)
                                                 <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                             @endforeach
                                         </select>
                                     </div>
                                     <div>
                                         <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 font-sans">Period Start</label>
                                         <input wire:model.live="periodStart" type="date" class="w-full rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500" style="background-color: #ffffff !important; color: #1e293b !important; border: 1px solid #cbd5e1 !important;">
                                     </div>
                                     <div>
                                         <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 font-sans">Period End</label>
                                         <input wire:model.live="periodEnd" type="date" class="w-full rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500" style="background-color: #ffffff !important; color: #1e293b !important; border: 1px solid #cbd5e1 !important;">
                                     </div>
                                 </div>
                                 <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-slate-200">
                                     <div>
                                         <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 font-sans">Hours Worked</label>
                                         <input wire:model.live="hoursWorked" type="number" step="0.5" class="w-full rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500" style="background-color: #ffffff !important; color: #1e293b !important; border: 1px solid #cbd5e1 !important;">
                                     </div>
                                     <div>
                                         <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 font-sans">Overtime Hours</label>
                                         <input wire:model.live="overtimeHours" type="number" step="0.5" class="w-full rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500" style="background-color: #ffffff !important; color: #1e293b !important; border: 1px solid #cbd5e1 !important;">
                                     </div>
                                     <div>
                                         <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 font-sans">Bonus / Allowance (€)</label>
                                         <input wire:model.live="bonus" type="number" step="0.01" class="w-full rounded-lg px-3 py-2 text-slate-800 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500" style="background-color: #ffffff !important; color: #1e293b !important; border: 1px solid #cbd5e1 !important;">
                                     </div>
                                 </div>
                             </div>

                            <!-- Steps Tabs -->
                            <div class="flex border border-slate-200 bg-slate-100/80 rounded-xl p-1 gap-1 shadow-inner">
                                <button @click="activeStep = 1" :class="activeStep === 1 ? 'bg-white text-slate-800 shadow-sm border border-slate-200/50 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-white/40'" class="flex-1 py-1.5 px-2 text-xs rounded transition-all font-sans">
                                    1. Gross Pay
                                </button>
                                <button @click="activeStep = 2" :class="activeStep === 2 ? 'bg-white text-slate-800 shadow-sm border border-slate-200/50 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-white/40'" class="flex-1 py-1.5 px-2 text-xs rounded transition-all font-sans" :disabled="!$wire.selectedEmployeeId">
                                    2. PAYE
                                </button>
                                <button @click="activeStep = 3" :class="activeStep === 3 ? 'bg-white text-slate-800 shadow-sm border border-slate-200/50 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-white/40'" class="flex-1 py-1.5 px-2 text-xs rounded transition-all font-sans" :disabled="!$wire.selectedEmployeeId">
                                    3. USC
                                </button>
                                <button @click="activeStep = 4" :class="activeStep === 4 ? 'bg-white text-slate-800 shadow-sm border border-slate-200/50 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-white/40'" class="flex-1 py-1.5 px-2 text-xs rounded transition-all font-sans" :disabled="!$wire.selectedEmployeeId">
                                    4. PRSI
                                </button>
                                <button @click="activeStep = 5" :class="activeStep === 5 ? 'bg-white text-slate-800 shadow-sm border border-slate-200/50 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-white/40'" class="flex-1 py-1.5 px-2 text-xs rounded transition-all font-sans" :disabled="!$wire.selectedEmployeeId">
                                    5. Net Wages
                                </button>
                            </div>

                            <!-- Steps Data -->
                             <div class="border border-slate-200 p-5 rounded-xl shadow-sm" style="background-color: #fafafa !important;">
                                 @if(!$selectedEmployeeId)
                                     <div class="py-8 text-center text-slate-500 text-xs font-sans">
                                         Please select an employee above to preview calculation steps.
                                     </div>
                                 @else
                                     <!-- Step 1: Gross Pay -->
                                     <div x-show="activeStep === 1" class="space-y-2">
                                         <h4 class="text-xs font-bold text-slate-800 font-sans">Gross Pay breakdown (€)</h4>
                                         <div class="divide-y divide-slate-100 font-mono text-xs">
                                             <div class="flex justify-between py-2"><span class="text-slate-600">Basic Earnings:</span> <span class="text-slate-900 font-semibold">{{ number_format($calculation['basic_pay'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2"><span class="text-slate-600">Overtime Earnings:</span> <span class="text-slate-900 font-semibold">{{ number_format($calculation['overtime_pay'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2"><span class="text-slate-600">Bonus / Allowances:</span> <span class="text-slate-900 font-semibold">{{ number_format($calculation['bonus_pay'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2 font-bold text-emerald-600 pt-1.5 border-t border-slate-200"><span>Total Weekly Gross:</span> <span>{{ number_format($calculation['gross_pay'] ?? 0, 2) }}</span></div>
                                         </div>
                                     </div>

                                     <!-- Step 2: PAYE -->
                                     <div x-show="activeStep === 2" class="space-y-2">
                                         <h4 class="text-xs font-bold text-slate-800 font-sans">PAYE Tax Calculation (€)</h4>
                                         <div class="divide-y divide-slate-100 font-mono text-xs">
                                             <div class="flex justify-between py-2"><span class="text-slate-600">Weekly Cutoff:</span> <span class="text-slate-900">{{ number_format($calculation['meta']['paye_cutoff'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2"><span class="text-slate-600">Taxed at 20%:</span> <span class="text-slate-900">{{ number_format(min($calculation['gross_pay'] ?? 0, $calculation['meta']['paye_cutoff'] ?? 0), 2) }}</span></div>
                                             <div class="flex justify-between py-2"><span class="text-slate-600">Taxed at 40%:</span> <span class="text-slate-900">{{ number_format(max(0, ($calculation['gross_pay'] ?? 0) - ($calculation['meta']['paye_cutoff'] ?? 0)), 2) }}</span></div>
                                             <div class="flex justify-between py-2"><span class="text-slate-600">Gross PAYE (A):</span> <span class="text-slate-900">{{ number_format($calculation['meta']['paye_gross'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2 text-rose-600"><span class="text-slate-600">Weekly Credits (B):</span> <span class="font-medium">- {{ number_format($calculation['meta']['paye_credit'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2 font-bold text-rose-600 pt-1.5 border-t border-slate-200"><span>Net PAYE Deduction:</span> <span>{{ number_format($calculation['paye'] ?? 0, 2) }}</span></div>
                                         </div>
                                     </div>

                                     <!-- Step 3: USC -->
                                     <div x-show="activeStep === 3" class="space-y-2">
                                         <h4 class="text-xs font-bold text-slate-800 font-sans">USC progressive bands (€)</h4>
                                         <div class="divide-y divide-slate-100 font-mono text-xs">
                                             <div class="flex justify-between py-2"><span class="text-slate-600">Band 1 (0.5% of €231):</span> <span class="text-slate-900">{{ number_format(min($calculation['gross_pay'] ?? 0, 231.00) * 0.005, 2) }}</span></div>
                                             @if(($calculation['gross_pay'] ?? 0) > 231.00)
                                                 <div class="flex justify-between py-2"><span class="text-slate-600">Band 2 (2.0% of next €264.38):</span> <span class="text-slate-900">{{ number_format(min(max(0, ($calculation['gross_pay'] ?? 0) - 231.00), 264.38) * 0.02, 2) }}</span></div>
                                             @endif
                                             @if(($calculation['gross_pay'] ?? 0) > 495.38)
                                                 <div class="flex justify-between py-2"><span class="text-slate-600">Band 3 (4.0% of next €852.77):</span> <span class="text-slate-900">{{ number_format(min(max(0, ($calculation['gross_pay'] ?? 0) - 495.38), 852.77) * 0.04, 2) }}</span></div>
                                             @endif
                                             @if(($calculation['gross_pay'] ?? 0) > 1348.15)
                                                 <div class="flex justify-between py-2"><span class="text-slate-600">Band 4 (8.0% of balance):</span> <span class="text-slate-900">{{ number_format(max(0, ($calculation['gross_pay'] ?? 0) - 1348.15) * 0.08, 2) }}</span></div>
                                             @endif
                                             <div class="flex justify-between py-2 font-bold text-rose-600 pt-1.5 border-t border-slate-200"><span>Total USC Deduction:</span> <span>{{ number_format($calculation['usc'] ?? 0, 2) }}</span></div>
                                         </div>
                                     </div>

                                     <!-- Step 4: PRSI -->
                                     <div x-show="activeStep === 4" class="space-y-2">
                                         <h4 class="text-xs font-bold text-slate-800 font-sans">PRSI contributions (€)</h4>
                                         <div class="divide-y divide-slate-100 font-mono text-xs">
                                             <div class="flex justify-between py-2"><span class="text-slate-600">EE Rate (4% tapered credit):</span> <span class="text-slate-900 font-semibold">{{ number_format($calculation['prsi'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2"><span class="text-slate-600">ER Rate ({{ ($calculation['gross_pay'] ?? 0) <= 441.00 ? '8.80%' : '11.05%' }}):</span> <span class="text-slate-900 font-semibold">{{ number_format($calculation['employer_prsi'] ?? 0, 2) }}</span></div>
                                         </div>
                                     </div>

                                     <!-- Step 5: Net Wages -->
                                     <div x-show="activeStep === 5" class="space-y-2">
                                         <h4 class="text-xs font-bold text-slate-800 font-sans">Net Wage Calculation (€)</h4>
                                         <div class="divide-y divide-slate-100 font-mono text-xs">
                                             <div class="flex justify-between py-2 text-emerald-600"><span>Gross Earnings:</span> <span class="font-semibold">+ {{ number_format($calculation['gross_pay'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2 text-rose-600"><span>PAYE Tax:</span> <span class="font-semibold">- {{ number_format($calculation['paye'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2 text-rose-600"><span>USC Charge:</span> <span class="font-semibold">- {{ number_format($calculation['usc'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2 text-rose-600"><span>EE PRSI:</span> <span class="font-semibold">- {{ number_format($calculation['prsi'] ?? 0, 2) }}</span></div>
                                             <div class="flex justify-between py-2 font-bold text-emerald-600 pt-1.5 text-sm border-t border-slate-200"><span>Net Take-home:</span> <span>{{ number_format($calculation['net_pay'] ?? 0, 2) }}</span></div>
                                         </div>
                                     </div>
                                 @endif
                             </div>
                        </div>

                             <div class="bg-emerald-50/80 border border-emerald-200/80 p-5 rounded-xl relative overflow-hidden shadow-sm h-full">
                                <h3 class="text-sm font-bold text-slate-800 border-b border-emerald-200/80 pb-3 mb-4 font-sans">Calculation Summary</h3>
                                
                                @if (session()->has('error'))
                                    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center gap-2 font-sans">
                                        <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ session('error') }}</span>
                                    </div>
                                @endif
 
                                @if (session()->has('message'))
                                    <div class="mb-4 p-3 bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2 font-sans">
                                        <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ session('message') }}</span>
                                    </div>
                                @endif
 
                                @if($calculation)
                                    <div class="space-y-4">
                                        <div class="text-center bg-white border border-emerald-250 p-4 rounded-xl shadow-sm">
                                            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider font-sans">Take-Home Wages</div>
                                            <div class="text-2xl font-extrabold text-slate-900 mt-1">€{{ number_format($calculation['net_pay'], 2) }}</div>
                                        </div>
 
                                        <div class="space-y-2 font-mono text-[11px]">
                                            <div class="flex justify-between text-slate-600"><span>Gross Pay:</span> <span class="text-slate-800 font-semibold">€{{ number_format($calculation['gross_pay'], 2) }}</span></div>
                                            <div class="flex justify-between text-slate-600"><span>PAYE:</span> <span class="text-rose-600 font-semibold">€{{ number_format($calculation['paye'], 2) }}</span></div>
                                            <div class="flex justify-between text-slate-600"><span>USC:</span> <span class="text-rose-600 font-semibold">€{{ number_format($calculation['usc'], 2) }}</span></div>
                                            <div class="flex justify-between text-slate-600"><span>EE PRSI:</span> <span class="text-rose-600 font-semibold">€{{ number_format($calculation['prsi'], 2) }}</span></div>
                                            <div class="border-t border-emerald-200/80 my-1"></div>
                                            <div class="flex justify-between text-slate-600"><span>ER PRSI:</span> <span class="text-slate-850 font-semibold">€{{ number_format($calculation['employer_prsi'], 2) }}</span></div>
                                        </div>

                                        <div class="pt-4 border-t border-slate-800 space-y-2">
                                            @if($editingPayslipId)
                                                <button wire:click="addOrUpdatePayslip" class="w-full py-2.5 px-4 bg-sky-100 hover:bg-sky-200 active:scale-95 text-sky-800 border border-sky-300/50 font-bold rounded-xl text-sm transition-all flex items-center justify-center gap-2 font-sans">
                                                    <svg class="h-4 w-4 text-sky-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    <span class="font-bold text-sky-800">Add/Update</span>
                                                </button>
                                            @endif

                                            <button wire:click="savePayslip" class="w-full py-2.5 px-4 bg-emerald-100 hover:bg-emerald-200 active:scale-95 text-emerald-800 border border-emerald-300/50 font-bold rounded-xl text-sm transition-all flex items-center justify-center gap-2 font-sans">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                                </svg>
                                                <span>Process & File</span>
                                            </button>

                                            @if($lastSavedPayslipId)
                                                <a href="{{ route('pdf.payslip', $lastSavedPayslipId) }}" target="_blank"
                                                   class="w-full py-2.5 px-4 bg-blue-500/20 border border-blue-500/30 hover:bg-blue-500/30 active:scale-95 text-blue-300 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-2 font-sans">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span>Download PDF</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="py-8 text-center text-slate-650 text-xs font-sans">
                                        Select an employee and adjust hours to preview.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-slate-800 flex justify-end">
                    <button wire:click="$set('showProcessModal', false)" class="py-2 px-4 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-800 border border-slate-200/60 font-semibold rounded-lg text-sm transition-all font-sans">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
