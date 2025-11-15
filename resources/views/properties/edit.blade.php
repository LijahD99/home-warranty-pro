<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Property') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('properties.update', $property) }}">
                        @csrf
                        @method('PUT')

                        <!-- Address -->
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">
                                Street Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="address" id="address" value="{{ old('address', $property->address) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('address') border-red-500 @enderror">
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="mb-4">
                            <label for="city" class="block text-sm font-medium text-gray-700">
                                City <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="city" id="city" value="{{ old('city', $property->city) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('city') border-red-500 @enderror">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- State and ZIP Code -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700">
                                    State <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="state" id="state" value="{{ old('state', $property->state) }}" maxlength="2" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('state') border-red-500 @enderror"
                                    placeholder="e.g., CA">
                                @error('state')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="zip_code" class="block text-sm font-medium text-gray-700">
                                    ZIP Code <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code', $property->zip_code) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('zip_code') border-red-500 @enderror"
                                    placeholder="e.g., 12345">
                                @error('zip_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notes (Optional)
                            </label>
                            <textarea name="notes" id="notes" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-500 @enderror"
                                placeholder="Add any additional information about this property...">{{ old('notes', $property->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-between">
                            <div>
                                @if ($property->canBeDeleted())
                                    <button type="button" onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-150">
                                        Delete Property
                                    </button>
                                @else
                                    <p class="text-sm text-gray-500">
                                        Cannot delete property with open tickets
                                    </p>
                                @endif
                            </div>
                            <div class="flex space-x-3">
                                <a href="{{ route('properties.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors duration-150">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-150">
                                    Update Property
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($property->canBeDeleted())
                        <form id="delete-form" method="POST" action="{{ route('properties.destroy', $property) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($property->canBeDeleted())
        <script>
            function confirmDelete() {
                if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
                    document.getElementById('delete-form').submit();
                }
            }
        </script>
    @endif
</x-app-layout>
