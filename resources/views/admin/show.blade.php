@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Ticket Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Ticket Header Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50 gap-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="text-blue-600">#{{ $ticket->id }}</span>
                    <span class="text-gray-300">|</span>
                    <span>{{ $ticket->subject }}</span>
                </h3>
                @php
                    $statusClasses = match($ticket->status) {
                        'new' => 'bg-green-50 text-green-700 ring-green-600/20',
                        'noted' => 'bg-blue-50 text-blue-700 ring-blue-700/10',
                        'closed' => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                        default => 'bg-gray-50 text-gray-600 ring-gray-500/10'
                    };
                @endphp
                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-sm font-medium ring-1 ring-inset {{ $statusClasses }}">
                    {{ ucfirst($ticket->status) }}
                </span>
            </div>

            <div class="p-6">
                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Customer Information</h4>
                        <dl class="space-y-2 text-sm text-gray-700">
                            <div class="flex sm:block">
                                <dt class="font-medium text-gray-900 w-20 sm:w-auto sm:inline">Name:</dt>
                                <dd class="sm:inline">{{ $ticket->customer_name }}</dd>
                            </div>
                            <div class="flex sm:block">
                                <dt class="font-medium text-gray-900 w-20 sm:w-auto sm:inline">Email:</dt>
                                <dd class="sm:inline">
                                    <a href="mailto:{{ $ticket->customer_email }}" class="text-blue-600 hover:text-blue-800 hover:underline">{{ $ticket->customer_email }}</a>
                                </dd>
                            </div>
                            <div class="flex sm:block">
                                <dt class="font-medium text-gray-900 w-20 sm:w-auto sm:inline">Phone:</dt>
                                <dd class="sm:inline">{{ $ticket->customer_phone ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Ticket Details</h4>
                        <dl class="space-y-2 text-sm text-gray-700">
                            <div class="flex sm:block">
                                <dt class="font-medium text-gray-900 w-24 sm:w-auto sm:inline">Department:</dt>
                                <dd class="sm:inline">
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                        {{ $ticket->department }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex sm:block">
                                <dt class="font-medium text-gray-900 w-24 sm:w-auto sm:inline">Submitted:</dt>
                                <dd class="sm:inline">{{ $ticket->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            <div class="flex sm:block">
                                <dt class="font-medium text-gray-900 w-24 sm:w-auto sm:inline">Updated:</dt>
                                <dd class="sm:inline">{{ $ticket->updated_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <hr class="my-6 border-gray-200">

                <!-- Message Body -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Message</h4>
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 text-gray-800 prose prose-sm max-w-none mb-6">
                        {!! nl2br(e($ticket->message)) !!}
                    </div>

                    <!-- Admin Notes Section -->
                    @if($ticket->notes->count() > 0)
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Admin Notes</h4>
                        <div class="space-y-4">
                            @foreach($ticket->notes as $note)
                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 relative">
                                    <div class="text-xs text-gray-500 top-3 right-4 mb-2">
                                        {{ $note->created_at->format('M d, Y H:i') }}
                                    </div>
                                    <div class="text-gray-800 prose prose-sm max-w-none">
                                        {!! $note->note !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Reply Box -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-900">Admin Actions</h3>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.tickets.update', ['department' => $ticket->department, 'id' => $ticket->id]) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="note" class="block text-sm font-medium leading-6 text-gray-900 mb-2">Add Note / Reply</label>
                        <input id="note" type="hidden" name="note">
                        <trix-editor input="note" class="trix-content" placeholder="Type your response or internal note here..."></trix-editor>
                        @error('note')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Adding a note will automatically update the ticket status to <span class="font-medium text-blue-600">Noted</span>.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                            Post Note & Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Sidebar -->
    <div class="lg:col-span-1">
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 sticky top-6">
            <div class="p-6">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Quick Actions</h4>
                <a href="{{ route('admin.tickets.index') }}" class="block w-full text-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                    &larr; Back to Ticket List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
