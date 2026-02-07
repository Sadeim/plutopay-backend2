@extends('layouts.app')
@section('title', 'Settings')

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Page Header --}}
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-xl font-semibold leading-none text-mono">Settings</h1>
            <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                Manage your account and business settings
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="flex items-center gap-2 p-3 rounded-md bg-success/10 text-success text-sm">
            <i class="ki-filled ki-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="flex items-center gap-2 p-3 rounded-md bg-destructive/10 text-destructive text-sm">
            <i class="ki-filled ki-information-2"></i>
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Tabs --}}
        <div class="flex flex-wrap gap-5 border-b border-input">
            <button class="settings-tab active pb-3 text-sm font-medium border-b-2 border-primary text-primary" data-target="tab_business">
                <i class="ki-filled ki-shop text-sm"></i> Business Profile
            </button>
            <button class="settings-tab pb-3 text-sm font-medium border-b-2 border-transparent text-secondary-foreground hover:text-foreground" data-target="tab_profile">
                <i class="ki-filled ki-user text-sm"></i> My Profile
            </button>
            <button class="settings-tab pb-3 text-sm font-medium border-b-2 border-transparent text-secondary-foreground hover:text-foreground" data-target="tab_password">
                <i class="ki-filled ki-lock text-sm"></i> Password
            </button>
            <button class="settings-tab pb-3 text-sm font-medium border-b-2 border-transparent text-secondary-foreground hover:text-foreground" data-target="tab_team">
                <i class="ki-filled ki-people text-sm"></i> Team
            </button>
        </div>

        {{-- Business Profile Tab --}}
        <div class="settings-panel" id="tab_business">
            <form method="POST" action="{{ route('dashboard.settings.business') }}">
                @csrf
                @method('PUT')
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <div class="flex flex-col gap-5">
                            <h3 class="text-base font-semibold text-foreground">Business Information</h3>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Business Name *</label>
                                    <input class="kt-input" name="business_name" type="text" value="{{ old('business_name', $merchant->business_name) }}" required>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Display Name</label>
                                    <input class="kt-input" name="display_name" type="text" value="{{ old('display_name', $merchant->display_name) }}" placeholder="Shown to customers">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Business Email *</label>
                                    <input class="kt-input" name="email" type="email" value="{{ old('email', $merchant->email) }}" required>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Phone</label>
                                    <input class="kt-input" name="phone" type="text" value="{{ old('phone', $merchant->phone) }}">
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-foreground">Website</label>
                                <input class="kt-input" name="website" type="url" value="{{ old('website', $merchant->website) }}" placeholder="https://">
                            </div>

                            <hr class="border-input">
                            <h3 class="text-base font-semibold text-foreground">Address</h3>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-foreground">Address Line 1</label>
                                <input class="kt-input" name="address_line1" type="text" value="{{ old('address_line1', $merchant->address_line1) }}">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-foreground">Address Line 2</label>
                                <input class="kt-input" name="address_line2" type="text" value="{{ old('address_line2', $merchant->address_line2) }}">
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">City</label>
                                    <input class="kt-input" name="city" type="text" value="{{ old('city', $merchant->city) }}">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">State</label>
                                    <input class="kt-input" name="state" type="text" value="{{ old('state', $merchant->state) }}">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Postal Code</label>
                                    <input class="kt-input" name="postal_code" type="text" value="{{ old('postal_code', $merchant->postal_code) }}">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Country</label>
                                    <input class="kt-input" name="country" type="text" value="{{ old('country', $merchant->country) }}" maxlength="2" placeholder="US">
                                </div>
                            </div>

                            <hr class="border-input">
                            <h3 class="text-base font-semibold text-foreground">Preferences</h3>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Default Currency</label>
                                    <select class="kt-select" name="default_currency">
                                        @foreach(['USD' => 'USD - US Dollar', 'EUR' => 'EUR - Euro', 'GBP' => 'GBP - British Pound', 'ILS' => 'ILS - Israeli Shekel', 'AED' => 'AED - UAE Dirham', 'SAR' => 'SAR - Saudi Riyal', 'JOD' => 'JOD - Jordanian Dinar'] as $code => $label)
                                        <option value="{{ $code }}" {{ $merchant->default_currency === $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Timezone</label>
                                    <select class="kt-select" name="timezone">
                                        @foreach(['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles', 'Europe/London', 'Europe/Berlin', 'Europe/Paris', 'Asia/Dubai', 'Asia/Riyadh', 'Asia/Jerusalem', 'Asia/Amman'] as $tz)
                                        <option value="{{ $tz }}" {{ $merchant->timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                            <hr class="border-input">
                            <h3 class="text-base font-semibold text-foreground">Payment Processor</h3>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Stripe Connected Account ID</label>
                                    <input class="kt-input font-mono" name="processor_account_id" value="{{ $merchant->processor_account_id }}" placeholder="acct_xxxxxxxxxx">
                                    <span class="text-xs text-secondary-foreground">Your Stripe Connect account ID. Payments will be processed on behalf of this account.</span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Mode</label>
                                    <div class="flex items-center gap-3 mt-1">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="test_mode" value="0" {{ !$merchant->test_mode ? 'checked' : '' }} class="rounded">
                                            <span class="text-sm text-foreground">Live</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="test_mode" value="1" {{ $merchant->test_mode ? 'checked' : '' }} class="rounded">
                                            <span class="text-sm text-foreground">Test</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @if($merchant->processor_account_id)
                            <div class="flex items-center gap-2 p-3 rounded-md bg-success/10 text-sm" style="color:#22c55e">
                                <i class="ki-filled ki-check-circle"></i>
                                Connected: {{ $merchant->processor_account_id }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="kt-card-footer justify-end">
                        <button type="submit" class="kt-btn kt-btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- My Profile Tab --}}
        <div class="settings-panel hidden" id="tab_profile">
            <form method="POST" action="{{ route('dashboard.settings.profile') }}">
                @csrf
                @method('PUT')
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <div class="flex flex-col gap-5">
                            <h3 class="text-base font-semibold text-foreground">Personal Information</h3>

                            <div class="flex items-center gap-4 mb-2">
                                <div class="flex items-center justify-center size-16 rounded-full bg-primary/10 text-primary text-xl font-semibold">
                                    {{ strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-base font-semibold text-foreground">{{ $user->first_name }} {{ $user->last_name }}</span>
                                    <span class="text-sm text-secondary-foreground">{{ ucfirst($user->role ?? 'member') }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">First Name *</label>
                                    <input class="kt-input" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" required>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Last Name *</label>
                                    <input class="kt-input" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Email *</label>
                                    <input class="kt-input" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-foreground">Phone</label>
                                    <input class="kt-input" name="phone" type="text" value="{{ old('phone', $user->phone) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="kt-card-footer justify-end">
                        <button type="submit" class="kt-btn kt-btn-primary">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Password Tab --}}
        <div class="settings-panel hidden" id="tab_password">
            <form method="POST" action="{{ route('dashboard.settings.password') }}">
                @csrf
                @method('PUT')
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <div class="flex flex-col gap-5 max-w-md">
                            <h3 class="text-base font-semibold text-foreground">Change Password</h3>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-foreground">Current Password *</label>
                                <input class="kt-input" name="current_password" type="password" required>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-foreground">New Password *</label>
                                <input class="kt-input" name="password" type="password" required minlength="8">
                                <span class="text-xs text-secondary-foreground">Minimum 8 characters</span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-foreground">Confirm New Password *</label>
                                <input class="kt-input" name="password_confirmation" type="password" required>
                            </div>
                        </div>
                    </div>
                    <div class="kt-card-footer justify-end">
                        <button type="submit" class="kt-btn kt-btn-primary">Change Password</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Team Tab --}}
        <div class="settings-panel hidden" id="tab_team">
            <div class="kt-card">
                <div class="kt-card-content p-5 lg:p-7">
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-foreground">Team Members</h3>
                            <span class="text-sm text-secondary-foreground">{{ $teamMembers->count() }} member{{ $teamMembers->count() !== 1 ? 's' : '' }}</span>
                        </div>

                        <div class="flex flex-col gap-3">
                            @foreach($teamMembers as $member)
                            <div class="flex items-center justify-between p-3 rounded-md border border-input">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10 text-primary text-sm font-semibold">
                                        {{ strtoupper(substr($member->first_name ?? '', 0, 1) . substr($member->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-foreground">{{ $member->first_name }} {{ $member->last_name }}</span>
                                            @if($member->id === auth()->id())
                                            <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">You</span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-secondary-foreground">{{ $member->email }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="kt-badge kt-badge-sm {{ $member->role === 'owner' ? 'kt-badge-warning' : ($member->role === 'admin' ? 'kt-badge-primary' : 'kt-badge-secondary') }} kt-badge-outline">
                                        {{ ucfirst($member->role ?? 'member') }}
                                    </div>
                                    @if($member->status === 'active')
                                    <span class="size-2 rounded-full bg-success"></span>
                                    @else
                                    <span class="size-2 rounded-full bg-secondary"></span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            const target = this.dataset.target;

            // Deactivate all tabs
            tabs.forEach(function(t) {
                t.classList.remove('active', 'border-primary', 'text-primary');
                t.classList.add('border-transparent', 'text-secondary-foreground');
            });

            // Activate clicked tab
            this.classList.add('active', 'border-primary', 'text-primary');
            this.classList.remove('border-transparent', 'text-secondary-foreground');

            // Hide all panels, show target
            panels.forEach(function(p) { p.classList.add('hidden'); });
            document.getElementById(target).classList.remove('hidden');
        });
    });
});
</script>
@endpush
