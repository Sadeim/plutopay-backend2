<div class="kt-sidebar bg-background border-e border-e-border fixed top-0 bottom-0 z-20 hidden lg:flex flex-col items-stretch shrink-0 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar">
    <div class="kt-sidebar-header hidden lg:flex items-center relative justify-between px-3 lg:px-6 shrink-0" id="sidebar_header">
        <a href="{{ route('dashboard') }}">
            <span class="default-logo text-xl font-bold text-primary min-h-[22px]">PlutoPay</span>
            <span class="small-logo text-xl font-bold text-primary min-h-[22px]">PP</span>
        </a>
        <button class="kt-btn kt-btn-outline kt-btn-icon size-[30px] absolute start-full top-2/4 -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4" data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle">
            <i class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 transition-all duration-300"></i>
        </button>
    </div>
    <div class="kt-sidebar-content flex grow shrink-0 py-5 pe-2" id="sidebar_content">
        <div class="kt-scrollable-y-hover grow shrink-0 flex ps-2 lg:ps-5 pe-1 lg:pe-3" data-kt-scrollable="true" data-kt-scrollable-dependencies="#sidebar_header" data-kt-scrollable-height="auto" data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_content" id="sidebar_scrollable">
            <div class="kt-menu flex flex-col grow gap-1" data-kt-menu="true" data-kt-menu-accordion-expand-all="false" id="sidebar_menu">

                {{-- Dashboard --}}
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard') && !request()->is('dashboard/*') ? 'active' : '' }}" href="{{ route('dashboard') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-element-11 text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Dashboard</span>
                    </a>
                </div>

                {{-- Transactions --}}
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard.transactions*') ? 'active' : '' }}" href="{{ route('dashboard.transactions.index') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-dollar text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Transactions</span>
                    </a>
                </div>

                {{-- Customers --}}
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard.customers*') ? 'active' : '' }}" href="{{ route('dashboard.customers.index') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-people text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Customers</span>
                    </a>
                </div>

                {{-- Terminals --}}
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard.terminals*') ? 'active' : '' }}" href="{{ route('dashboard.terminals.index') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-shop text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Terminals</span>
                    </a>
                </div>

                {{-- Payouts --}}
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard.payouts*') ? 'active' : '' }}" href="{{ route('dashboard.payouts.index') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-bank text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Payouts</span>
                    </a>
                </div>

                {{-- Separator --}}
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">Developers</span>
                </div>

                {{-- API Keys --}}
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard.api-keys*') ? 'active' : '' }}" href="{{ route('dashboard.api-keys.index') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-key text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">API Keys</span>
                    </a>
                </div>

                {{-- Webhooks --}}
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard.webhooks*') ? 'active' : '' }}" href="{{ route('dashboard.webhooks.index') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-wifi text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Webhooks</span>
                    </a>
                </div>

                {{-- Separator --}}
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">Account</span>
                </div>

                {{-- Settings --}}
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard.settings*') ? 'active' : '' }}" href="{{ route('dashboard.settings') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-setting-2 text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Settings</span>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
