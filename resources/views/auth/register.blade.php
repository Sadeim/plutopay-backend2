@extends('layouts.auth')
@section('title', 'Register')

@section('content')
    <h3 class="text-lg font-semibold text-mono text-center mb-7">Create Account</h3>

    @if($errors->any())
        <div class="kt-alert kt-alert-danger mb-5">
            @foreach($errors->all() as $error)
                <span class="text-sm">{{ $error }}</span><br>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}" class="flex flex-col gap-5">
        @csrf
        <div class="flex flex-col gap-1">
            <label class="kt-form-label text-mono">Business Name</label>
            <input class="kt-input" name="business_name" type="text" placeholder="Your Business Name" value="{{ old('business_name') }}" required/>
        </div>
        <div class="flex gap-2.5">
            <div class="flex flex-col gap-1 grow">
                <label class="kt-form-label text-mono">First Name</label>
                <input class="kt-input" name="first_name" type="text" placeholder="First Name" value="{{ old('first_name') }}" required/>
            </div>
            <div class="flex flex-col gap-1 grow">
                <label class="kt-form-label text-mono">Last Name</label>
                <input class="kt-input" name="last_name" type="text" placeholder="Last Name" value="{{ old('last_name') }}" required/>
            </div>
        </div>
        <div class="flex flex-col gap-1">
            <label class="kt-form-label text-mono">Email</label>
            <input class="kt-input" name="email" type="email" placeholder="email@company.com" value="{{ old('email') }}" required/>
        </div>
        <div class="flex flex-col gap-1">
            <label class="kt-form-label text-mono">Password</label>
            <input class="kt-input" name="password" type="password" placeholder="Enter Password" required/>
        </div>
        <div class="flex flex-col gap-1">
            <label class="kt-form-label text-mono">Confirm Password</label>
            <input class="kt-input" name="password_confirmation" type="password" placeholder="Confirm Password" required/>
        </div>
        <button class="kt-btn kt-btn-primary flex justify-center grow" type="submit">Create Account</button>
    </form>

    <div class="flex items-center justify-center gap-1 mt-5">
        <span class="text-2sm text-secondary-foreground">Already have an account?</span>
        <a class="text-2sm kt-link" href="{{ route('login') }}">Sign In</a>
    </div>
@endsection
