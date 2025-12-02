<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- SEO & Metadata --}}
    <meta name="description" content="A robust multi-department ticket management system for efficient customer support handling.">
    <meta name="author" content="Kenan Alzaidi">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="noindex, nofollow"> {{-- Default to private since this is an internal/admin tool mainly --}}

    {{-- Security Headers --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    {{-- Performance Optimization: Preconnect to CDNs --}}
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="preconnect" href="https://cdn.datatables.net" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://cdn.datatables.net">
    <link rel="dns-prefetch" href="https://code.jquery.com">

    <title>{{ config('app.name', 'Ticket System') }}</title>

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6', // blue-500
                    }
                }
            }
        }
    </script>

    <!-- Trix Editor -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js" defer></script>

    <!-- DataTables CSS (Tailwind adaptation) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.tailwindcss.min.css">

    <style>
        /* Custom Trix Editor Styling to match Tailwind forms */
        trix-editor {
            min-height: 150px;
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            padding: 0.5rem;
            color: #111827;
            outline: none; /* Let Tailwind/ring handle focus if possible, or custom below */
        }
        trix-editor:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); /* Ring effect on focus */
        }
        trix-toolbar .trix-button--icon {
            color: #4b5563;
        }
        /* DataTables overrides for cleaner Tailwind integration */
        .dataTables_wrapper select,
        .dataTables_wrapper .dataTables_filter input {
            border-width: 1px;
            border-radius: 0.375rem;
            border-color: #d1d5db;
            padding: 0.25rem 2rem 0.25rem 0.5rem;
            color: #4b5563;
        }

        /* Style the search cancel button (X) to be black and visible */
        .dataTables_wrapper .dataTables_filter input[type="search"]::-webkit-search-cancel-button {
            -webkit-appearance: none;
            height: 1em;
            width: 1em;
            border-radius: 50em;
            background: url("data:image/svg+xml;charset=UTF-8,%3csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M6 18L18 6M6 6l12 12' stroke='black' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/%3e%3c/svg%3e") no-repeat center center;
            cursor: pointer;
            opacity: 0.6;
        }
        .dataTables_wrapper .dataTables_filter input[type="search"]::-webkit-search-cancel-button:hover {
            opacity: 1;
        }
        .dataTables_wrapper .dataTables_filter input[type="search"]{
            padding-right: 0.5rem;
        }

        /* DataTables Pagination - Fixed for CDN (No @apply) */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            margin-left: 0.25rem;
            margin-right: 0.25rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: #fff;
            color: #374151 !important;
            cursor: pointer;
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #f3f4f6 !important;
            color: #2563eb !important;
            border-color: #d1d5db !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #eff6ff !important;
            color: #2563eb !important;
            border-color: #3b82f6 !important;
            font-weight: 600;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Increase table row spacing for better UX */
        .dataTables_wrapper table.dataTable tbody td {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
    </style>
</head>
<body class="flex flex-col h-full bg-gray-50 font-sans antialiased text-gray-900">

    <!-- Navigation -->
    <nav class="bg-gray-800 text-white shadow-md">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a class="text-xl font-bold tracking-wider hover:text-gray-200 transition" href="{{ url('/') }}">
                        {{ config('app.name', 'Ticket System') }}
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="{{ route('tickets.create') }}" class="{{ request()->routeIs('tickets.create') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium transition">Submit Ticket</a>

                        @auth
                            <a href="{{ route('admin.tickets.index') }}" class="{{ request()->routeIs('admin.tickets.index') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium transition">Admin Dashboard</a>

                            <form action="{{ route('logout') }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition cursor-pointer">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium transition">Admin Login</a>
                        @endauth
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="-mr-2 flex md:hidden">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="bg-gray-800 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ route('tickets.create') }}" class="{{ request()->routeIs('tickets.create') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} block px-3 py-2 rounded-md text-base font-medium">Submit Ticket</a>
                @auth
                    <a href="{{ route('admin.tickets.index') }}" class="{{ request()->routeIs('admin.tickets.index') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} block px-3 py-2 rounded-md text-base font-medium">Admin Dashboard</a>
                    <form action="{{ route('logout') }}" method="POST" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} block px-3 py-2 rounded-md text-base font-medium">Admin Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-grow flex flex-col">
        <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8 flex-grow flex flex-col justify-center">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-gray-100 border-t border-gray-200 mt-auto">
        <div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} Ticket System. All rights reserved.
        </div>
    </footer>

    <!-- jQuery (Required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.tailwindcss.min.js"></script>

    @stack('scripts')
</body>
</html>
