@extends('layouts.app')
@section('title', 'Webhooks')

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Page Header --}}
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Webhooks</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Manage webhook endpoints for real-time event notifications
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button class="kt-btn kt-btn-primary" id="addEndpointBtn">
                    <i class="ki-filled ki-plus"></i>
                    Add Endpoint
                </button>
            </div>
        </div>

        {{-- New Secret Alert --}}
        @if(session('new_secret'))
        <div class="kt-card border-success/30 bg-success/5">
            <div class="kt-card-content p-5">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2">
                        <i class="ki-filled ki-shield-tick text-success text-lg"></i>
                        <span class="text-sm font-semibold text-success">Signing Secret</span>
                    </div>
                    <p class="text-sm text-secondary-foreground">Use this secret to verify webhook signatures. It won't be shown again.</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 bg-background border border-input rounded-md px-3 py-2 text-sm font-mono text-foreground select-all" id="newSecretValue">{{ session('new_secret') }}</code>
                        <button class="kt-btn kt-btn-sm kt-btn-outline copy-btn" data-value="{{ session('new_secret') }}">
                            <i class="ki-filled ki-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Flash Messages --}}
        @if(session('success') && !session('new_secret'))
        <div class="flex items-center gap-2 p-3 rounded-md bg-success/10 text-success text-sm">
            <i class="ki-filled ki-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        {{-- Stats --}}
        <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">{{ $stats['total_endpoints'] }}</span>
                <span class="text-secondary-foreground text-sm">Endpoints</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-success text-2xl leading-none font-semibold">{{ $stats['active_endpoints'] }}</span>
                <span class="text-secondary-foreground text-sm">Active</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-primary text-2xl leading-none font-semibold">{{ $stats['total_events'] }}</span>
                <span class="text-secondary-foreground text-sm">Events Sent</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-destructive text-2xl leading-none font-semibold">{{ $stats['failed_events'] }}</span>
                <span class="text-secondary-foreground text-sm">Failed</span>
            </div>
        </div>

        {{-- Endpoints List --}}
        <div class="flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-foreground">Endpoints</h3>

            @forelse($endpoints as $endpoint)
            <div class="kt-card {{ $endpoint->status !== 'active' ? 'opacity-60' : '' }}">
                <div class="kt-card-content p-5">
                    <div class="flex flex-col gap-3">
                        {{-- Header --}}
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-10 rounded-lg {{ $endpoint->status === 'active' ? 'bg-success/10' : 'bg-secondary/10' }}">
                                    <i class="ki-filled ki-satellite {{ $endpoint->status === 'active' ? 'text-success' : 'text-secondary-foreground' }} text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-0.5">
                                    <div class="flex items-center gap-2">
                                        <code class="text-sm font-mono font-medium text-foreground">{{ $endpoint->url }}</code>
                                        <div class="kt-badge kt-badge-sm {{ $endpoint->status === 'active' ? 'kt-badge-success' : 'kt-badge-secondary' }} kt-badge-outline">
                                            {{ ucfirst($endpoint->status) }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-secondary-foreground">
                                        @if($endpoint->description)
                                        <span>{{ $endpoint->description }}</span>
                                        <span>•</span>
                                        @endif
                                        <span>{{ $endpoint->deliveries_count ?? 0 }} deliveries</span>
                                        @if($endpoint->last_success_at)
                                        <span>•</span>
                                        <span>Last success {{ $endpoint->last_success_at->diffForHumans() }}</span>
                                        @endif
                                        @if($endpoint->failure_count > 0)
                                        <span>•</span>
                                        <span class="text-destructive">{{ $endpoint->failure_count }} failures</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('dashboard.webhooks.test', $endpoint->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline" title="Send test event">
                                        <i class="ki-filled ki-send"></i> Test
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('dashboard.webhooks.toggle', $endpoint->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline {{ $endpoint->status === 'active' ? '' : 'kt-btn-success' }}">
                                        <i class="ki-filled {{ $endpoint->status === 'active' ? 'ki-cross-circle' : 'ki-check-circle' }}"></i>
                                        {{ $endpoint->status === 'active' ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('dashboard.webhooks.destroy', $endpoint->id) }}" class="inline"
                                      onsubmit="return confirm('Delete this endpoint? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline kt-btn-danger">
                                        <i class="ki-filled ki-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Subscribed Events --}}
                        @if(!empty($endpoint->events))
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($endpoint->events as $event)
                            <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline font-mono">{{ $event }}</span>
                            @endforeach
                        </div>
                        @else
                        <span class="text-xs text-secondary-foreground italic">Subscribed to all events</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="kt-card">
                <div class="kt-card-content p-10">
                    <div class="flex flex-col items-center gap-3">
                        <i class="ki-filled ki-satellite text-3xl text-muted-foreground"></i>
                        <span class="text-sm text-secondary-foreground">No webhook endpoints</span>
                        <span class="text-xs text-muted-foreground">Add an endpoint to receive real-time event notifications</span>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        {{-- Recent Events --}}
        @if($recentEvents->count() > 0)
        <div class="kt-card kt-card-grid">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Recent Events</h3>
            </div>
            <div class="kt-card-content">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[180px]">Event Type</th>
                                <th class="min-w-[100px]">Status</th>
                                <th class="min-w-[100px]">Mode</th>
                                <th class="min-w-[160px]">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEvents as $event)
                            <tr>
                                <td><code class="text-sm font-mono">{{ $event->type }}</code></td>
                                <td>
                                    @if($event->status === 'delivered')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Delivered</span>
                                    @elseif($event->status === 'failed')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Failed</span>
                                    @else
                                    <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">{{ ucfirst($event->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($event->is_test)
                                    <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">Test</span>
                                    @else
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Live</span>
                                    @endif
                                </td>
                                <td class="text-sm text-secondary-foreground">{{ $event->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Documentation --}}
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-info text-lg mt-0.5"></i>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium text-foreground">Webhook Signatures</span>
                        <p class="text-xs text-secondary-foreground leading-relaxed">
                            All webhook payloads are signed with your endpoint's secret using HMAC-SHA256.
                            Verify the <code class="bg-muted px-1 rounded">X-PlutoPay-Signature</code> header to ensure authenticity.
                            Failed deliveries are retried with exponential backoff (1min, 5min, 30min, 2hr, 24hr).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Endpoint Modal --}}
    <div class="kt-modal" id="endpointModal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Add Webhook Endpoint</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Endpoint URL</label>
                        <input class="kt-input" id="endpointUrl" type="url" placeholder="https://your-server.com/webhooks/plutopay">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Description (optional)</label>
                        <input class="kt-input" id="endpointDesc" type="text" placeholder="e.g., Production webhook handler">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Events to subscribe</label>
                        <p class="text-xs text-secondary-foreground mb-2">Leave all unchecked to receive all events.</p>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 max-h-[200px] overflow-y-auto p-1">
                            @foreach($eventTypes as $type)
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="checkbox" class="kt-checkbox event-checkbox" value="{{ $type }}">
                                <code class="text-xs font-mono">{{ $type }}</code>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div id="endpointError" class="hidden text-sm text-destructive bg-destructive/10 rounded-md p-3"></div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">Cancel</button>
                <button class="kt-btn kt-btn-primary" id="submitEndpoint">Create Endpoint</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Endpoint Modal
    const addBtn = document.querySelector('#addEndpointBtn');
    const modalEl = document.querySelector('#endpointModal');
    let modal;

    if (typeof KTModal !== 'undefined') {
        modal = KTModal.getInstance(modalEl);
    }

    addBtn.addEventListener('click', function() {
        document.querySelector('#endpointUrl').value = '';
        document.querySelector('#endpointDesc').value = '';
        document.querySelectorAll('.event-checkbox').forEach(cb => cb.checked = false);
        document.querySelector('#endpointError').classList.add('hidden');
        if (modal) modal.show();
    });

    // Submit
    document.querySelector('#submitEndpoint').addEventListener('click', function() {
        const url = document.querySelector('#endpointUrl').value.trim();
        const desc = document.querySelector('#endpointDesc').value.trim();
        const errorEl = document.querySelector('#endpointError');
        const events = [];

        document.querySelectorAll('.event-checkbox:checked').forEach(cb => events.push(cb.value));

        if (!url) {
            errorEl.textContent = 'Please enter an endpoint URL.';
            errorEl.classList.remove('hidden');
            return;
        }

        if (!url.startsWith('https://')) {
            errorEl.textContent = 'Endpoint URL must use HTTPS.';
            errorEl.classList.remove('hidden');
            return;
        }

        // Submit via hidden form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("dashboard.webhooks.store") }}';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const urlInput = document.createElement('input');
        urlInput.type = 'hidden';
        urlInput.name = 'url';
        urlInput.value = url;
        form.appendChild(urlInput);

        if (desc) {
            const descInput = document.createElement('input');
            descInput.type = 'hidden';
            descInput.name = 'description';
            descInput.value = desc;
            form.appendChild(descInput);
        }

        events.forEach(evt => {
            const evtInput = document.createElement('input');
            evtInput.type = 'hidden';
            evtInput.name = 'events[]';
            evtInput.value = evt;
            form.appendChild(evtInput);
        });

        document.body.appendChild(form);
        form.submit();
    });

    // Copy buttons
    document.querySelectorAll('.copy-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const val = this.dataset.value;
            navigator.clipboard.writeText(val).then(() => {
                const original = this.innerHTML;
                this.innerHTML = '<i class="ki-filled ki-check"></i> Copied!';
                setTimeout(() => { this.innerHTML = original; }, 2000);
            });
        });
    });
});
</script>
@endpush
