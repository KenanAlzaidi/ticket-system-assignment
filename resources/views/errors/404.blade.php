@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center w-full h-full text-center">
    <div class="max-w-2xl px-4">
        <p class="text-8xl font-bold text-blue-600 mb-4">404</p>
        <h1 class="text-4xl font-bold tracking-tight text-gray-900 mb-8">Page not found</h1>
        <p class="text-xl leading-relaxed text-gray-600 mb-10">
            Oops! The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <div>
            <a href="{{ auth()->check() ? route('admin.tickets.index') : route('tickets.create') }}" class="inline-block rounded-md bg-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-lg hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-transform hover:scale-105">
                Return Home
            </a>
        </div>
    </div>
</div>
@endsection
