@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
    <div class="text-center mb-2.5">
        <h3 class="text-lg font-medium text-mono leading-none mb-2.5">Sign in</h3>
        <div class="flex items-center justify-center font-medium">
            <span class="text-sm text-secondary-foreground me-1.5">Need an account?</span>
            <a class="text-sm link" href="{{ route('register') }}">Sign up</a>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-3 mb-4">
            @foreach($errors->all() as $error)
                <span class="text-sm text-red-600">{{ $error }}</span><br>
            @endforeach
        </div>
    @endif

    <div class="flex flex-col gap-1">
        <label class="kt-form-label font-normal text-mono">Email</label>
        <input class="kt-input" name="email" form="sign_in_form" type="email" placeholder="email@email.com" value="{{ old('email') }}"/>
    </div>
    <div class="flex flex-col gap-1">
        <div class="flex items-center justify-between gap-1">
            <label class="kt-form-label font-normal text-mono">Password</label>
        </div>
        <div class="kt-input" data-kt-toggle-password="true">
            <input name="password" form="sign_in_form" placeholder="Enter Password" type="password" value=""/>
            <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button">
                <span class="kt-toggle-password-active:hidden"><i class="ki-filled ki-eye text-muted-foreground"></i></span>
                <span class="hidden kt-toggle-password-active:block"><i class="ki-filled ki-eye-slash text-muted-foreground"></i></span>
            </button>
        </div>
    </div>
    <label class="kt-label">
        <input class="kt-checkbox kt-checkbox-sm" name="remember" type="checkbox" value="1"/>
        <span class="kt-checkbox-label">Remember me</span>
    </label>
    <button class="kt-btn kt-btn-primary flex justify-center grow" form="sign_in_form" type="submit">Sign In</button>

    <form id="sign_in_form" method="POST" action="{{ url('/login') }}" class="hidden">@csrf</form>
@endsection
