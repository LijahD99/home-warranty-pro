<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Tickets') }}
            </h2>
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Ticket
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if ($tickets->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a warranty ticket.</p>
                        <div class="mt-6">
                            <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create Ticket
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Status Filter Tabs -->
                <div class="mb-6 bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="flex flex-wrap gap-2">
                        @php
                            $statusCounts = $tickets->groupBy('status')->map->count();
                        @endphp
                        <a href="{{ route('tickets.index') }}" class="px-4 py-2 rounded-md text-sm font-medium {{ request('status') ? 'bg-gray-100 text-gray-700' : 'bg-blue-600 text-white' }}">
                            All ({{ $tickets->count() }})
                        </a>
                        @foreach(['submitted', 'assigned', 'in_progress', 'complete', 'closed'] as $status)
                            <a href="{{ route('tickets.index', ['status' => $status]) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ request('status') === $status ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $statusCounts[$status] ?? 0 }})
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Tickets List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="divide-y divide-gray-200">
                        @foreach ($tickets as $ticket)
                            <div class="p-6 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            @php
                                                $statusColors = [
                                                    'submitted' => 'bg-yellow-100 text-yellow-800',
                                                    'assigned' => 'bg-blue-100 text-blue-800',
                                                    'in_progress' => 'bg-purple-100 text-purple-800',
                                                    'complete' => 'bg-green-100 text-green-800',
                                                    'closed' => 'bg-gray-100 text-gray-800',
                                                ];
                                            @endphp
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $ticket->area_of_issue }}
                                            </h3>
                                        </div>

                                        <p class="text-gray-600 mb-3">
                                            {{ Str::limit($ticket->description, 150) }}
                                        </p>

                                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                                </svg>
                                                {{ $ticket->property->address }}
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $ticket->created_at->format('M j, Y') }}
                                            </div>
                                            @if ($ticket->assignedTo)
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    Assigned to: {{ $ticket->assignedTo->name }}
                                                </div>
                                            @endif
                                            @if ($ticket->image_path)
                                                <div class="flex items-center text-blue-600">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    Has image
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="ml-4">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150 text-sm font-medium">
                                            View Details
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
