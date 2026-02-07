@php
    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪', 'AED' => 'د.إ', 'SAR' => '﷼', 'JOD' => 'د.ا'];
    $sym = $currencySymbols[$currency] ?? $currency . ' ';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $merchant->display_name ?? $merchant->business_name }} - POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            overscroll-behavior: none;
        }
        .numpad-btn:active { transform: scale(0.95); }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 1s linear infinite; }
    </style>
</head>
<body class="min-h-screen bg-gray-50">

    {{-- Top Bar --}}
    <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-500 text-white flex items-center justify-center text-sm font-bold">
                {{ strtoupper(substr($merchant->display_name ?? $merchant->business_name, 0, 1)) }}
            </div>
            <div>
                <div class="text-sm font-semibold text-gray-900">{{ $merchant->display_name ?? $merchant->business_name }}</div>
                <div class="text-xs text-gray-500">{{ $user->name }} • {{ $merchant->test_mode ? 'Test Mode' : 'Live' }}</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($merchant->test_mode)
            <span class="px-2 py-1 rounded-md bg-yellow-100 text-yellow-700 text-xs font-medium">TEST</span>
            @else
            <span class="px-2 py-1 rounded-md bg-green-100 text-green-700 text-xs font-medium">LIVE</span>
            @endif
            <form method="POST" action="{{ route('standalone.pos.logout', $merchant->id) }}" class="inline">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-5xl mx-auto p-4 lg:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">

            {{-- Left: Charge Panel --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 lg:p-10">

                    {{-- Amount Display --}}
                    <div class="flex flex-col items-center gap-2 mb-8">
                        <span class="text-xs text-gray-400 uppercase tracking-wider font-medium">Charge Amount</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl text-gray-400 font-light">{{ $sym }}</span>
                            <input type="text" id="amountDisplay"
                                   class="text-6xl lg:text-7xl font-bold text-gray-900 text-center bg-transparent border-none outline-none w-full max-w-[400px]"
                                   value="0.00" inputmode="decimal" readonly>
                        </div>
                    </div>

                    {{-- Numpad --}}
                    <div class="grid grid-cols-3 gap-3 max-w-[360px] mx-auto mb-8">
                        @foreach([1,2,3,4,5,6,7,8,9] as $n)
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-gray-100 hover:bg-gray-200 text-gray-900 transition-all" data-value="{{ $n }}">{{ $n }}</button>
                        @endforeach
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-gray-100 hover:bg-gray-200 text-gray-900 transition-all" data-value=".">.</button>
                        <button class="numpad-btn h-16 rounded-xl text-2xl font-medium bg-gray-100 hover:bg-gray-200 text-gray-900 transition-all" data-value="0">0</button>
                        <button class="numpad-btn h-16 rounded-xl text-xl font-medium bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-500 transition-all" data-value="backspace">
                            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l7-7 11 0 0 14-11 0-7-7z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Description --}}
                    <div class="max-w-[360px] mx-auto mb-6">
                        <input type="text" id="description"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 text-center text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                               placeholder="Add a note (optional)">
                    </div>

                    {{-- Charge Button --}}
                    <div class="max-w-[360px] mx-auto">
                        <button id="chargeBtn"
                                class="w-full h-14 rounded-xl text-lg font-semibold text-white bg-blue-500 hover:bg-blue-600 active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <span id="chargeBtnText">Enter Amount</span>
                        </button>
                    </div>

                    {{-- Status Display --}}
                    <div id="statusPanel" class="hidden max-w-[360px] mx-auto mt-6">
                        <div class="flex flex-col items-center gap-3 p-6 rounded-xl bg-gray-50 border border-gray-200">
                            <div id="statusSpinner" class="hidden">
                                <div class="w-10 h-10 border-[3px] border-blue-200 border-t-blue-500 rounded-full spin"></div>
                            </div>
                            <div id="statusIcon" class="hidden text-4xl"></div>
                            <span id="statusText" class="text-sm font-medium text-gray-900 text-center"></span>
                            <span id="statusSub" class="text-xs text-gray-500 text-center"></span>
                            <button id="cancelPaymentBtn" class="hidden w-full mt-2 px-4 py-2.5 rounded-xl border border-red-200 bg-red-50 text-red-600 font-medium text-sm hover:bg-red-100 transition-colors">
                                ✕ Cancel Payment
                            </button>
                            <button id="newChargeBtn" class="hidden mt-2 px-6 py-2 rounded-xl bg-blue-500 text-white font-medium text-sm hover:bg-blue-600 transition-colors">
                                New Charge
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Terminal + Recent --}}
            <div class="flex flex-col gap-4">

                {{-- Terminal Selection --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Terminal</h3>
                    @if($terminals->count() > 0)
                    <div class="flex flex-col gap-2">
                        @foreach($terminals as $terminal)
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer hover:bg-gray-50 transition-colors terminal-option {{ $loop->first ? 'border-blue-500 bg-blue-50/50' : 'border-gray-200' }}">
                            <input type="radio" name="terminal" value="{{ $terminal->id }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex flex-col flex-1">
                                <span class="text-sm font-medium text-gray-900">{{ $terminal->name }}</span>
                                <span class="text-xs text-gray-500">{{ $terminal->location_name ?? 'No location' }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                <span class="text-xs text-green-600">Online</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @else
                    <div class="flex flex-col items-center gap-2 py-6 text-center">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-xs text-gray-400">No terminals online</span>
                    </div>
                    @endif
                </div>

                {{-- Quick Amounts --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Quick Amounts</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <button class="quick-amount px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 transition-colors" data-amount="5.00">{{ $sym }}5</button>
                        <button class="quick-amount px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 transition-colors" data-amount="10.00">{{ $sym }}10</button>
                        <button class="quick-amount px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 transition-colors" data-amount="20.00">{{ $sym }}20</button>
                        <button class="quick-amount px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 transition-colors" data-amount="25.00">{{ $sym }}25</button>
                        <button class="quick-amount px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 transition-colors" data-amount="50.00">{{ $sym }}50</button>
                        <button class="quick-amount px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 transition-colors" data-amount="100.00">{{ $sym }}100</button>
                    </div>
                </div>

                {{-- Recent Payments --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Recent Payments</h3>
                    @if($recentTransactions->count() > 0)
                    <div class="flex flex-col gap-1">
                        @foreach($recentTransactions as $txn)
                        <div class="flex items-center justify-between p-2 rounded-lg">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $sym }}{{ number_format($txn->amount / 100, 2) }}</span>
                                <span class="text-xs text-gray-400 ml-2">{{ $txn->created_at->diffForHumans() }}</span>
                            </div>
                            @if($txn->status === 'succeeded')
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            @elseif($txn->status === 'pending')
                            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                            @else
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-xs text-gray-400 text-center py-4">No payments yet</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center py-4">
        <span class="text-xs text-gray-400">Powered by PlutoPay</span>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const display = document.getElementById('amountDisplay');
    const chargeBtn = document.getElementById('chargeBtn');
    const chargeBtnText = document.getElementById('chargeBtnText');
    const statusPanel = document.getElementById('statusPanel');
    const statusSpinner = document.getElementById('statusSpinner');
    const statusIcon = document.getElementById('statusIcon');
    const statusText = document.getElementById('statusText');
    const statusSub = document.getElementById('statusSub');
    const newChargeBtn = document.getElementById('newChargeBtn');
    const cancelBtn = document.getElementById('cancelPaymentBtn');
    const sym = '{{ $sym }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const merchantId = '{{ $merchant->id }}';

    let rawAmount = '';
    let currentInterval = null;
    let currentTxnId = null;

    function updateDisplay() {
        if (!rawAmount || rawAmount === '0') {
            display.value = '0.00';
        } else {
            let parts = rawAmount.split('.');
            parts[1] = (parts[1] || '').substring(0, 2);
            display.value = parts[0] + '.' + parts[1].padEnd(2, '0');
        }

        const amount = parseFloat(display.value);
        if (amount >= 0.50) {
            chargeBtn.disabled = false;
            chargeBtnText.textContent = 'Charge ' + sym + display.value;
        } else {
            chargeBtn.disabled = true;
            chargeBtnText.textContent = amount > 0 ? 'Min ' + sym + '0.50' : 'Enter Amount';
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
                o.classList.remove('border-blue-500', 'bg-blue-50/50');
                o.classList.add('border-gray-200');
            });
            this.classList.remove('border-gray-200');
            this.classList.add('border-blue-500', 'bg-blue-50/50');
            this.querySelector('input').checked = true;
        });
    });

    // Charge
    chargeBtn.addEventListener('click', async function() {
        const amount = parseFloat(display.value);
        const terminalRadio = document.querySelector('input[name="terminal"]:checked');
        const description = document.getElementById('description').value;

        if (!terminalRadio) {
            alert('Please select a terminal');
            return;
        }

        chargeBtn.disabled = true;
        statusPanel.classList.remove('hidden');
        statusSpinner.classList.remove('hidden');
        statusIcon.classList.add('hidden');
        newChargeBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        statusText.textContent = 'Sending to terminal...';
        statusSub.textContent = 'Waiting for customer to present card';

        try {
            const res = await fetch('/pos/' + merchantId + '/charge', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ amount, terminal_id: terminalRadio.value, description: description || null }),
            });

            const data = await res.json();

            if (data.success) {
                statusText.textContent = 'Waiting for customer...';
                statusSub.textContent = 'Present card on terminal';
                cancelBtn.classList.remove('hidden');
                currentTxnId = data.transaction.id;
                pollStatus(data.transaction.id);
            } else {
                showError(data.message || 'Failed');
            }
        } catch (err) {
            showError(err.message || 'Network error');
        }
    });

    function pollStatus(txnId) {
        let attempts = 0;

        currentInterval = setInterval(async () => {
            attempts++;

            // Auto-cancel after 3 minutes (90 * 2s)
            if (attempts >= 90) {
                clearInterval(currentInterval);
                await cancelPayment(txnId);
                showError('Payment auto-cancelled (timeout)');
                return;
            }

            try {
                const res = await fetch('/pos/' + merchantId + '/status/' + txnId, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await res.json();

                if (data.status === 'succeeded') {
                    clearInterval(currentInterval);
                    showSuccess(data);
                } else if (data.status === 'failed' || data.status === 'canceled') {
                    clearInterval(currentInterval);
                    showError('Payment ' + data.status);
                }
            } catch (e) {}
        }, 2000);
    }

    async function cancelPayment(txnId) {
        try {
            await fetch('/pos/' + merchantId + '/cancel/' + txnId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
        } catch (e) {}
    }

    cancelBtn.addEventListener('click', async function() {
        if (!currentTxnId) return;
        cancelBtn.disabled = true;
        cancelBtn.textContent = 'Cancelling...';
        if (currentInterval) clearInterval(currentInterval);
        await cancelPayment(currentTxnId);
        cancelBtn.disabled = false;
        cancelBtn.innerHTML = '✕ Cancel Payment';
        showError('Payment cancelled');
    });

    function showSuccess(data) {
        statusSpinner.classList.add('hidden');
        statusIcon.innerHTML = '<svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        statusIcon.classList.remove('hidden');
        statusText.textContent = 'Payment Successful!';
        statusSub.textContent = sym + display.value + ' charged' + (data.card_brand ? ' • ' + data.card_brand + ' ••••' + data.card_last4 : '');
        newChargeBtn.classList.remove('hidden');
        cancelBtn.classList.add('hidden');
    }

    function showError(msg) {
        statusSpinner.classList.add('hidden');
        statusIcon.innerHTML = '<svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        statusIcon.classList.remove('hidden');
        statusText.textContent = 'Payment Failed';
        statusSub.textContent = msg;
        newChargeBtn.classList.remove('hidden');
        cancelBtn.classList.add('hidden');
        chargeBtn.disabled = false;
    }

    newChargeBtn.addEventListener('click', function() {
        rawAmount = '';
        updateDisplay();
        statusPanel.classList.add('hidden');
        document.getElementById('description').value = '';
        chargeBtn.disabled = true;
        currentTxnId = null;
    });

    // Keyboard support
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' && e.target.id !== 'amountDisplay') return;
        if (e.key >= '0' && e.key <= '9') {
            if (rawAmount.includes('.') && rawAmount.split('.')[1].length >= 2) return;
            if (rawAmount === '0' && e.key !== '0') rawAmount = e.key;
            else rawAmount += e.key;
            updateDisplay();
        } else if (e.key === '.' || e.key === ',') {
            if (!rawAmount.includes('.')) rawAmount += rawAmount ? '.' : '0.';
            updateDisplay();
        } else if (e.key === 'Backspace') {
            rawAmount = rawAmount.slice(0, -1);
            updateDisplay();
        } else if (e.key === 'Enter' && !chargeBtn.disabled) {
            chargeBtn.click();
        }
    });
});
</script>
</body>
</html>
