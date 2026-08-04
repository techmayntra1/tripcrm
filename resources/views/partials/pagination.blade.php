<div class="d-flex align-items-center w-100">
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted">Show</span>
        <select class="form-select form-select-sm" style="width: 80px;" onchange="window.location.href=this.options[this.selectedIndex].getAttribute('data-url')">
            @foreach([10, 15, 25, 50, 100] as $size)
                <option data-url="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ request()->input('per_page', 15) == $size ? 'selected' : '' }}>{{ $size }}</option>
            @endforeach
        </select>
        <span class="text-muted">entries</span>
    </div>
    <div class="flex-grow-1 d-flex justify-content-center">
        @if($paginator->hasPages())
            {{ $paginator->onEachSide(1)->links('pagination::bootstrap-5') }}
        @endif
    </div>
    <div>
        <span class="text-muted text-nowrap">Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} entries</span>
    </div>
</div>
