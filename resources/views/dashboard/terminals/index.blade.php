@extends('layouts.app')
@section('title', 'Terminals')

@section('content')
<div class="flex flex-col gap-5 lg:gap-7.5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center gap-5 justify-between">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-xl font-semibold leading-none text-mono">Terminals</h1>
            <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                Manage your POS terminals and readers
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button class="kt-btn kt-btn-light" id="importBtn">
                <i class="ki-filled ki-cloud-download"></i> Import from Stripe
            </button>
            <button class="kt-btn kt-btn-primary" id="registerBtn">
                <i class="ki-filled ki-plus"></i> Register New
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-2 p-3 rounded-md bg-success/10 text-sm" style="color:#22c55e">
        <i class="ki-filled ki-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-2 p-3 rounded-md bg-destructive/10 text-destructive text-sm">
        <i class="ki-filled ki-information-2"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="flex items-center flex-wrap gap-2 lg:gap-5">
        <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
            <span class="text-mono text-2xl leading-none font-semibold">{{ $stats['total'] }}</span>
            <span class="text-secondary-foreground text-sm">Total</span>
        </div>
        <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
            <span class="text-2xl leading-none font-semibold" style="color:#22c55e">{{ $stats['online'] }}</span>
            <span class="text-secondary-foreground text-sm">Online</span>
        </div>
        <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
            <span class="text-mono text-2xl leading-none font-semibold">{{ $stats['offline'] }}</span>
            <span class="text-secondary-foreground text-sm">Offline</span>
        </div>
        <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
            <span class="text-mono text-2xl leading-none font-semibold">{{ $stats['pairing'] }}</span>
            <span class="text-secondary-foreground text-sm">Pairing</span>
        </div>
    </div>

    {{-- Terminal List --}}
    @if($terminals->count() > 0)
    <div class="flex flex-col gap-3">
        @foreach($terminals as $terminal)
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10">
                        <i class="ki-filled ki-technology-4 text-primary text-xl"></i>
                    </div>
                    <div class="flex flex-col flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-foreground">{{ $terminal->name }}</span>
                            @if($terminal->status === 'online')
                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Online</span>
                            @elseif($terminal->status === 'pairing')
                            <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Pairing</span>
                            @else
                            <span class="kt-badge kt-badge-sm kt-badge-outline">Offline</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs text-secondary-foreground mt-1">
                            <span>SN: {{ $terminal->serial_number }}</span>
                            @if($terminal->location_name)
                            <span>• 📍 {{ $terminal->location_name }}</span>
                            @endif
                            @if($terminal->model)
                            <span>• {{ $terminal->model }}</span>
                            @endif
                            @if($terminal->last_seen_at)
                            <span>• Last seen {{ $terminal->last_seen_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                    @if($terminal->battery_level)
                    <span class="text-xs text-secondary-foreground">{{ $terminal->battery_level }}%</span>
                    @endif
                    <form method="POST" action="{{ route('dashboard.terminals.destroy', $terminal->id) }}"
                          onsubmit="return confirm('Remove this terminal?')">
                        @csrf @method('DELETE')
                        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" type="submit">
                            <i class="ki-filled ki-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="kt-card">
        <div class="kt-card-content p-10">
            <div class="flex flex-col items-center gap-3">
                <i class="ki-filled ki-technology-4 text-4xl text-muted-foreground"></i>
                <span class="text-sm font-medium text-foreground">No terminals yet</span>
                <span class="text-xs text-secondary-foreground">Import terminals from Stripe or register a new one</span>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Import from Stripe Modal --}}
