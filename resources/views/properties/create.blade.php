<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Property') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('properties.store') }}">
                        @csrf

                        <!-- Address -->
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">
                                Street Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="address" id="address" value="{{ old('address') }}" required
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
                            <input type="text" name="city" id="city" value="{{ old('city') }}" required
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
                                <input type="text" name="state" id="state" value="{{ old('state') }}" maxlength="2" required
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
                                <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}" required
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
                                placeholder="Add any additional information about this property...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('properties.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-150">
                                Add Property
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
