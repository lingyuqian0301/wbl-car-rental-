@extends('layouts.admin')

@section('title', 'Cars')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Cars</h1>
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
                        <option value="all" {{ request('category') === 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Vehicle list</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Category</th>
                        <th>Plate</th>
                        <th>Daily rate</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->id }}</td>
                            <td>{{ $vehicle->full_model }}</td>
                            <td>
                                <span class="badge bg-info">{{ $vehicle->category->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td>{{ $vehicle->registration_number }}</td>
                            <td>RM {{ number_format($vehicle->daily_rate, 2) }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $vehicle->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-danger">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">No vehicles found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($vehicles instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="card-footer">
                {{ $vehicles->withQueryString()->links() }}
            </div>
        @endif
    </div>

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

