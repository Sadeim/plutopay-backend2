@extends('layouts.app')
@section('title', 'Terminals')

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Page Header --}}
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Terminals</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Manage your POS terminals and readers
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button class="kt-btn kt-btn-primary" id="registerTerminalBtn">
                    <i class="ki-filled ki-plus"></i>
                    Register Terminal
                </button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="flex items-center gap-2 p-3 rounded-md bg-success/10 text-success text-sm">
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
                <span class="text-success text-2xl leading-none font-semibold">{{ $stats['online'] }}</span>
                <span class="text-secondary-foreground text-sm">Online</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-secondary-foreground text-2xl leading-none font-semibold">{{ $stats['offline'] }}</span>
                <span class="text-secondary-foreground text-sm">Offline</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-warning text-2xl leading-none font-semibold">{{ $stats['pairing'] }}</span>
                <span class="text-secondary-foreground text-sm">Pairing</span>
            </div>
        </div>

        {{-- Terminals List --}}
        <div class="flex flex-col gap-4">
            @forelse($terminals as $terminal)
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10">
                                <i class="ki-filled ki-technology-4 text-primary text-2xl"></i>
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-foreground">{{ $terminal->name }}</span>
                                    @if($terminal->status === 'online')
                                    <div class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">
                                        <span class="size-1.5 rounded-full bg-success"></span> Online
                                    </div>
                                    @elseif($terminal->status === 'pairing')
                                    <div class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Pairing</div>
                                    @else
                                    <div class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">Offline</div>
                                    @endif
                                    @if($terminal->is_test)
                                    <div class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">Test</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-xs text-secondary-foreground">
                                    @if($terminal->serial_number)
                                    <span>SN: {{ $terminal->serial_number }}</span>
                                    <span>•</span>
                                    @endif
                                    @if($terminal->location_name)
                                    <span><i class="ki-filled ki-geolocation text-xs"></i> {{ $terminal->location_name }}</span>
                                    <span>•</span>
                                    @endif
                                    <span>Added {{ $terminal->created_at->format('M d, Y') }}</span>
                                    @if($terminal->last_seen_at)
                                    <span>•</span>
                                    <span>Last seen {{ $terminal->last_seen_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            @if($terminal->battery_level !== null)
                            <div class="flex items-center gap-1 text-xs text-secondary-foreground">
                                <i class="ki-filled ki-battery-{{ $terminal->battery_level > 50 ? 'full' : ($terminal->battery_level > 20 ? 'half' : 'low') }}"></i>
                                {{ $terminal->battery_level }}%
                            </div>
                            @endif

                            @if($terminal->firmware_version)
                            <span class="text-xs text-secondary-foreground">v{{ $terminal->firmware_version }}</span>
                            @endif

                            <form method="POST" action="{{ route('dashboard.terminals.destroy', $terminal->id) }}" class="inline"
                                  onsubmit="return confirm('Remove this terminal?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline kt-btn-danger">
                                    <i class="ki-filled ki-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="kt-card">
                <div class="kt-card-content p-10">
                    <div class="flex flex-col items-center gap-3">
                        <i class="ki-filled ki-technology-4 text-3xl text-muted-foreground"></i>
                        <span class="text-sm text-secondary-foreground">No terminals registered</span>
                        <span class="text-xs text-muted-foreground">Register a Stripe Terminal reader to start accepting in-person payments</span>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        {{-- Documentation --}}
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-info text-lg mt-0.5"></i>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium text-foreground">Terminal Setup</span>
                        <p class="text-xs text-secondary-foreground leading-relaxed">
                            To register a terminal, you need the <strong>registration code</strong> displayed on the terminal screen.
                            Connect the terminal to WiFi, then enter the code here. The terminal will automatically pair with your account.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Register Terminal Modal --}}
    <div class="kt-modal" id="registerModal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[500px] top-5 lg:top-[15%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Register Terminal</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Terminal Name *</label>
                        <input class="kt-input" id="terminalName" type="text" placeholder="e.g., Front Desk Reader">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Registration Code *</label>
                        <input class="kt-input" id="terminalCode" type="text" placeholder="e.g., sepia-cerulean-aqua">
                        <span class="text-xs text-secondary-foreground">Displayed on the terminal screen during setup</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Location Name</label>
                        <input class="kt-input" id="terminalLocation" type="text" placeholder="e.g., Main Store">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Location Address</label>
                        <input class="kt-input" id="terminalAddress" type="text" placeholder="e.g., 123 Main St, City">
                    </div>
                    <div id="registerError" class="hidden text-sm text-destructive bg-destructive/10 rounded-md p-3"></div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">Cancel</button>
                <button class="kt-btn kt-btn-primary" id="submitRegister">Register</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerBtn = document.querySelector('#registerTerminalBtn');
    const modalEl = document.querySelector('#registerModal');
    let modal;

    if (typeof KTModal !== 'undefined') {
        modal = KTModal.getInstance(modalEl);
    }

    registerBtn.addEventListener('click', function() {
        document.querySelector('#terminalName').value = '';
        document.querySelector('#terminalCode').value = '';
        document.querySelector('#terminalLocation').value = '';
        document.querySelector('#terminalAddress').value = '';
        document.querySelector('#registerError').classList.add('hidden');
        if (modal) modal.show();
    });

    document.querySelector('#submitRegister').addEventListener('click', function() {
        const name = document.querySelector('#terminalName').value.trim();
        const code = document.querySelector('#terminalCode').value.trim();
        const location = document.querySelector('#terminalLocation').value.trim();
        const address = document.querySelector('#terminalAddress').value.trim();
        const errorEl = document.querySelector('#registerError');

        if (!name || !code) {
            errorEl.textContent = 'Name and registration code are required.';
            errorEl.classList.remove('hidden');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("dashboard.terminals.store") }}';

        const fields = {
            '_token': '{{ csrf_token() }}',
            'name': name,
            'registration_code': code,
            'location_name': location,
            'location_address': address
        };

        for (const [key, value] of Object.entries(fields)) {
            if (value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
        }

        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endpush
