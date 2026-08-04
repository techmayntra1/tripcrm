<div class="app-header header-shadow HeaderAnimation-appear">
    <div class="app-header__logo">
        <a href="javascript:void(0)" class="header-sidebar-text" id="toggleAllSections" style="text-decoration: none; cursor: pointer; flex: 1; text-align: center;">Collapse All</a>
        <div class="header__pane">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="bi bi-three-dots-vertical"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="app-header__content">
        <div class="app-header-left justify-content-center">
            <div class="header-title">
                <span class="text-warning fw-bold">TripMantra Travel Tourism Fz LLC</span>
            </div>
        </div>
        <div class="app-header-right">
            <div class="widget-content p-0">
                <div class="widget-content-wrapper">
                    <div class="widget-content-left me-3">
                        @php
                            $currentMonth = now()->month;
                            $currentYear = now()->year;
                            $currentFY = $currentMonth >= 4 ? ($currentYear . '-' . substr($currentYear + 1, 2)) : (($currentYear - 1) . '-' . substr($currentYear, 2));
                            $selectedFY = session('financial_year', 'current');
                        @endphp
                        <div class="d-flex align-items-center">
                            <span class="fw-bold me-3">FY</span>
                            <select id="financialYearSelect" class="form-select form-select-sm" style="min-width: 200px; border-radius: 6px; font-weight: 500; cursor: pointer;">
                                <option value="all" {{ $selectedFY == 'all' ? 'selected' : '' }}>All</option>
                                <option value="current" {{ $selectedFY == 'current' ? 'selected' : '' }}>Current FY ({{ $currentFY }})</option>
                                <option value="2025-26" {{ $selectedFY == '2025-26' ? 'selected' : '' }}>April 2025 - March 2026</option>
                                <option value="2024-25" {{ $selectedFY == '2024-25' ? 'selected' : '' }}>April 2024 - March 2025</option>
                                <option value="2023-24" {{ $selectedFY == '2023-24' ? 'selected' : '' }}>April 2023 - March 2024</option>
                            </select>
                        </div>
                    </div>
                    <div class="widget-content-left">
                        <div class="btn-group">
                            <a data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn" style="border: none;">
                                @if(auth()->user()->profile_photo_url)
                                    <img width="42" class="rounded-circle" src="{{ auth()->user()->profile_photo_url }}" alt="User Avatar" style="object-fit: cover; height: 42px;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                @endif
                                <i class="bi bi-chevron-down ms-2 opacity-8"></i>
                            </a>
                            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 200px; border-radius: 8px; border: 1px solid #e5e5e5;">
                                <div class="px-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size: 14px;color: #000 !important;">{{ auth()->user()->name ?? 'User' }}</div>
                                            <div class="text-muted" style="font-size: 12px;color: rgba(73, 80, 87, 0.75) !important;">{{ auth()->user()->role->name ?? 'No Role' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('admin.settings.account') }}" class="dropdown-item py-2" style="color: #495057 !important;">
                                    <i class="bi bi-gear me-2" style="color: #495057 !important;"></i> Settings
                                </a>
                                <div class="dropdown-divider m-0"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger" style="color: rgba(217, 37, 80) !important;">
                                        <i class="bi bi-box-arrow-right me-2" style="color: rgba(217, 37, 80) !important;"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fySelect = document.getElementById('financialYearSelect');
    if (fySelect) {
        fySelect.addEventListener('change', function() {
            fetch('{{ route("set.financial.year") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ financial_year: this.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        });
    }
});
</script>
