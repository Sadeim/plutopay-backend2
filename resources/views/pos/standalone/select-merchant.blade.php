<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlutoPay POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <img src="/images/plutopay.svg" alt="PlutoPay" class="h-12 mb-4">
            <h1 class="text-xl font-bold text-gray-900">PlutoPay POS</h1>
            <p class="text-sm text-gray-500 mt-1">Sign in to access your terminal</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">{{ session('error') }}</div>
            @endif
            <form method="POST" action="{{ route('pos.login') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required autofocus class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Enter your email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Enter your password">
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl bg-blue-500 text-white font-semibold text-sm hover:bg-blue-600 active:scale-[0.98] transition-all">Sign In to POS</button>
                </div>
            </form>
        </div>
        <p class="text-center text-xs text-gray-400 mt-6">Powered by PlutoPay</p>
    </div>
</body>
</html>
