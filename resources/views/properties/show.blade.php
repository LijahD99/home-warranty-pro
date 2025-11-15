<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Property Details') }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('properties.edit', $property) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    Edit Property
                </a>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    Back to Properties
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Property Information -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Property Information</h3>

                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Address</label>
                                    <p class="text-gray-900">{{ $property->address }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500">City</label>
                                    <p class="text-gray-900">{{ $property->city }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">State</label>
                                        <p class="text-gray-900">{{ $property->state }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">ZIP Code</label>
                                        <p class="text-gray-900">{{ $property->zip_code }}</p>
                                    </div>
                                </div>

                                @if ($property->notes)
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Notes</label>
                                        <p class="text-gray-900">{{ $property->notes }}</p>
                                    </div>
                                @endif

                                <div class="pt-3 border-t">
                                    <label class="text-sm font-medium text-gray-500">Added</label>
                                    <p class="text-gray-900">{{ $property->created_at->format('M j, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Tickets ({{ $property->tickets->count() }})
                                </h3>
                                <a href="{{ route('tickets.create', ['property' => $property->id]) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    New Ticket
                                </a>
                            </div>

                            @if ($property->tickets->isEmpty())
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets</h3>
                                    <p class="mt-1 text-sm text-gray-500">No warranty tickets for this property yet.</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach ($property->tickets as $ticket)
                                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors duration-150">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2">
                                                        <h4 class="text-sm font-semibold text-gray-900">
                                                            {{ $ticket->area_of_issue }}
                                                        </h4>
                                                        @php
                                                            $statusColors = [
                                                                'submitted' => 'bg-yellow-100 text-yellow-800',
                                                                'assigned' => 'bg-blue-100 text-blue-800',
                                                                'in_progress' => 'bg-purple-100 text-purple-800',
                                                                'complete' => 'bg-green-100 text-green-800',
                                                                'closed' => 'bg-gray-100 text-gray-800',
                                                            ];
                                                        @endphp
                                                        <span class="px-2 py-1 text-xs font-medium rounded {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-gray-600 mt-1">
                                                        {{ Str::limit($ticket->description, 100) }}
                                                    </p>
                                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                                        <span>{{ $ticket->created_at->format('M j, Y') }}</span>
                                                        @if ($ticket->assignedTo)
                                                            <span>Assigned to: {{ $ticket->assignedTo->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <a href="{{ route('tickets.show', $ticket) }}" class="ml-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    View →
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