<div class="kt-modal" id="importModal" data-kt-modal="true">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Import Terminals from Stripe</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body">
            <div class="flex flex-col gap-4">
                {{-- Step 1: Location ID --}}
                <div id="step1">
                    <div class="flex flex-col gap-1 mb-4">
                        <label class="text-sm font-medium text-foreground">Stripe Location ID</label>
                        <input class="kt-input" id="locationId" type="text" placeholder="tml_xxxxxxxxx">
                        <span class="text-xs text-secondary-foreground">Find this in Stripe Dashboard → Terminal → Locations</span>
                    </div>
                    <button class="kt-btn kt-btn-primary w-full" id="fetchReadersBtn">
                        <i class="ki-filled ki-cloud-download"></i> Fetch Terminals
                    </button>
                </div>

                {{-- Step 2: Select Readers --}}
                <div id="step2" class="hidden">
                    <div class="flex items-center gap-2 p-3 rounded-md bg-muted mb-4" id="locationInfo">
                        <i class="ki-filled ki-map-pin text-primary"></i>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-foreground" id="locationName"></span>
                            <span class="text-xs text-secondary-foreground" id="locationAddress"></span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 mb-4" id="readersList"></div>

                    <div id="noReadersMsg" class="hidden text-sm text-secondary-foreground text-center py-4">
                        No terminals found at this location
                    </div>
                </div>

                {{-- Loading --}}
                <div id="loadingState" class="hidden flex items-center justify-center py-8">
                    <div class="size-8 border-3 border-primary/30 border-t-primary rounded-full animate-spin"></div>
                </div>

                {{-- Error --}}
                <div id="importError" class="hidden text-sm text-destructive bg-destructive/10 rounded-md p-3"></div>
            </div>
        </div>
        <div class="kt-modal-footer">
            <button class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">Cancel</button>
            <button class="kt-btn kt-btn-primary hidden" id="importSelectedBtn">
                <i class="ki-filled ki-cloud-download"></i> Import Selected
            </button>
        </div>
    </div>
</div>

