@extends('layouts.admin')

@section('title', 'Motorcycles')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Motorcycles</h1>
        <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle"></i> Add Category
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Filter by Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="all" {{ $selectedCategory === 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $selectedCategory == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Search</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control form-control-sm"
                           placeholder="Search by brand / model / plate">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-danger w-100" type="submit">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('admin.vehicles.partials.list', ['vehicles' => $vehicles])

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="#">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

