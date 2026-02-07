@extends('layouts.app')
@section('title', 'API Keys')

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Page Header --}}
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">API Keys</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Manage your API keys for integration
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button class="kt-btn kt-btn-primary" id="createKeyBtn">
                    <i class="ki-filled ki-plus"></i>
                    Create API Key
                </button>
            </div>
        </div>

        {{-- New Key Alert (shown only once after creation) --}}
        @if(session('new_key'))
        <div class="kt-card border-success/30 bg-success/5">
            <div class="kt-card-content p-5">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2">
                        <i class="ki-filled ki-shield-tick text-success text-lg"></i>
                        <span class="text-sm font-semibold text-success">New API Key Created</span>
                    </div>
                    <p class="text-sm text-secondary-foreground">Copy your API key now. For security reasons, it won't be displayed again.</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 bg-background border border-input rounded-md px-3 py-2 text-sm font-mono text-foreground select-all" id="newKeyValue">{{ session('new_key') }}</code>
                        <button class="kt-btn kt-btn-sm kt-btn-outline copy-key-btn" data-key="{{ session('new_key') }}">
                            <i class="ki-filled ki-copy"></i>
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Success/Error Messages --}}
        @if(session('success') && !session('new_key'))
        <div class="flex items-center gap-2 p-3 rounded-md bg-success/10 text-success text-sm">
            <i class="ki-filled ki-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center gap-2 p-3 rounded-md bg-destructive/10 text-destructive text-sm">
            <i class="ki-filled ki-information-2"></i>
            {{ session('error') }}
        </div>
        @endif

        {{-- Stats --}}
        <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">{{ $apiKeys->count() }}</span>
                <span class="text-secondary-foreground text-sm">Total Keys</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-success text-2xl leading-none font-semibold">{{ $apiKeys->filter(fn($k) => $k->isValid())->count() }}</span>
                <span class="text-secondary-foreground text-sm">Active</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-destructive text-2xl leading-none font-semibold">{{ $apiKeys->filter(fn($k) => $k->isRevoked())->count() }}</span>
                <span class="text-secondary-foreground text-sm">Revoked</span>
            </div>
        </div>

        {{-- API Keys List --}}
        <div class="flex flex-col gap-4">
            @forelse($apiKeys as $key)
            <div class="kt-card {{ $key->isRevoked() ? 'opacity-60' : '' }}">
                <div class="kt-card-content p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        {{-- Key Info --}}
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center size-10 rounded-lg {{ $key->type === 'secret' ? 'bg-warning/10' : 'bg-primary/10' }}">
                                <i class="ki-filled {{ $key->type === 'secret' ? 'ki-lock text-warning' : 'ki-key text-primary' }} text-xl"></i>
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-foreground">{{ $key->name ?: 'Unnamed Key' }}</span>
                                    <div class="kt-badge kt-badge-sm {{ $key->type === 'secret' ? 'kt-badge-warning' : 'kt-badge-primary' }} kt-badge-outline">
                                        {{ ucfirst($key->type) }}
                                    </div>
                                    @if($key->is_test)
                                    <div class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">Test</div>
                                    @else
                                    <div class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Live</div>
                                    @endif
                                    @if($key->isRevoked())
                                    <div class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Revoked</div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-xs text-secondary-foreground">
                                    <code class="font-mono">{{ $key->type === 'secret' ? 'sk_' : 'pk_' }}****{{ $key->key_last_four }}</code>
                                    <span>•</span>
                                    <span>Created {{ $key->created_at->format('M d, Y') }}</span>
                                    @if($key->last_used_at)
                                    <span>•</span>
                                    <span>Last used {{ $key->last_used_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2">
                            @if(!$key->isRevoked())
                            <form method="POST" action="{{ route('dashboard.api-keys.revoke', $key->id) }}" class="inline"
                                  onsubmit="return confirm('Are you sure you want to revoke this key? This cannot be undone.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline kt-btn-danger">
                                    <i class="ki-filled ki-cross-circle"></i>
                                    Revoke
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-secondary-foreground">Revoked {{ $key->revoked_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="kt-card">
                <div class="kt-card-content p-10">
                    <div class="flex flex-col items-center gap-3">
                        <i class="ki-filled ki-key text-3xl text-muted-foreground"></i>
                        <span class="text-sm text-secondary-foreground">No API keys yet</span>
                        <span class="text-xs text-muted-foreground">Create your first API key to start integrating</span>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        {{-- Documentation Card --}}
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-info text-lg mt-0.5"></i>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium text-foreground">API Key Types</span>
                        <p class="text-xs text-secondary-foreground leading-relaxed">
                            <strong>Publishable keys</strong> (pk_) are used in frontend code to identify your account. They can only read public data.
                            <strong>Secret keys</strong> (sk_) are used in backend code and have full API access. Keep them secure and never expose them in client-side code.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create API Key Modal --}}
    <div class="kt-modal" id="createKeyModal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[500px] top-5 lg:top-[15%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Create API Key</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Key Name</label>
                        <input class="kt-input" id="keyName" type="text" placeholder="e.g., Production Backend, Mobile App">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Key Type</label>
                        <div class="flex flex-col gap-3 mt-1">
                            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-md border border-input hover:bg-muted/50 transition-colors">
                                <input type="radio" name="keyType" value="secret" class="kt-radio mt-0.5" checked>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Secret Key</span>
                                    <span class="text-xs text-secondary-foreground">Full API access. Use in backend only.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-md border border-input hover:bg-muted/50 transition-colors">
                                <input type="radio" name="keyType" value="publishable" class="kt-radio mt-0.5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Publishable Key</span>
                                    <span class="text-xs text-secondary-foreground">Read-only access. Safe for frontend use.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div id="createKeyError" class="hidden text-sm text-destructive bg-destructive/10 rounded-md p-3"></div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">Cancel</button>
                <button class="kt-btn kt-btn-primary" id="submitCreateKey">Create Key</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create Key Modal
    const createKeyBtn = document.querySelector('#createKeyBtn');
    const createKeyModalEl = document.querySelector('#createKeyModal');
    let createKeyModal;

    if (typeof KTModal !== 'undefined') {
        createKeyModal = KTModal.getInstance(createKeyModalEl);
    }

    createKeyBtn.addEventListener('click', function() {
        document.querySelector('#keyName').value = '';
        document.querySelector('#createKeyError').classList.add('hidden');
        if (createKeyModal) createKeyModal.show();
    });

    // Submit Create Key (using form post)
    document.querySelector('#submitCreateKey').addEventListener('click', function() {
        const name = document.querySelector('#keyName').value.trim();
        const type = document.querySelector('input[name="keyType"]:checked')?.value;
        const errorEl = document.querySelector('#createKeyError');

        if (!name) {
            errorEl.textContent = 'Please enter a key name.';
            errorEl.classList.remove('hidden');
            return;
        }

        // Create a hidden form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("dashboard.api-keys.store") }}';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'name';
        nameInput.value = name;
        form.appendChild(nameInput);

        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'type';
        typeInput.value = type;
        form.appendChild(typeInput);

        document.body.appendChild(form);
        form.submit();
    });

    // Copy key buttons
    document.querySelectorAll('.copy-key-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const key = this.dataset.key;
            navigator.clipboard.writeText(key).then(() => {
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="ki-filled ki-check"></i> Copied!';
                this.classList.add('kt-btn-success');
                this.classList.remove('kt-btn-outline');
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.classList.remove('kt-btn-success');
                    this.classList.add('kt-btn-outline');
                }, 2000);
            });
        });
    });
});
</script>
@endpush
