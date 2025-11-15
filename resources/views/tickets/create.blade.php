<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Ticket') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($properties->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No properties found</h3>
                            <p class="mt-1 text-sm text-gray-500">You need to add a property before creating a ticket.</p>
                            <div class="mt-6">
                                <a href="{{ route('properties.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Add Property First
                                </a>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                            @csrf

                            <!-- Property Selection -->
                            <div class="mb-4">
                                <label for="property_id" class="block text-sm font-medium text-gray-700">
                                    Property <span class="text-red-500">*</span>
                                </label>
                                <select name="property_id" id="property_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('property_id') border-red-500 @enderror">
                                    <option value="">Select a property</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id', request('property')) == $property->id ? 'selected' : '' }}>
                                            {{ $property->address }} - {{ $property->city }}, {{ $property->state }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('property_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Area of Issue -->
                            <div class="mb-4">
                                <label for="area_of_issue" class="block text-sm font-medium text-gray-700">
                                    Area of Issue <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="area_of_issue" id="area_of_issue" value="{{ old('area_of_issue') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('area_of_issue') border-red-500 @enderror"
                                    placeholder="e.g., Kitchen, Bathroom, HVAC, Plumbing">
                                @error('area_of_issue')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Specify the area or system with the issue.</p>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Description <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" id="description" rows="6" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                                    placeholder="Please provide a detailed description of the issue...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Describe the problem in detail (minimum 10 characters).</p>
                            </div>

                            <!-- Image Upload -->
                            <div class="mb-6">
                                <label for="image" class="block text-sm font-medium text-gray-700">
                                    Upload Image (Optional)
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors duration-150">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload a file</span>
                                                <input id="image" name="image" type="file" class="sr-only" accept="image/*" onchange="previewImage(event)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                    </div>
                                </div>
                                @error('image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <div id="image-preview" class="mt-4 hidden">
                                    <img id="preview-img" src="" alt="Preview" class="max-w-full h-auto rounded-lg shadow-md max-h-64">
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('tickets.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors duration-150">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-150">
                                    Submit Ticket
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</x-app-layout>
