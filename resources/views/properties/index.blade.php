<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Properties') }}
            </h2>
            <a href="{{ route('properties.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Property
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

            @if ($properties->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No properties</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding a new property.</p>
                        <div class="mt-6">
                            <a href="{{ route('properties.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Property
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($properties as $property)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $property->address }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $property->city }}, {{ $property->state }} {{ $property->zip_code }}
                                        </p>
                                        @if ($property->notes)
                                            <p class="text-sm text-gray-500 mt-2">
                                                {{ Str::limit($property->notes, 100) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between text-sm">
                                    <span class="text-gray-500">
                                        {{ $property->tickets->count() }} {{ Str::plural('ticket', $property->tickets->count()) }}
                                    </span>
                                    <span class="text-gray-400">
                                        Added {{ $property->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <div class="mt-4 flex space-x-3">
                                    <a href="{{ route('properties.show', $property) }}" class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150 text-sm font-medium">
                                        View
                                    </a>
                                    <a href="{{ route('properties.edit', $property) }}" class="flex-1 text-center px-3 py-2 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-150 text-sm font-medium">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
