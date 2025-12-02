@extends('layouts.app')

@section('content')
<div class="w-full h-full flex items-center justify-center">
    <div class="w-full max-w-sm px-6">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm mb-10">
            <h2 class="text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Admin Portal Login</h2>
        </div>

        <form class="space-y-6" action="{{ route('login.post') }}" method="POST">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                <div class="mt-2">
                    <input id="email"
                           name="email"
                           type="email"
                           autocomplete="email"
                           required
                           value="{{ old('email') }}"
                           autofocus
                           placeholder="admin@example.com"
                           class="px-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                </div>
                <div class="mt-2">
                    <input id="password"
                           name="password"
                           type="password"
                           autocomplete="current-password"
                           required
                           class="px-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition duration-150 ease-in-out">Sign in</button>
            </div>
        </form>
    </div>
</div>
@endsection