{{-- Register New Modal --}}
<div class="kt-modal" id="registerModal" data-kt-modal="true">
    <div class="kt-modal-content max-w-[450px] top-5 lg:top-[15%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Register Terminal</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('dashboard.terminals.store') }}">
            @csrf
            <div class="kt-modal-body">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Terminal Name *</label>
                        <input class="kt-input" name="name" required placeholder="e.g., Front Desk Reader">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Registration Code *</label>
                        <input class="kt-input" name="registration_code" required placeholder="e.g., sepia-cerulean-aqua">
                        <span class="text-xs text-secondary-foreground">Displayed on the terminal screen during setup</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Location Name</label>
                        <input class="kt-input" name="location_name" placeholder="e.g., Main Store">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Location Address</label>
                        <input class="kt-input" name="location_address" placeholder="e.g., 123 Main St, City">
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">Register</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const importBtn = document.querySelector('#importBtn');
    const registerBtn = document.querySelector('#registerBtn');
    const importModalEl = document.querySelector('#importModal');
    const registerModalEl = document.querySelector('#registerModal');

    let importModal = typeof KTModal !== 'undefined' ? KTModal.getInstance(importModalEl) : null;
    let registerModal = typeof KTModal !== 'undefined' ? KTModal.getInstance(registerModalEl) : null;

    let fetchedReaders = [];
    let locationData = {};

    importBtn.addEventListener('click', () => { if (importModal) importModal.show(); });
    registerBtn.addEventListener('click', () => { if (registerModal) registerModal.show(); });

    // Fetch readers
    document.querySelector('#fetchReadersBtn').addEventListener('click', async function() {
        const locationId = document.querySelector('#locationId').value.trim();
        if (!locationId) return;

        document.querySelector('#step1').classList.add('hidden');
        document.querySelector('#step2').classList.add('hidden');
        document.querySelector('#loadingState').classList.remove('hidden');
        document.querySelector('#importError').classList.add('hidden');
        document.querySelector('#importSelectedBtn').classList.add('hidden');

        try {
            const res = await fetch('{{ route("dashboard.terminals.fetch-readers") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ location_id: locationId }),
            });

            const data = await res.json();
            document.querySelector('#loadingState').classList.add('hidden');

            if (data.success) {
                fetchedReaders = data.readers;
                locationData = data.location;

                document.querySelector('#locationName').textContent = data.location.display_name;
                document.querySelector('#locationAddress').textContent = data.location.address;

                const list = document.querySelector('#readersList');
                list.innerHTML = '';

                if (data.readers.length === 0) {
                    document.querySelector('#noReadersMsg').classList.remove('hidden');
                    document.querySelector('#step2').classList.remove('hidden');
                    document.querySelector('#step1').classList.remove('hidden');
                    return;
                }

                document.querySelector('#noReadersMsg').classList.add('hidden');

                data.readers.forEach((reader, i) => {
                    const disabled = reader.already_imported;
                    const div = document.createElement('div');
                    div.className = 'flex items-center gap-3 p-3 rounded-lg border border-input' + (disabled ? ' opacity-50' : ' cursor-pointer hover:bg-muted/50');
                    div.innerHTML = `
                        <input type="checkbox" class="reader-check rounded" value="${i}" ${disabled ? 'disabled checked' : ''}>
                        <div class="flex items-center justify-center size-9 rounded-lg bg-primary/10">
                            <i class="ki-filled ki-technology-4 text-primary"></i>
                        </div>
                        <div class="flex flex-col flex-1">
                            <span class="text-sm font-medium text-foreground">${reader.label}</span>
                            <span class="text-xs text-secondary-foreground">SN: ${reader.serial_number} • ${reader.device_type}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            ${reader.status === 'online'
                                ? '<span class="size-2 rounded-full" style="background:#22c55e"></span><span class="text-xs" style="color:#22c55e">Online</span>'
                                : '<span class="size-2 rounded-full bg-secondary-foreground/30"></span><span class="text-xs text-secondary-foreground">Offline</span>'
                            }
                            ${disabled ? '<span class="text-xs text-secondary-foreground ml-2">Already imported</span>' : ''}
                        </div>
                    `;

                    if (!disabled) {
                        div.addEventListener('click', (e) => {
                            if (e.target.type !== 'checkbox') {
                                const cb = div.querySelector('.reader-check');
                                cb.checked = !cb.checked;
                            }
                            updateImportBtn();
                        });
                    }

                    list.appendChild(div);
                });

                document.querySelector('#step1').classList.remove('hidden');
                document.querySelector('#step2').classList.remove('hidden');
                updateImportBtn();
            } else {
                document.querySelector('#step1').classList.remove('hidden');
                document.querySelector('#importError').textContent = data.message;
                document.querySelector('#importError').classList.remove('hidden');
            }
        } catch (e) {
            document.querySelector('#loadingState').classList.add('hidden');
            document.querySelector('#step1').classList.remove('hidden');
            document.querySelector('#importError').textContent = e.message;
            document.querySelector('#importError').classList.remove('hidden');
        }
    });

    function updateImportBtn() {
        const checked = document.querySelectorAll('.reader-check:checked:not(:disabled)');
        const btn = document.querySelector('#importSelectedBtn');
        if (checked.length > 0) {
            btn.classList.remove('hidden');
            btn.textContent = `Import ${checked.length} Terminal${checked.length > 1 ? 's' : ''}`;
        } else {
            btn.classList.add('hidden');
        }
    }

    // Import selected
    document.querySelector('#importSelectedBtn').addEventListener('click', async function() {
        const checked = document.querySelectorAll('.reader-check:checked:not(:disabled)');
        const selected = [];
        checked.forEach(cb => selected.push(fetchedReaders[cb.value]));

        if (selected.length === 0) return;

        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("dashboard.terminals.import") }}';
        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;

        selected.forEach((reader, i) => {
            Object.keys(reader).forEach(key => {
                form.innerHTML += `<input type="hidden" name="readers[${i}][${key}]" value="${reader[key]}">`;
            });
        });

        form.innerHTML += `<input type="hidden" name="location_name" value="${locationData.display_name}">`;
        form.innerHTML += `<input type="hidden" name="location_address" value="${locationData.address}">`;

        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endpush
