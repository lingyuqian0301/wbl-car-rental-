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

