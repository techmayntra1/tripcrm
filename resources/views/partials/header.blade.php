{{-- Velzon topbar (reskin). FY selector, user dropdown, logout form preserved verbatim. --}}
<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex align-items-center">
                {{-- Horizontal logo (mobile) --}}
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
                        <span class="logo-lg"><img src="{{ asset('logo.png') }}?v=2" alt="" height="36"></span>
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
                        <span class="logo-lg"><img src="{{ asset('logo.png') }}?v=2" alt="" height="36"></span>
                    </a>
                </div>

                {{-- Sidebar toggle (Velzon vertical hamburger) --}}
                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                {{-- App title --}}
                <div class="d-none d-md-flex align-items-center ms-2">
                    <span class="fw-bold fs-16 text-primary">TripMantra Travel Tourism Fz LLC</span>
                </div>
            </div>

            <div class="d-flex align-items-center">
                {{-- Financial Year selector --}}
                @php
                    $currentMonth = now()->month;
                    $currentYear = now()->year;
                    $currentFY = $currentMonth >= 4 ? ($currentYear . '-' . substr($currentYear + 1, 2)) : (($currentYear - 1) . '-' . substr($currentYear, 2));
                    $selectedFY = session('financial_year', 'current');
                @endphp
                <div class="d-flex align-items-center me-3">
                    <span class="fw-bold me-2 d-none d-sm-inline">FY</span>
                    <select id="financialYearSelect" class="form-select form-select-sm" style="min-width: 190px; font-weight: 500; cursor: pointer;">
                        <option value="all" {{ $selectedFY == 'all' ? 'selected' : '' }}>All</option>
                        <option value="current" {{ $selectedFY == 'current' ? 'selected' : '' }}>Current FY ({{ $currentFY }})</option>
                        <option value="2025-26" {{ $selectedFY == '2025-26' ? 'selected' : '' }}>April 2025 - March 2026</option>
                        <option value="2024-25" {{ $selectedFY == '2024-25' ? 'selected' : '' }}>April 2024 - March 2025</option>
                        <option value="2023-24" {{ $selectedFY == '2023-24' ? 'selected' : '' }}>April 2023 - March 2024</option>
                    </select>
                </div>

                {{-- User dropdown --}}
                <div class="dropdown ms-sm-2 header-item topbar-user">
                    <button type="button" class="btn" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            @if(auth()->user()->profile_photo_url)
                                <img class="rounded-circle header-profile-user" src="{{ auth()->user()->profile_photo_url }}" alt="User Avatar" style="object-fit: cover;">
                            @else
                                <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center header-profile-user">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                            @endif
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ auth()->user()->name ?? 'User' }}</span>
                                <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text">{{ auth()->user()->role->name ?? 'No Role' }}</span>
                            </span>
                            <i class="bi bi-chevron-down ms-2 d-none d-sm-inline"></i>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Welcome {{ auth()->user()->name ?? 'User' }}!</h6>
                        <a href="{{ route('admin.settings.account') }}" class="dropdown-item">
                            <i class="bi bi-gear text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right text-danger fs-16 align-middle me-1"></i>
                                <span class="align-middle">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

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
