@extends('layouts.app')
@section('title', 'Transactions')

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Page Header --}}
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Transactions</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Monitor and manage all your payment transactions
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button class="kt-btn kt-btn-outline" id="exportCsvBtn">
                    <i class="ki-filled ki-exit-down"></i>
                    Export CSV
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">{{ number_format($stats['total_count']) }}</span>
                <span class="text-secondary-foreground text-sm">Total Transactions</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-success text-2xl leading-none font-semibold">{{ number_format($stats['succeeded']) }}</span>
                <span class="text-secondary-foreground text-sm">Succeeded</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-warning text-2xl leading-none font-semibold">{{ number_format($stats['pending']) }}</span>
                <span class="text-secondary-foreground text-sm">Pending</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-destructive text-2xl leading-none font-semibold">{{ number_format($stats['failed']) }}</span>
                <span class="text-secondary-foreground text-sm">Failed</span>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="kt-card kt-card-grid min-w-full">
            <div class="kt-card-header flex-wrap gap-2">
                <h3 class="kt-card-title text-sm">All Transactions</h3>
                <div class="flex flex-wrap gap-2 lg:gap-5">
                    <div class="flex">
                        <label class="kt-input kt-input-sm">
                            <i class="ki-filled ki-magnifier"></i>
                            <input id="searchInput" placeholder="Search transactions..." type="text" value=""/>
                        </label>
                    </div>
                    <div class="flex flex-wrap gap-2.5">
                        <select class="kt-select w-36" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="succeeded">Succeeded</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                            <option value="canceled">Canceled</option>
                        </select>
                        <select class="kt-select w-36" id="methodFilter">
                            <option value="">All Methods</option>
                            <option value="card">Card</option>
                            <option value="wallet">Wallet</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="terminal">Terminal</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="kt-card-table kt-scrollable-x-auto">
                <table class="kt-table" id="txnTable">
                    <thead>
                    <tr>
                        <th class="min-w-52 cursor-pointer" data-sort="reference">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Reference</span>
                                    <span class="kt-table-col-sort" id="sort-reference"></span>
                                </span>
                        </th>
                        <th class="min-w-24 text-end cursor-pointer" data-sort="amount">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Amount</span>
                                    <span class="kt-table-col-sort" id="sort-amount"></span>
                                </span>
                        </th>
                        <th class="min-w-24 text-end cursor-pointer" data-sort="status">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Status</span>
                                    <span class="kt-table-col-sort" id="sort-status"></span>
                                </span>
                        </th>
                        <th class="min-w-32 text-end cursor-pointer" data-sort="payment_method_type">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Method</span>
                                    <span class="kt-table-col-sort" id="sort-payment_method_type"></span>
                                </span>
                        </th>
                        <th class="min-w-48 text-end cursor-pointer" data-sort="receipt_email">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Customer</span>
                                    <span class="kt-table-col-sort" id="sort-receipt_email"></span>
                                </span>
                        </th>
                        <th class="min-w-32 text-end cursor-pointer" data-sort="created_at">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label">Date</span>
                                    <span class="kt-table-col-sort" id="sort-created_at"></span>
                                </span>
                        </th>
                    </tr>
                    </thead>
                    <tbody id="txnBody">
                    <tr><td colspan="6" class="text-center py-10 text-secondary-foreground">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                <div class="flex items-center gap-2 order-2 md:order-1">
                    Show
                    <select class="kt-select w-16" id="perPage">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    per page
                </div>
                <div class="flex items-center gap-4 order-1 md:order-2">
                    <span id="pageInfo" class="text-sm text-secondary-foreground"></span>
                    <div class="flex items-center gap-1" id="pagination"></div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const API_BASE = '/dashboard';

            let currentPage = 1, pageSize = 10, sortField = 'created_at', sortOrder = 'desc', searchQuery = '', searchTimeout = null;

            const tbody = document.getElementById('txnBody');
            const pageInfo = document.getElementById('pageInfo');
            const paginationEl = document.getElementById('pagination');
            const statusFilter = document.getElementById('statusFilter');
            const methodFilter = document.getElementById('methodFilter');
            const searchInput = document.getElementById('searchInput');
            const perPage = document.getElementById('perPage');

            async function fetchTransactions() {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-secondary-foreground">Loading...</td></tr>';

                const params = new URLSearchParams({ page: currentPage, size: pageSize, sortField, sortOrder });
                if (searchQuery) params.set('search', searchQuery);
                if (statusFilter.value) params.set('status', statusFilter.value);
                if (methodFilter.value) params.set('payment_method_type', methodFilter.value);

                try {
                    const res = await fetch(`${API_BASE}/transactions?${params}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await res.json();
                    renderTable(json.data || [], json.totalCount || 0, json.page || 1, json.lastPage || 1);
                } catch (err) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-destructive">Failed to load transactions</td></tr>';
                }
            }

            function renderTable(rows, total, page, lastPage) {
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-secondary-foreground"><div class="flex flex-col items-center gap-3"><i class="ki-filled ki-dollar text-3xl text-muted-foreground"></i><span>No transactions found</span></div></td></tr>';
                    pageInfo.textContent = '';
                    paginationEl.innerHTML = '';
                    return;
                }

                tbody.innerHTML = rows.map(r => `<tr>
            <td class="text-sm text-foreground font-normal"><a href="/dashboard/transactions/${r.id}" class="font-medium text-mono hover:text-primary">${r.reference || '-'}</a></td>
            <td class="text-sm text-foreground font-normal lg:text-end"><span class="font-semibold text-mono">${r.amount_formatted || fmtAmt(r.amount, r.currency)}</span></td>
            <td class="lg:text-end">${badge(r.status, r.status_badge)}</td>
            <td class="text-sm text-secondary-foreground font-normal lg:text-end">${method(r.payment_method_type)}</td>
            <td class="text-sm text-secondary-foreground font-normal lg:text-end">${r.receipt_email || '-'}</td>
            <td class="text-sm text-secondary-foreground font-normal lg:text-end">${fmtDate(r.created_at)}</td>
        </tr>`).join('');

                const start = (page - 1) * pageSize + 1;
                pageInfo.textContent = `${start}-${Math.min(page * pageSize, total)} of ${total}`;
                renderPagination(page, lastPage);
            }

            function renderPagination(page, last) {
                let html = `<button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline ${page <= 1 ? 'opacity-40 pointer-events-none' : ''}" data-page="${page-1}"><i class="ki-filled ki-black-left text-xs"></i></button>`;
                pgNums(page, last).forEach(p => {
                    html += p === '...' ? '<span class="px-2">...</span>' : `<button class="kt-btn kt-btn-sm kt-btn-icon ${p===page?'kt-btn-primary':'kt-btn-outline'}" data-page="${p}">${p}</button>`;
                });
                html += `<button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline ${page >= last ? 'opacity-40 pointer-events-none' : ''}" data-page="${page+1}"><i class="ki-filled ki-black-right text-xs"></i></button>`;
                paginationEl.innerHTML = html;
                paginationEl.querySelectorAll('[data-page]').forEach(b => b.addEventListener('click', () => { const p = +b.dataset.page; if (p >= 1 && p <= last) { currentPage = p; fetchTransactions(); } }));
            }

            function pgNums(c, l) {
                if (l <= 7) return Array.from({length:l},(_,i)=>i+1);
                if (c <= 3) return [1,2,3,4,'...',l];
                if (c >= l-2) return [1,'...',l-3,l-2,l-1,l];
                return [1,'...',c-1,c,c+1,'...',l];
            }

            function badge(status, type) {
                const m = {success:'kt-badge-success',warning:'kt-badge-warning',danger:'kt-badge-destructive',info:'kt-badge-info',secondary:'kt-badge-secondary',destructive:'kt-badge-destructive'};
                return `<div class="kt-badge kt-badge-sm ${m[type]||'kt-badge-secondary'} kt-badge-outline">${status?status[0].toUpperCase()+status.slice(1):'?'}</div>`;
            }

            function method(m) {
                const i = {card:'ki-credit-card',wallet:'ki-wallet',bank_transfer:'ki-bank',terminal:'ki-shop'};
                const label = m ? m.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase()) : 'N/A';
                return `<span class="flex items-center gap-1.5 justify-end"><i class="ki-filled ${i[m]||'ki-dollar'} text-sm"></i>${label}</span>`;
            }

            function fmtAmt(a, c) { const s = {USD:'$',EUR:'€',GBP:'£',ILS:'₪'}; return (s[c]||c+' ')+(a/100).toFixed(2); }

            function fmtDate(iso) {
                if (!iso) return '-';
                const d = new Date(iso);
                return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})+' '+d.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',hour12:false});
            }

            // Sort
            document.querySelectorAll('[data-sort]').forEach(th => th.addEventListener('click', () => {
                const f = th.dataset.sort;
                if (sortField === f) sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
                else { sortField = f; sortOrder = 'asc'; }
                document.querySelectorAll('[id^="sort-"]').forEach(s => s.className = 'kt-table-col-sort');
                const el = document.getElementById('sort-' + f);
                if (el) el.className = 'kt-table-col-sort ' + (sortOrder === 'asc' ? 'asc' : 'desc');
                currentPage = 1; fetchTransactions();
            }));

            // Search (debounced)
            searchInput.addEventListener('input', () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => { searchQuery = searchInput.value.trim(); currentPage = 1; fetchTransactions(); }, 400); });

            // Filters
            statusFilter.addEventListener('change', () => { currentPage = 1; fetchTransactions(); });
            methodFilter.addEventListener('change', () => { currentPage = 1; fetchTransactions(); });
            perPage.addEventListener('change', () => { pageSize = +perPage.value; currentPage = 1; fetchTransactions(); });

            // Export
            document.getElementById('exportCsvBtn').addEventListener('click', async () => {
                const params = new URLSearchParams({page:1,size:10000});
                if (statusFilter.value) params.set('status', statusFilter.value);
                if (methodFilter.value) params.set('payment_method_type', methodFilter.value);
                if (searchQuery) params.set('search', searchQuery);
                try {
                    const res = await fetch(`${API_BASE}/transactions?${params}`, { headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
                    const json = await res.json();
                    const rows = json.data || [];
                    if (!rows.length) { alert('No transactions to export'); return; }
                    const h = ['Reference','Amount','Currency','Status','Method','Customer Email','Description','Date'];
                    const csv = [h.join(','), ...rows.map(r => [`"${r.reference||''}"`, (r.amount/100).toFixed(2), r.currency, r.status, r.payment_method_type, `"${r.receipt_email||''}"`, `"${(r.description||'').replace(/"/g,'""')}"`, r.created_at].join(','))];
                    const blob = new Blob([csv.join('\n')], {type:'text/csv'});
                    const url = URL.createObjectURL(blob);
                    Object.assign(document.createElement('a'), {href:url, download:'transactions_'+new Date().toISOString().slice(0,10)+'.csv'}).click();
                    URL.revokeObjectURL(url);
                } catch(e) { alert('Export failed'); }
            });

            // Init
            fetchTransactions();
        });
    </script>
@endpush
