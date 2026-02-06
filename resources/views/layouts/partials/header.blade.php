<header class="kt-header fixed top-0 z-10 start-0 end-0 flex items-stretch shrink-0 bg-background" data-kt-sticky="true" data-kt-sticky-name="header" id="header">
    <div class="container-fixed flex justify-between items-stretch lg:gap-4" id="header_container">
        <div class="flex gap-1 lg:hidden items-center -ml-1">
            <a href="{{ route('dashboard') }}">
                <span class="text-lg font-bold text-primary">PlutoPay</span>
            </a>
        </div>

        <div class="flex items-center gap-2 lg:gap-3.5">
            {{-- Test/Live Mode Badge --}}
            @if(auth()->user()->merchant->test_mode ?? true)
                <span class="kt-badge kt-badge-warning kt-badge-outline kt-badge-sm gap-1">
                    <i class="ki-filled ki-information-2 text-warning text-base"></i>
                    Test Mode
                </span>
            @else
                <span class="kt-badge kt-badge-success kt-badge-outline kt-badge-sm gap-1">
                    <i class="ki-filled ki-check-circle text-success text-base"></i>
                    Live Mode
                </span>
            @endif

            {{-- User Menu --}}
            <div class="kt-menu" data-kt-menu="true">
                <div class="kt-menu-item" data-kt-menu-item-offset="20px, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-toggle kt-btn kt-btn-icon rounded-full">
                        <div class="flex items-center justify-center size-9 rounded-full bg-primary text-white text-sm font-semibold">
                            {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? 'U', 0, 1)) }}
                        </div>
                    </div>
                    <div class="kt-menu-dropdown kt-menu-default w-[200px]">
                        <div class="flex items-center gap-2 px-5 py-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-semibold text-mono">{{ auth()->user()->full_name ?? 'User' }}</span>
                                <span class="text-2sm text-secondary-foreground">{{ auth()->user()->email ?? '' }}</span>
                            </div>
                        </div>
                        <div class="kt-menu-separator"></div>
                        <div class="flex flex-col">
                            <a class="kt-menu-item kt-menu-link" href="{{ route('dashboard.settings') }}">
                                <span class="kt-menu-icon"><i class="ki-filled ki-setting-2"></i></span>
                                <span class="kt-menu-title">Settings</span>
                            </a>
                        </div>
                        <div class="kt-menu-separator"></div>
                        <div class="flex flex-col">
                            <form method="POST" action="{{ route('dashboard.logout') }}">
                                @csrf
                                <button class="kt-menu-item kt-menu-link w-full" type="submit">
                                    <span class="kt-menu-icon"><i class="ki-filled ki-exit-right"></i></span>
                                    <span class="kt-menu-title">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
