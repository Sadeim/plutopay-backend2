@extends('layouts.app')
@section('title', 'Reports')

@php
    $currency = auth()->user()->merchant->default_currency ?? 'USD';
    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪', 'AED' => 'د.إ', 'SAR' => '﷼'];
    $symbol = $currencySymbols[$currency] ?? $currency . ' ';
@endphp

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Page Header --}}
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Reports</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Generate and export detailed business reports
                </div>
            </div>
        </div>

        {{-- Report Generator --}}
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7">
                <h3 class="text-base font-semibold text-foreground mb-5">Generate Report</h3>
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex flex-col gap-1.5 min-w-[200px]">
                        <label class="text-xs font-medium text-secondary-foreground">Report Type</label>
                        <select class="kt-select" id="reportType">
                            <option value="summary">Summary Overview</option>
                            <option value="transactions">Transactions Detail</option>
                            <option value="payouts">Payouts Detail</option>
                            <option value="daily">Daily Breakdown</option>
                            <option value="payment_methods">Payment Methods</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-secondary-foreground">From Date</label>
                        <input type="date" class="kt-input" id="dateFrom" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-secondary-foreground">To Date</label>
                        <input type="date" class="kt-input" id="dateTo" />
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="kt-btn kt-btn-xs kt-btn-outline date-quick" data-range="7d">7 Days</button>
                        <button class="kt-btn kt-btn-xs kt-btn-outline date-quick active-range" data-range="30d">30 Days</button>
                        <button class="kt-btn kt-btn-xs kt-btn-outline date-quick" data-range="90d">90 Days</button>
                        <button class="kt-btn kt-btn-xs kt-btn-outline date-quick" data-range="ytd">YTD</button>
                        <button class="kt-btn kt-btn-xs kt-btn-outline date-quick" data-range="all">All Time</button>
                    </div>
                    <div class="flex items-center gap-2 ml-auto">
                        <button class="kt-btn kt-btn-primary" id="generateBtn">
                            <i class="ki-filled ki-chart-simple"></i> Generate
                        </button>
                        <button class="kt-btn kt-btn-outline" id="exportBtn">
                            <i class="ki-filled ki-exit-down"></i> Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Report Content --}}
        <div id="reportContent">
            <div class="flex flex-col items-center gap-3 p-16">
                <i class="ki-filled ki-chart-simple text-4xl text-muted-foreground"></i>
                <span class="text-sm text-secondary-foreground">Select a report type and date range, then click Generate</span>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportType = document.getElementById('reportType');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const content = document.getElementById('reportContent');
    const symbol = '{{ $symbol }}';

    // Set default 30 days
    const today = new Date();
    const thirtyAgo = new Date(today);
    thirtyAgo.setDate(thirtyAgo.getDate() - 30);
    dateFrom.value = fmt(thirtyAgo);
    dateTo.value = fmt(today);

    function fmt(d) { return d.toISOString().slice(0, 10); }
    function fmtMoney(cents) { return symbol + (cents / 100).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
    function fmtDate(iso) {
        const d = new Date(iso);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    // Quick date buttons
    document.querySelectorAll('.date-quick').forEach(btn => btn.addEventListener('click', () => {
        const range = btn.dataset.range;
        dateTo.value = fmt(today);
        if (range === '7d') { const f = new Date(today); f.setDate(f.getDate() - 7); dateFrom.value = fmt(f); }
        else if (range === '30d') { const f = new Date(today); f.setDate(f.getDate() - 30); dateFrom.value = fmt(f); }
        else if (range === '90d') { const f = new Date(today); f.setDate(f.getDate() - 90); dateFrom.value = fmt(f); }
        else if (range === 'ytd') { dateFrom.value = today.getFullYear() + '-01-01'; }
        else if (range === 'all') { dateFrom.value = '2020-01-01'; }
        document.querySelectorAll('.date-quick').forEach(b => b.classList.remove('kt-btn-primary'));
        btn.classList.add('kt-btn-primary');
        btn.classList.remove('kt-btn-outline');
    }));

    // Generate
    document.getElementById('generateBtn').addEventListener('click', generateReport);
    document.getElementById('exportBtn').addEventListener('click', () => {
        const params = new URLSearchParams({
            report_type: reportType.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
            format: 'csv',
        });
        window.location.href = '/dashboard/reports/generate?' + params;
    });

    async function generateReport() {
        content.innerHTML = '<div class="flex justify-center p-16"><div class="flex items-center gap-3 text-secondary-foreground"><i class="ki-filled ki-loading-3 animate-spin text-xl"></i> Generating report...</div></div>';

        const params = new URLSearchParams({
            report_type: reportType.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        });

        try {
            const res = await fetch('/dashboard/reports/generate?' + params, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            renderReport(reportType.value, data);
        } catch (err) {
            content.innerHTML = '<div class="kt-card"><div class="kt-card-content p-10 text-center text-destructive">Failed to generate report. Please try again.</div></div>';
        }
    }

    function renderReport(type, data) {
        switch (type) {
            case 'summary': renderSummary(data); break;
            case 'transactions': renderTransactions(data); break;
            case 'payouts': renderPayouts(data); break;
            case 'daily': renderDaily(data); break;
            case 'payment_methods': renderPaymentMethods(data); break;
        }
    }

    function renderSummary(data) {
        const o = data.overview;
        content.innerHTML = `
            <div class="flex flex-col gap-5">
                <div class="flex items-center flex-wrap gap-2 lg:gap-5">
                    ${statCard(fmtMoney(o.total_volume), 'Total Volume', 'success')}
                    ${statCard(o.total_transactions, 'Total Transactions', 'mono')}
                    ${statCard(o.success_rate + '%', 'Success Rate', 'primary')}
                    ${statCard(fmtMoney(o.average_transaction), 'Avg Transaction', 'mono')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="kt-card">
                        <div class="kt-card-content p-5 lg:p-7">
                            <h3 class="text-base font-semibold text-foreground mb-4">Transaction Breakdown</h3>
                            <div class="flex flex-col gap-3">
                                ${summaryRow('Succeeded', o.succeeded_count, 'success')}
                                ${summaryRow('Failed', o.failed_count, 'destructive')}
                                ${summaryRow('Canceled', o.canceled_count, 'secondary')}
                            </div>
                        </div>
                    </div>
                    <div class="kt-card">
                        <div class="kt-card-content p-5 lg:p-7">
                            <h3 class="text-base font-semibold text-foreground mb-4">Revenue Summary</h3>
                            <div class="flex flex-col gap-3">
                                ${moneyRow('Gross Volume', o.total_volume)}
                                ${moneyRow('Refunds', -o.total_refunds, true)}
                                <div class="border-t-2 border-foreground/20 pt-2">
                                    ${moneyRow('Net Volume', o.net_volume)}
                                </div>
                                <div class="border-t pt-2">
                                    ${moneyRow('Total Paid Out', o.total_paid_out)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function renderTransactions(data) {
        const txns = data.transactions || [];
        content.innerHTML = `
            <div class="flex flex-col gap-5">
                <div class="flex items-center flex-wrap gap-2 lg:gap-5">
                    ${statCard(txns.length, 'Transactions', 'mono')}
                    ${statCard(fmtMoney(data.totals.total_amount), 'Total Amount', 'success')}
                    ${statCard(fmtMoney(data.totals.total_refunded), 'Total Refunded', 'warning')}
                </div>
                <div class="kt-card kt-card-grid">
                    <div class="kt-card-header"><h3 class="kt-card-title text-sm">Transactions</h3></div>
                    <div class="kt-card-content">
                        <div class="kt-scrollable-x-auto">
                            <table class="kt-table table-auto kt-table-border">
                                <thead><tr>
                                    <th>Reference</th><th class="text-end">Amount</th><th>Status</th>
                                    <th>Method</th><th>Date</th>
                                </tr></thead>
                                <tbody>
                                    ${txns.map(t => `<tr>
                                        <td><code class="text-xs">${t.reference}</code></td>
                                        <td class="text-end font-medium text-mono">${fmtMoney(t.amount)}</td>
                                        <td>${statusBadge(t.status)}</td>
                                        <td class="text-sm text-secondary-foreground">${ucfirst(t.payment_method_type || 'N/A')}</td>
                                        <td class="text-sm text-secondary-foreground">${fmtDate(t.created_at)}</td>
                                    </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function renderPayouts(data) {
        const payouts = data.payouts || [];
        content.innerHTML = `
            <div class="flex flex-col gap-5">
                <div class="flex items-center flex-wrap gap-2 lg:gap-5">
                    ${statCard(data.totals.count, 'Total Payouts', 'mono')}
                    ${statCard(fmtMoney(data.totals.total_amount), 'Total Amount', 'success')}
                    ${statCard(fmtMoney(data.totals.total_fees), 'Total Fees', 'warning')}
                    ${statCard(fmtMoney(data.totals.total_net), 'Net Received', 'primary')}
                </div>
                <div class="kt-card kt-card-grid">
                    <div class="kt-card-header"><h3 class="kt-card-title text-sm">Payouts</h3></div>
                    <div class="kt-card-content">
                        <div class="kt-scrollable-x-auto">
                            <table class="kt-table table-auto kt-table-border">
                                <thead><tr>
                                    <th>Reference</th><th class="text-end">Amount</th><th class="text-end">Fee</th>
                                    <th class="text-end">Net</th><th>Status</th><th>Date</th>
                                </tr></thead>
                                <tbody>
                                    ${payouts.map(p => `<tr>
                                        <td><code class="text-xs">${p.reference}</code></td>
                                        <td class="text-end font-medium text-mono">${fmtMoney(p.amount)}</td>
                                        <td class="text-end text-secondary-foreground">${fmtMoney(p.fee || 0)}</td>
                                        <td class="text-end font-medium text-mono">${fmtMoney(p.net_amount || p.amount)}</td>
                                        <td>${statusBadge(p.status)}</td>
                                        <td class="text-sm text-secondary-foreground">${fmtDate(p.created_at)}</td>
                                    </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function renderDaily(data) {
        const days = data.days || [];
        const t = data.totals;
        const maxVol = Math.max(...days.map(d => d.volume), 1);
        content.innerHTML = `
            <div class="flex flex-col gap-5">
                <div class="flex items-center flex-wrap gap-2 lg:gap-5">
                    ${statCard(fmtMoney(t.total_volume), 'Total Volume', 'success')}
                    ${statCard(t.total_count, 'Total Transactions', 'mono')}
                    ${statCard(t.active_days + ' / ' + t.total_days, 'Active Days', 'primary')}
                    ${statCard(t.best_day ? fmtMoney(t.best_day.volume) + ' (' + t.best_day.date_formatted + ')' : '-', 'Best Day', 'success')}
                </div>
                <div class="kt-card kt-card-grid">
                    <div class="kt-card-header"><h3 class="kt-card-title text-sm">Daily Breakdown</h3></div>
                    <div class="kt-card-content">
                        <div class="kt-scrollable-x-auto">
                            <table class="kt-table table-auto kt-table-border">
                                <thead><tr>
                                    <th class="min-w-[140px]">Date</th>
                                    <th class="text-end min-w-[100px]">Transactions</th>
                                    <th class="text-end min-w-[120px]">Volume</th>
                                    <th class="text-end min-w-[120px]">Average</th>
                                    <th class="min-w-[200px]">Volume Chart</th>
                                </tr></thead>
                                <tbody>
                                    ${days.map(d => `<tr>
                                        <td class="text-sm font-medium">${d.date_formatted}</td>
                                        <td class="text-end text-sm text-mono">${d.count}</td>
                                        <td class="text-end text-sm font-medium text-mono">${fmtMoney(d.volume)}</td>
                                        <td class="text-end text-sm text-secondary-foreground">${d.count > 0 ? fmtMoney(d.avg) : '-'}</td>
                                        <td>
                                            <div class="w-full bg-muted rounded-full h-2">
                                                <div class="bg-primary rounded-full h-2" style="width: ${Math.round((d.volume / maxVol) * 100)}%"></div>
                                            </div>
                                        </td>
                                    </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function renderPaymentMethods(data) {
        const methods = data.methods || [];
        const colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-destructive', 'bg-secondary'];
        content.innerHTML = `
            <div class="flex flex-col gap-5">
                <div class="flex items-center flex-wrap gap-2 lg:gap-5">
                    ${statCard(fmtMoney(data.totals.total_volume), 'Total Volume', 'success')}
                    ${statCard(data.totals.total_count, 'Total Transactions', 'mono')}
                    ${statCard(data.totals.method_count, 'Payment Methods', 'primary')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="kt-card">
                        <div class="kt-card-content p-5 lg:p-7">
                            <h3 class="text-base font-semibold text-foreground mb-5">Volume by Method</h3>
                            <div class="flex flex-col gap-4">
                                ${methods.map((m, i) => `
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium">${m.method_label}</span>
                                            <span class="text-sm text-mono font-semibold">${fmtMoney(m.volume)} (${m.percentage}%)</span>
                                        </div>
                                        <div class="w-full bg-muted rounded-full h-3">
                                            <div class="${colors[i % colors.length]} rounded-full h-3 transition-all" style="width: ${m.percentage}%"></div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                    <div class="kt-card kt-card-grid">
                        <div class="kt-card-header"><h3 class="kt-card-title text-sm">Details</h3></div>
                        <div class="kt-card-content">
                            <table class="kt-table table-auto kt-table-border">
                                <thead><tr>
                                    <th>Method</th><th class="text-end">Count</th>
                                    <th class="text-end">Volume</th><th class="text-end">Avg</th><th class="text-end">%</th>
                                </tr></thead>
                                <tbody>
                                    ${methods.map(m => `<tr>
                                        <td class="text-sm font-medium">${m.method_label}</td>
                                        <td class="text-end text-sm text-mono">${m.count}</td>
                                        <td class="text-end text-sm font-medium text-mono">${fmtMoney(m.volume)}</td>
                                        <td class="text-end text-sm text-secondary-foreground">${fmtMoney(m.avg)}</td>
                                        <td class="text-end text-sm font-semibold">${m.percentage}%</td>
                                    </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    // Helpers
    function statCard(value, label, color) {
        return `<div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
            <span class="text-${color} text-2xl leading-none font-semibold">${value}</span>
            <span class="text-secondary-foreground text-sm">${label}</span>
        </div>`;
    }

    function summaryRow(label, count, color) {
        return `<div class="flex items-center justify-between py-1">
            <div class="flex items-center gap-2"><span class="inline-block w-2 h-2 rounded-full bg-${color}"></span><span class="text-sm">${label}</span></div>
            <span class="text-sm font-semibold text-mono">${count}</span>
        </div>`;
    }

    function moneyRow(label, amount, isNegative) {
        return `<div class="flex items-center justify-between py-1">
            <span class="text-sm text-secondary-foreground">${label}</span>
            <span class="text-sm font-semibold text-mono ${isNegative ? 'text-destructive' : ''}">${isNegative ? '-' : ''}${fmtMoney(Math.abs(amount))}</span>
        </div>`;
    }

    function statusBadge(status) {
        const m = {succeeded:'kt-badge-success',paid:'kt-badge-success',pending:'kt-badge-warning',failed:'kt-badge-destructive',canceled:'kt-badge-secondary',in_transit:'kt-badge-primary',refunded:'kt-badge-info'};
        return `<span class="kt-badge kt-badge-sm ${m[status]||'kt-badge-secondary'} kt-badge-outline">${ucfirst(status)}</span>`;
    }

    function ucfirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ') : ''; }
});
</script>
@endpush
