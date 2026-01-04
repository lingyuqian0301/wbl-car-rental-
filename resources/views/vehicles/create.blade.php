@extends('vehicles.layouts.app')

@section('title', 'Add New Vehicle')

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-2xl font-semibold text-gray-800">Add New Vehicle</h2>
    </div>

    <div class="p-6">
        <form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                    
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                        <input type="text" name="brand" id="brand" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                        <input type="text" name="model" id="model" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="registration_number" class="block text-sm font-medium text-gray-700">Registration Number</label>
                        <input type="text" name="registration_number" id="registration_number" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="item_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="item_category_id" id="item_category_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach(\App\Models\ItemCategory::all() as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Pricing & Status -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Pricing & Status</h3>
                    
                    <div>
                        <label for="daily_rate" class="block text-sm font-medium text-gray-700">Daily Rate (RM)</label>
                        <input type="number" step="0.01" name="daily_rate" id="daily_rate" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div>
                        <label for="current_mileage" class="block text-sm font-medium text-gray-700">Current Mileage (km)</label>
                        <input type="number" name="current_mileage" id="current_mileage"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <!-- Photos -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Photos</h3>
                    <div class="mt-1 flex items-center">
                        <input type="file" name="photos[]" id="photos" multiple
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700
                                      hover:file:bg-blue-100">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Upload one or more photos of the vehicle.</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('vehicles.index') }}" 
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Vehicle
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Include any additional JavaScript for file upload previews -->
@push('scripts')
<script>
    // File upload preview (optional)
    document.getElementById('photos').addEventListener('change', function(e) {
        const files = e.target.files;
        console.log(files.length + ' files selected');
        // You can add preview functionality here
    });
</script>
@endpush
@endsection
