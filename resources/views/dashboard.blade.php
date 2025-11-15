<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h3>
                    <p class="mt-1 text-gray-600">Manage your properties and warranty tickets all in one place.</p>
                </div>
            </div>

            @if (Auth::user()->isHomeowner())
                <!-- Quick Stats -->
                @php
                    $properties = Auth::user()->properties;
                    $tickets = Auth::user()->tickets;
                    $openTickets = $tickets->whereIn('status', ['submitted', 'assigned', 'in_progress']);
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Properties Count -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Properties</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $properties->count() }}</p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                </div>
                            </div>
                            <a href="{{ route('properties.index') }}" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View all →
                            </a>
                        </div>
                    </div>

                    <!-- Open Tickets Count -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Open Tickets</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $openTickets->count() }}</p>
                                </div>
                                <div class="p-3 bg-yellow-100 rounded-full">
                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <a href="{{ route('tickets.index') }}" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View all →
                            </a>
                        </div>
                    </div>

                    <!-- Total Tickets Count -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Total Tickets</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $tickets->count() }}</p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-full">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                            <a href="{{ route('tickets.index') }}" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View history →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="{{ route('tickets.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-150">
                                <div class="p-3 bg-blue-600 rounded-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="font-semibold text-gray-900">Create New Ticket</p>
                                    <p class="text-sm text-gray-600">Submit a warranty claim</p>
                                </div>
                            </a>

                            <a href="{{ route('properties.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-150">
                                <div class="p-3 bg-green-600 rounded-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="font-semibold text-gray-900">Add New Property</p>
                                    <p class="text-sm text-gray-600">Register another property</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Tickets</h3>
                            <a href="{{ route('tickets.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View all →
                            </a>
                        </div>

                        @if ($tickets->isEmpty())
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets yet</h3>
                                <p class="mt-1 text-sm text-gray-500">Create your first warranty ticket to get started.</p>
                                <div class="mt-6">
                                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                        Create Ticket
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($tickets->take(5) as $ticket)
                                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors duration-150">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-1">
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
                                                    <h4 class="font-semibold text-gray-900">{{ $ticket->area_of_issue }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 mb-2">{{ Str::limit($ticket->description, 100) }}</p>
                                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                    <span>{{ $ticket->property->address }}</span>
                                                    <span>{{ $ticket->created_at->diffForHumans() }}</span>
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
            @else
                <!-- Admin/Builder Dashboard -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-gray-900">Welcome! Use the admin panel at <a href="/admin" class="text-blue-600 hover:text-blue-800 font-medium">/admin</a> to manage tickets and users.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
