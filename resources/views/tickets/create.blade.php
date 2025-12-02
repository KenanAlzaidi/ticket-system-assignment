@extends('layouts.app')

@section('content')
<div class="flex justify-center w-full">
    <div class="w-full max-w-2xl my-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h4 class="text-xl font-semibold text-white">Submit a Support Ticket</h4>
            </div>

            <div class="p-6 sm:p-8">
                <form method="POST" action="{{ route('tickets.store') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <label for="customer_name" class="block text-sm font-medium leading-6 text-gray-900">Full Name <span class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <input type="text"
                                       id="customer_name"
                                       name="customer_name"
                                       value="{{ old('customer_name') }}"
                                       required
                                       minlength="2"
                                       maxlength="255"
                                       autocomplete="name"
                                       placeholder="John Doe"
                                       class="px-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 @error('customer_name') ring-red-500 focus:ring-red-500 @enderror">
                            </div>
                            @error('customer_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-1">
                            <label for="customer_email" class="block text-sm font-medium leading-6 text-gray-900">Email <span class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <input type="email"
                                       id="customer_email"
                                       name="customer_email"
                                       value="{{ old('customer_email') }}"
                                       required
                                       autocomplete="email"
                                       placeholder="name@example.com"
                                       class="px-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 @error('customer_email') ring-red-500 focus:ring-red-500 @enderror">
                            </div>
                            @error('customer_email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <label for="customer_phone" class="block text-sm font-medium leading-6 text-gray-900">Phone <span class="text-gray-500 font-normal">(Optional)</span></label>
                            <div class="mt-1">
                                <input type="tel"
                                       id="customer_phone"
                                       name="customer_phone"
                                       value="{{ old('customer_phone') }}"
                                       autocomplete="tel"
                                       placeholder="+1234567890"
                                       class="px-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 @error('customer_phone') ring-red-500 focus:ring-red-500 @enderror">
                            </div>
                            @error('customer_phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-1">
                            <label for="department" class="block text-sm font-medium leading-6 text-gray-900">Department <span class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <select id="department"
                                        name="department"
                                        required
                                        aria-label="Select support department"
                                        class="px-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 @error('department') ring-red-500 focus:ring-red-500 @enderror">
                                    <option value="" selected disabled>Select...</option>
                                    @foreach($departments as $type => $connection)
                                        <option value="{{ $type }}" {{ old('department') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('department')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium leading-6 text-gray-900">Subject <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <input type="text"
                                   id="subject"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   required
                                   minlength="5"
                                   maxlength="255"
                                   placeholder="Brief summary of your issue"
                                   class="px-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 @error('subject') ring-red-500 focus:ring-red-500 @enderror">
                        </div>
                        @error('subject')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium leading-6 text-gray-900">Message Details <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <textarea id="message"
                                      name="message"
                                      rows="4"
                                      required
                                      minlength="10"
                                      placeholder="Please describe your issue in detail..."
                                      class="px-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 @error('message') ring-red-500 focus:ring-red-500 @enderror">{{ old('message') }}</textarea>
                        </div>
                        @error('message')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition duration-150 ease-in-out">
                            Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
