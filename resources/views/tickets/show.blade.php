<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ticket Details') }}
            </h2>
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                Back to Tickets
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Ticket Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Ticket Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $ticket->area_of_issue }}</h3>
                                    @php
                                        $statusColors = [
                                            'submitted' => 'bg-yellow-100 text-yellow-800',
                                            'assigned' => 'bg-blue-100 text-blue-800',
                                            'in_progress' => 'bg-purple-100 text-purple-800',
                                            'complete' => 'bg-green-100 text-green-800',
                                            'closed' => 'bg-gray-100 text-gray-800',
                                        ];
                                    @endphp
                                    <span class="inline-block mt-2 px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="prose max-w-none">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
                                <p class="text-gray-900 whitespace-pre-line">{{ $ticket->description }}</p>
                            </div>

                            @if ($ticket->image_path)
                                <div class="mt-6">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Attached Image</h4>
                                    <a href="{{ Storage::url($ticket->image_path) }}" target="_blank">
                                        <img src="{{ Storage::url($ticket->image_path) }}" alt="Ticket image" class="max-w-full h-auto rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Comments ({{ $ticket->comments->where('is_internal', false)->count() }})
                            </h3>

                            @php
                                $visibleComments = Auth::user()->isHomeowner()
                                    ? $ticket->comments->where('is_internal', false)
                                    : $ticket->comments;
                            @endphp

                            @if ($visibleComments->isEmpty())
                                <p class="text-gray-500 text-center py-4">No comments yet.</p>
                            @else
                                <div class="space-y-4">
                                    @foreach ($visibleComments as $comment)
                                        <div class="border rounded-lg p-4 {{ $comment->is_internal ? 'bg-yellow-50 border-yellow-200' : 'bg-gray-50' }}">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-900">{{ $comment->user->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                                    </div>
                                                    @if ($comment->is_internal)
                                                        <span class="px-2 py-1 text-xs font-semibold bg-yellow-200 text-yellow-800 rounded">
                                                            Internal Note
                                                        </span>
                                                    @endif
                                                </div>
                                                @if (Auth::id() === $comment->user_id || Auth::user()->isAdmin())
                                                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <p class="mt-3 text-gray-700 whitespace-pre-line">{{ $comment->comment }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Add Comment Form -->
                            <div class="mt-6 border-t pt-6">
                                <form method="POST" action="{{ route('comments.store', $ticket) }}">
                                    @csrf
                                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                        Add a Comment
                                    </label>
                                    <textarea name="comment" id="comment" rows="4" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('comment') border-red-500 @enderror"
                                        placeholder="Type your comment here...">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror

                                    @if (Auth::user()->isBuilder() || Auth::user()->isAdmin())
                                        <div class="mt-3">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">Mark as internal note (not visible to homeowner)</span>
                                            </label>
                                        </div>
                                    @endif

                                    <div class="mt-4">
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-150">
                                            Post Comment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Property Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Property</h3>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">{{ $ticket->property->address }}</p>
                                <p class="text-sm text-gray-600">{{ $ticket->property->city }}, {{ $ticket->property->state }} {{ $ticket->property->zip_code }}</p>
                                <a href="{{ route('properties.show', $ticket->property) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    View Property →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Details</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Submitted By</label>
                                    <p class="text-gray-900">{{ $ticket->user->name }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500">Created</label>
                                    <p class="text-gray-900">{{ $ticket->created_at->format('M j, Y g:i A') }}</p>
                                </div>

                                @if ($ticket->assignedTo)
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Assigned To</label>
                                        <p class="text-gray-900">{{ $ticket->assignedTo->name }}</p>
                                    </div>
                                @else
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Assignment</label>
                                        <p class="text-gray-500 italic">Not yet assigned</p>
                                    </div>
                                @endif

                                @if ($ticket->updated_at->ne($ticket->created_at))
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Last Updated</label>
                                        <p class="text-gray-900">{{ $ticket->updated_at->diffForHumans() }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline (Simple) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                            <div class="space-y-2">
                                @php
                                    $statuses = ['submitted', 'assigned', 'in_progress', 'complete', 'closed'];
                                    $currentIndex = array_search($ticket->status, $statuses);
                                @endphp
                                @foreach ($statuses as $index => $status)
                                    <div class="flex items-center space-x-3">
                                        @if ($index <= $currentIndex)
                                            <div class="w-4 h-4 bg-blue-600 rounded-full flex-shrink-0"></div>
                                        @else
                                            <div class="w-4 h-4 bg-gray-300 rounded-full flex-shrink-0"></div>
                                        @endif
                                        <span class="text-sm {{ $index <= $currentIndex ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
