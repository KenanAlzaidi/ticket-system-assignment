@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Ticket Management Dashboard</h3>
        </div>

        <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between gap-4 mb-4">
                <!-- Department Filter -->
                <div class="w-full sm:w-1/3">
                    <label for="filter-department" class="block text-sm font-medium text-gray-700 mb-1">Filter by
                        Department</label>
                    <select id="filter-department"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                        <option value="">All Departments</option>
                        @foreach (config('departments.connection_map') as $dept => $conn)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-full sm:w-1/3">
                    <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                    <select id="filter-status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="noted">Noted</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <!-- Added 'hidden' class to prevent FOUC (Flash of Unstyled Content) -->
                <table id="ticketsTable" class="w-full text-sm text-left text-gray-500 hidden" style="width:100%">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">ID</th>
                            <th class="px-4 py-3 whitespace-nowrap">Department</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 whitespace-nowrap">Updated</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#ticketsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.tickets.index') }}",
                    data: function(d) {
                        d.department = $('#filter-department').val();
                        d.status = $('#filter-status').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'subject',
                        name: 'subject'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        render: function(data, type, row) {
                            return '<div class="flex flex-col"><span class="font-medium text-gray-900">' +
                                data + '</span><span class="text-xs text-gray-500">' + row
                                .customer_email + '</span></div>';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                responsive: true,
                order: [
                    [5, 'desc']
                ], // Sort by Updated At descending
                language: {
                    search: "",
                    searchPlaceholder: "Search tickets...",
                    emptyTable: "No tickets found across any department."
                },
                // Tailwind-specific DOM structure and classes for DataTables
                dom: "<'flex flex-col sm:flex-row justify-between items-center mb-4 gap-4'f>" +
                    "<'overflow-x-auto'tr>" +
                    "<'flex flex-col sm:flex-row justify-between items-center mt-4 gap-4'ip>",
            });

            // Show the table only after DataTables has finished initializing
            $('#ticketsTable').removeClass('hidden');

            // Trigger redraw on filter change
            $('#filter-department, #filter-status').change(function() {
                table.draw();
            });
        });
    </script>
@endpush
