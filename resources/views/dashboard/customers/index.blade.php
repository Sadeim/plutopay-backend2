@extends('layouts.app')
@section('title', 'Customers')

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Page Header --}}
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Customers</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Manage your customer database
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button class="kt-btn kt-btn-primary" id="addCustomerBtn">
                    <i class="ki-filled ki-plus"></i>
                    Add Customer
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">{{ number_format($stats['total_count']) }}</span>
                <span class="text-secondary-foreground text-sm">Total Customers</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-primary text-2xl leading-none font-semibold">{{ number_format($stats['with_email']) }}</span>
                <span class="text-secondary-foreground text-sm">With Email</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-success text-2xl leading-none font-semibold">{{ number_format($stats['with_phone']) }}</span>
                <span class="text-secondary-foreground text-sm">With Phone</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-info text-2xl leading-none font-semibold">{{ number_format($stats['recent_30d']) }}</span>
                <span class="text-secondary-foreground text-sm">Last 30 Days</span>
            </div>
        </div>

        {{-- Customers DataTable --}}
        <div class="kt-card kt-card-grid min-w-full">
            <div class="kt-card-header flex-wrap gap-2">
                <h3 class="kt-card-title text-sm">All Customers</h3>
                <div class="flex flex-wrap gap-2 lg:gap-5">
                    <div class="flex">
                        <label class="kt-input kt-input-sm">
                            <i class="ki-filled ki-magnifier"></i>
                            <input data-kt-datatable-search="#customersTable" placeholder="Search customers..." type="text" value=""/>
                        </label>
                    </div>
                </div>
            </div>
            <div class="kt-card-content">
                <div id="customersTable" data-kt-datatable="false" data-kt-datatable-page-size="10" class="grid">
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table table-auto kt-table-border" data-kt-datatable-table="true">
                            <thead>
                            <tr>
                                <th class="min-w-[180px]">
                                    <span class="kt-table-col"><span class="kt-table-col-label">Name</span><span class="kt-table-col-sort"></span></span>
                                </th>
                                <th class="min-w-[200px]">
                                    <span class="kt-table-col"><span class="kt-table-col-label">Email</span><span class="kt-table-col-sort"></span></span>
                                </th>
                                <th class="min-w-[140px]">
                                    <span class="kt-table-col"><span class="kt-table-col-label">Phone</span><span class="kt-table-col-sort"></span></span>
                                </th>
                                <th class="min-w-[100px]">
                                    <span class="kt-table-col"><span class="kt-table-col-label">Transactions</span><span class="kt-table-col-sort"></span></span>
                                </th>
                                <th class="min-w-[120px]">
                                    <span class="kt-table-col"><span class="kt-table-col-label">Total Spent</span><span class="kt-table-col-sort"></span></span>
                                </th>
                                <th class="min-w-[140px]">
                                    <span class="kt-table-col"><span class="kt-table-col-label">Created</span><span class="kt-table-col-sort"></span></span>
                                </th>
                                <th class="w-[80px]">Actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                        <div class="flex items-center gap-2 order-2 md:order-1">
                            Show
                            <select class="kt-select w-16" data-kt-datatable-size="true" name="perpage"></select>
                            per page
                        </div>
                        <div class="flex items-center gap-4 order-1 md:order-2">
                            <span data-kt-datatable-info="true"></span>
                            <div class="kt-datatable-pagination" data-kt-datatable-pagination="true"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add/Edit Customer Modal --}}
    <div class="kt-modal" id="customerModal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[600px] top-5 lg:top-[15%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title" id="modalTitle">Add Customer</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body">
                <div class="flex flex-col gap-4">
                    <input type="hidden" id="customerId" value="">

                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Name</label>
                        <input class="kt-input" id="customerName" type="text" placeholder="Full name">
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">Email</label>
                            <input class="kt-input" id="customerEmail" type="email" placeholder="email@example.com">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">Phone</label>
                            <input class="kt-input" id="customerPhone" type="text" placeholder="+1234567890">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Address</label>
                        <input class="kt-input" id="customerAddress" type="text" placeholder="Street address">
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">City</label>
                            <input class="kt-input" id="customerCity" type="text" placeholder="City">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">State</label>
                            <input class="kt-input" id="customerState" type="text" placeholder="State">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-foreground">Country</label>
                            <input class="kt-input" id="customerCountry" type="text" placeholder="US" maxlength="2">
                        </div>
                    </div>

                    <div id="modalError" class="hidden text-sm text-destructive bg-destructive/10 rounded-md p-3"></div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">Cancel</button>
                <button class="kt-btn kt-btn-primary" id="saveCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="kt-modal" id="deleteModal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[400px] top-5 lg:top-[20%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Delete Customer</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body">
                <p class="text-sm text-secondary-foreground">Are you sure you want to delete <strong id="deleteCustomerName"></strong>? This action cannot be undone.</p>
                <input type="hidden" id="deleteCustomerId" value="">
            </div>
            <div class="kt-modal-footer">
                <button class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">Cancel</button>
                <button class="kt-btn kt-btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const API_BASE = '/api/v1';
            const API_KEY = '{{ $apiKey }}';
            const AUTH_HEADER = { 'Authorization': 'Bearer ' + API_KEY, 'Accept': 'application/json', 'Content-Type': 'application/json' };

            // Format date
            function formatDate(iso) {
                if (!iso) return '-';
                const d = new Date(iso);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            }

            // Init KTDataTable
            const tableEl = document.querySelector('#customersTable');
            let dataTable;

            if (typeof KTDataTable !== 'undefined' && tableEl) {
                dataTable = new KTDataTable(tableEl, {
                    apiEndpoint: API_BASE + '/customers',
                    requestMethod: 'GET',
                    requestHeaders: AUTH_HEADER,
                    pageSize: 10,
                    pageSizes: [5, 10, 25, 50],
                    stateSave: true,
                    stateNamespace: 'plutopay_customers',
                    info: '{start}-{end} of {total}',
                    infoEmpty: '<div class="flex flex-col items-center gap-3 py-10"><i class="ki-filled ki-people text-3xl text-muted-foreground"></i><span>No customers yet</span><span class="text-xs text-muted-foreground">Add your first customer to get started</span></div>',
                    mapResponse: function(response) {
                        return { data: response.data || [], totalCount: response.totalCount || 0 };
                    },
                    columns: {
                        'name': {
                            render: function(data, row) {
                                const initials = (data || '?').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                                return '<div class="flex items-center gap-2.5">' +
                                    '<div class="flex items-center justify-center size-9 rounded-full bg-primary/10 text-primary text-xs font-semibold">' + initials + '</div>' +
                                    '<div class="flex flex-col"><span class="text-sm font-medium text-foreground">' + (data || '-') + '</span>' +
                                    (row.country ? '<span class="text-xs text-secondary-foreground">' + row.country + '</span>' : '') +
                                    '</div></div>';
                            }
                        },
                        'email': {
                            render: function(data) {
                                return '<span class="text-sm text-secondary-foreground">' + (data || '-') + '</span>';
                            }
                        },
                        'phone': {
                            render: function(data) {
                                return '<span class="text-sm text-secondary-foreground">' + (data || '-') + '</span>';
                            }
                        },
                        'transactions_count': {
                            render: function(data) {
                                return '<span class="text-sm text-mono font-medium">' + (data || 0) + '</span>';
                            }
                        },
                        'total_spent_formatted': {
                            render: function(data) {
                                return '<span class="text-sm text-mono font-medium">' + (data || '$0.00') + '</span>';
                            }
                        },
                        'created_at': {
                            render: function(data) {
                                return '<span class="text-sm text-secondary-foreground">' + formatDate(data) + '</span>';
                            }
                        },
                        // Actions column (index 6)
                        'id': {
                            render: function(data, row) {
                                return '<div class="flex items-center gap-1">' +
                                    '<button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light edit-btn" data-id="' + row.id + '"><i class="ki-filled ki-pencil text-sm"></i></button>' +
                                    '<button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light delete-btn" data-id="' + row.id + '" data-name="' + (row.name || 'this customer') + '"><i class="ki-filled ki-trash text-sm text-destructive"></i></button>' +
                                    '</div>';
                            }
                        },
                    },
                });
            }

            // Modal instances
            const customerModalEl = document.querySelector('#customerModal');
            const deleteModalEl = document.querySelector('#deleteModal');
            let customerModal, deleteModal;

            if (typeof KTModal !== 'undefined') {
                customerModal = KTModal.getInstance(customerModalEl);
                deleteModal = KTModal.getInstance(deleteModalEl);
            }

            // Add Customer button
            document.querySelector('#addCustomerBtn').addEventListener('click', function() {
                resetForm();
                document.querySelector('#modalTitle').textContent = 'Add Customer';
                if (customerModal) customerModal.show();
            });

            // Save Customer
            document.querySelector('#saveCustomerBtn').addEventListener('click', async function() {
                const id = document.querySelector('#customerId').value;
                const body = {
                    name: document.querySelector('#customerName').value,
                    email: document.querySelector('#customerEmail').value || undefined,
                    phone: document.querySelector('#customerPhone').value || undefined,
                    address_line1: document.querySelector('#customerAddress').value || undefined,
                    city: document.querySelector('#customerCity').value || undefined,
                    state: document.querySelector('#customerState').value || undefined,
                    country: document.querySelector('#customerCountry').value || undefined,
                };

                // Remove undefined values
                Object.keys(body).forEach(k => body[k] === undefined && delete body[k]);

                const errorEl = document.querySelector('#modalError');
                errorEl.classList.add('hidden');

                try {
                    const url = id ? API_BASE + '/customers/' + id : API_BASE + '/customers';
                    const method = id ? 'PUT' : 'POST';

                    const res = await fetch(url, { method, headers: AUTH_HEADER, body: JSON.stringify(body) });
                    const data = await res.json();

                    if (!res.ok) {
                        const msg = data.message || data.error?.message || 'Something went wrong';
                        errorEl.textContent = msg;
                        errorEl.classList.remove('hidden');
                        return;
                    }

                    if (customerModal) customerModal.hide();
                    if (dataTable) dataTable.reload();

                    // Reload page to update stats
                    window.location.reload();
                } catch (err) {
                    errorEl.textContent = 'Network error. Please try again.';
                    errorEl.classList.remove('hidden');
                }
            });

            // Edit button (delegated)
            document.querySelector('#customersTable').addEventListener('click', async function(e) {
                const editBtn = e.target.closest('.edit-btn');
                const deleteBtn = e.target.closest('.delete-btn');

                if (editBtn) {
                    const id = editBtn.dataset.id;
                    try {
                        const res = await fetch(API_BASE + '/customers/' + id, { headers: AUTH_HEADER });
                        const data = await res.json();
                        const c = data.data || data;

                        document.querySelector('#customerId').value = c.id;
                        document.querySelector('#customerName').value = c.name || '';
                        document.querySelector('#customerEmail').value = c.email || '';
                        document.querySelector('#customerPhone').value = c.phone || '';
                        document.querySelector('#customerAddress').value = c.address_line1 || '';
                        document.querySelector('#customerCity').value = c.city || '';
                        document.querySelector('#customerState').value = c.state || '';
                        document.querySelector('#customerCountry').value = c.country || '';
                        document.querySelector('#modalTitle').textContent = 'Edit Customer';
                        document.querySelector('#modalError').classList.add('hidden');

                        if (customerModal) customerModal.show();
                    } catch (err) {
                        console.error('Failed to load customer:', err);
                    }
                }

                if (deleteBtn) {
                    document.querySelector('#deleteCustomerId').value = deleteBtn.dataset.id;
                    document.querySelector('#deleteCustomerName').textContent = deleteBtn.dataset.name;
                    if (deleteModal) deleteModal.show();
                }
            });

            // Confirm Delete
            document.querySelector('#confirmDeleteBtn').addEventListener('click', async function() {
                const id = document.querySelector('#deleteCustomerId').value;
                try {
                    await fetch(API_BASE + '/customers/' + id, { method: 'DELETE', headers: AUTH_HEADER });
                    if (deleteModal) deleteModal.hide();
                    if (dataTable) dataTable.reload();
                    window.location.reload();
                } catch (err) {
                    console.error('Delete failed:', err);
                }
            });

            function resetForm() {
                document.querySelector('#customerId').value = '';
                document.querySelector('#customerName').value = '';
                document.querySelector('#customerEmail').value = '';
                document.querySelector('#customerPhone').value = '';
                document.querySelector('#customerAddress').value = '';
                document.querySelector('#customerCity').value = '';
                document.querySelector('#customerState').value = '';
                document.querySelector('#customerCountry').value = '';
                document.querySelector('#modalError').classList.add('hidden');
            }
        });
    </script>
@endpush
