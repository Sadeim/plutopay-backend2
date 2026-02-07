@extends('layouts.app')
@section('title', 'POS')

@php
    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪', 'AED' => 'د.إ', 'SAR' => '﷼', 'JOD' => 'د.ا'];
    $sym = $currencySymbols[$currency] ?? $currency . ' ';
@endphp

@section('content')
<div class="flex flex-col gap-5 lg:gap-7.5">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">

        {{-- Left: Charge Panel --}}
        <div class="lg:col-span-2">
            <div class="kt-card">
                <div class="kt-card-content p-6 lg:p-10">

                    {{-- Amount Display --}}
                    <div class="flex flex-col items-center gap-2 mb-8">
                        <span class="text-xs text-secondary-foreground uppercase tracking-wider font-medium">Charge Amount</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl text-secondary-foreground font-light">{{ $sym }}</span>
                            <input type="text" id="amountDisplay"
                                   class="text-6xl lg:text-7xl font-bold text-foreground text-center bg-transparent border-none outline-none w-full max-w-[400px]"
                                   value="0.00" inputmode="decimal" readonly>
                        </div>
                    </div>

                    {{-- Numpad --}}
                    <div class="grid grid-cols-3 gap-3 max-w-[360px] mx-auto mb-8">
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="1">1</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="2">2</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="3">3</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="4">4</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="5">5</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="6">6</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="7">7</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="8">8</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="9">9</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value=".">.</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-muted hover:bg-muted/80 text-foreground transition-all active:scale-95" data-value="0">0</button>
                        <button class="numpad-btn h-16 rounded-xl text-xl font-medium bg-muted hover:bg-destructive/10 text-secondary-foreground hover:text-destructive transition-all active:scale-95" data-value="backspace">
                            <i class="ki-filled ki-arrow-left"></i>
                        </button>
                    </div>

                    {{-- Description --}}
                    <div class="max-w-[360px] mx-auto mb-6">
                        <input type="text" id="description" class="kt-input text-center text-sm"
                               placeholder="Add a note (optional)">
                    </div>

                    {{-- Charge Button --}}
                    <div class="max-w-[360px] mx-auto">
                        <button id="chargeBtn" class="w-full h-14 rounded-xl text-lg font-semibold text-white transition-all active:scale-[0.98]"
                                style="background-color: #3b82f6" disabled>
                            <span id="chargeBtnText">Enter Amount</span>
                        </button>
                    </div>

                    {{-- Status Display --}}
                    <div id="statusPanel" class="hidden max-w-[360px] mx-auto mt-6">
                        <div class="flex flex-col items-center gap-3 p-6 rounded-xl bg-muted">
                            <div id="statusSpinner" class="hidden">
                                <div class="size-10 border-3 border-primary/30 border-t-primary rounded-full animate-spin"></div>
                            </div>
                            <div id="statusIcon" class="hidden text-4xl"></div>
                            <span id="statusText" class="text-sm font-medium text-foreground text-center"></span>
                            <span id="statusSub" class="text-xs text-secondary-foreground text-center"></span>
                            <button id="newChargeBtn" class="hidden kt-btn kt-btn-sm kt-btn-primary mt-2">New Charge</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Terminal Selection + Recent --}}
        <div class="flex flex-col gap-5">

            {{-- Terminal Select --}}
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <h3 class="text-sm font-semibold text-foreground mb-3">Terminal</h3>
                    @if($terminals->count() > 0)
                    <div class="flex flex-col gap-2">
                        @foreach($terminals as $terminal)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-input cursor-pointer hover:bg-muted/50 transition-colors terminal-option {{ $loop->first ? 'border-primary bg-primary/5' : '' }}"
                               data-terminal-id="{{ $terminal->id }}"
                               data-reader-id="{{ $terminal->processor_terminal_id }}"
                               data-status="{{ $terminal->status }}">
                            <input type="radio" name="terminal" value="{{ $terminal->id }}"
                                   class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="flex items-center justify-center size-9 rounded-lg bg-primary/10">
                                <i class="ki-filled ki-technology-4 text-primary"></i>
                            </div>
                            <div class="flex flex-col flex-1">
                                <span class="text-sm font-medium text-foreground">{{ $terminal->name }}</span>
                                <span class="text-xs text-secondary-foreground">{{ $terminal->location_name ?? 'No location' }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                @if($terminal->status === 'online')
                                <span class="size-2 rounded-full" style="background-color:#22c55e"></span>
                                <span class="text-xs" style="color:#22c55e">Online</span>
                                @else
                                <span class="size-2 rounded-full bg-secondary-foreground/30"></span>
                                <span class="text-xs text-secondary-foreground">Offline</span>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @else
                    <div class="flex flex-col items-center gap-2 py-6">
                        <i class="ki-filled ki-technology-4 text-2xl text-muted-foreground"></i>
                        <span class="text-xs text-secondary-foreground">No terminals</span>
                        <a href="{{ route('dashboard.terminals.index') }}" class="text-xs text-primary">Register one</a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Amounts --}}
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <h3 class="text-sm font-semibold text-foreground mb-3">Quick Amounts</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <button class="quick-amount kt-btn kt-btn-sm kt-btn-light" data-amount="5.00">{{ $sym }}5</button>
                        <button class="quick-amount kt-btn kt-btn-sm kt-btn-light" data-amount="10.00">{{ $sym }}10</button>
                        <button class="quick-amount kt-btn kt-btn-sm kt-btn-light" data-amount="20.00">{{ $sym }}20</button>
                        <button class="quick-amount kt-btn kt-btn-sm kt-btn-light" data-amount="25.00">{{ $sym }}25</button>
                        <button class="quick-amount kt-btn kt-btn-sm kt-btn-light" data-amount="50.00">{{ $sym }}50</button>
                        <button class="quick-amount kt-btn kt-btn-sm kt-btn-light" data-amount="100.00">{{ $sym }}100</button>
                    </div>
                </div>
            </div>

            {{-- Recent Terminal Transactions --}}
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <h3 class="text-sm font-semibold text-foreground mb-3">Recent POS Payments</h3>
                    @if($recentTransactions->count() > 0)
                    <div class="flex flex-col gap-2">
                        @foreach($recentTransactions as $txn)
                        <a href="{{ route('dashboard.transactions.show', $txn->id) }}"
                           class="flex items-center justify-between p-2 rounded-md hover:bg-muted/50 transition-colors">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-foreground">{{ $sym }}{{ number_format($txn->amount / 100, 2) }}</span>
                                <span class="text-xs text-secondary-foreground">{{ $txn->created_at->diffForHumans() }}</span>
                            </div>
                            @if($txn->status === 'succeeded')
                            <span class="size-2 rounded-full" style="background-color:#22c55e"></span>
                            @elseif($txn->status === 'pending')
                            <span class="size-2 rounded-full" style="background-color:#f59e0b"></span>
                            @else
                            <span class="size-2 rounded-full" style="background-color:#ef4444"></span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                    @else
                    <div class="text-xs text-secondary-foreground text-center py-4">No POS payments yet</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rawAmount = '';
    const display = document.querySelector('#amountDisplay');
    const chargeBtn = document.querySelector('#chargeBtn');
    const chargeBtnText = document.querySelector('#chargeBtnText');
    const statusPanel = document.querySelector('#statusPanel');
    const statusSpinner = document.querySelector('#statusSpinner');
    const statusIcon = document.querySelector('#statusIcon');
    const statusText = document.querySelector('#statusText');
    const statusSub = document.querySelector('#statusSub');
    const newChargeBtn = document.querySelector('#newChargeBtn');
    const sym = '{{ $sym }}';

    function updateDisplay() {
        if (rawAmount === '' || rawAmount === '0') {
            display.value = '0.00';
            chargeBtn.disabled = true;
            chargeBtn.style.opacity = '0.5';
            chargeBtnText.textContent = 'Enter Amount';
            return;
        }

        let val = rawAmount;
        if (val.indexOf('.') === -1) {
            display.value = parseFloat(val).toFixed(2);
        } else {
            let parts = val.split('.');
            parts[1] = (parts[1] || '').substring(0, 2);
            display.value = parts[0] + '.' + parts[1].padEnd(2, '0');
        }

        const amount = parseFloat(display.value);
        if (amount >= 0.50) {
            chargeBtn.disabled = false;
            chargeBtn.style.opacity = '1';
            chargeBtnText.textContent = 'Charge ' + sym + display.value;
        } else {
            chargeBtn.disabled = true;
            chargeBtn.style.opacity = '0.5';
            chargeBtnText.textContent = 'Min ' + sym + '0.50';
        }
    }

    // Numpad
    document.querySelectorAll('.numpad-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const val = this.dataset.value;
            if (val === 'backspace') {
                rawAmount = rawAmount.slice(0, -1);
            } else if (val === '.') {
                if (!rawAmount.includes('.')) rawAmount += rawAmount ? '.' : '0.';
            } else {
                if (rawAmount.includes('.') && rawAmount.split('.')[1].length >= 2) return;
                if (rawAmount === '0' && val !== '.') rawAmount = val;
                else rawAmount += val;
            }
            updateDisplay();
        });
    });

    // Quick amounts
    document.querySelectorAll('.quick-amount').forEach(btn => {
        btn.addEventListener('click', function() {
            rawAmount = this.dataset.amount;
            updateDisplay();
        });
    });

    // Terminal selection
    document.querySelectorAll('.terminal-option').forEach(opt => {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.terminal-option').forEach(o => {
                o.classList.remove('border-primary', 'bg-primary/5');
            });
            this.classList.add('border-primary', 'bg-primary/5');
            this.querySelector('input').checked = true;
        });
    });

    // Charge
    chargeBtn.addEventListener('click', async function() {
        const amount = parseFloat(display.value);
        const terminalRadio = document.querySelector('input[name="terminal"]:checked');
        const description = document.querySelector('#description').value;

        if (!terminalRadio) {
            alert('Please select a terminal');
            return;
        }

        // Show status
        chargeBtn.disabled = true;
        statusPanel.classList.remove('hidden');
        statusSpinner.classList.remove('hidden');
        statusIcon.classList.add('hidden');
        newChargeBtn.classList.add('hidden');
        statusText.textContent = 'Sending to terminal...';
        statusSub.textContent = 'Waiting for customer to present card';

        try {
            const res = await fetch('{{ route("dashboard.pos.charge") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    amount: amount,
                    terminal_id: terminalRadio.value,
                    description: description || null,
                }),
            });

            const data = await res.json();

            if (data.success) {
                statusText.textContent = 'Waiting for customer...';
                statusSub.textContent = 'Present card on terminal';

                // Poll for status
                pollStatus(data.transaction.id);
            } else {
                showError(data.message || 'Failed to send to terminal');
            }
        } catch (err) {
            showError(err.message || 'Network error');
        }
    });

    function pollStatus(txnId) {
        let attempts = 0;
        const maxAttempts = 60; // 2 minutes

        const interval = setInterval(async () => {
            attempts++;
            if (attempts > maxAttempts) {
                clearInterval(interval);
                showError('Payment timed out. Check terminal.');
                return;
            }

            try {
                const res = await fetch(`/dashboard/pos/status/${txnId}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();

                if (data.status === 'succeeded') {
                    clearInterval(interval);
                    showSuccess(data);
                } else if (data.status === 'failed' || data.status === 'canceled') {
                    clearInterval(interval);
                    showError('Payment ' + data.status);
                }
            } catch (e) { /* continue polling */ }
        }, 2000);
    }

    function showSuccess(data) {
        statusSpinner.classList.add('hidden');
        statusIcon.innerHTML = '<i class="ki-filled ki-check-circle" style="color:#22c55e"></i>';
        statusIcon.classList.remove('hidden');
        statusText.textContent = 'Payment Successful!';
        statusSub.textContent = sym + display.value + ' charged';
        newChargeBtn.classList.remove('hidden');
    }

    function showError(msg) {
        statusSpinner.classList.add('hidden');
        statusIcon.innerHTML = '<i class="ki-filled ki-cross-circle" style="color:#ef4444"></i>';
        statusIcon.classList.remove('hidden');
        statusText.textContent = 'Payment Failed';
        statusSub.textContent = msg;
        newChargeBtn.classList.remove('hidden');
        chargeBtn.disabled = false;
    }

    newChargeBtn.addEventListener('click', function() {
        rawAmount = '';
        updateDisplay();
        statusPanel.classList.add('hidden');
        document.querySelector('#description').value = '';
        chargeBtn.disabled = true;
    });
});
</script>
@endpush
