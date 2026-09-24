{{-- Velzon vertical sidebar (reskin). All routes / hasModuleAccess gates preserved. --}}
<div class="app-menu navbar-menu">
    {{-- LOGO --}}
    <div class="navbar-brand-box">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm"><img src="{{ asset('logo.png') }}?v=2" alt="" height="28"></span>
            <span class="logo-lg"><img src="{{ asset('logo.png') }}?v=2" alt="TripMantra Travel Tourism Fz LLC" height="44"></span>
        </a>
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
            <span class="logo-sm"><img src="{{ asset('logo.png') }}?v=2" alt="" height="28"></span>
            <span class="logo-lg"><img src="{{ asset('logo.png') }}?v=2" alt="TripMantra Travel Tourism Fz LLC" height="44"></span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar" data-simplebar class="h-100">
        <div class="container-fluid">
            <ul class="navbar-nav" id="navbar-nav">

                <li class="menu-title sidebar-section-heading" data-section="main">
                    <span>Main</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="main">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                        </a>
                    </li>
                </div>

                @if(auth()->user()->hasModuleAccess('leads') || auth()->user()->hasModuleAccess('customers') || auth()->user()->hasModuleAccess('vendors') || auth()->user()->hasModuleAccess('meetings'))
                <li class="menu-title sidebar-section-heading" data-section="crm">
                    <span>CRM</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="crm">
                    @if(auth()->user()->hasModuleAccess('leads'))
                    <li class="nav-item">
                        <a href="{{ route('admin.leads.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i> <span>Leads</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('customers'))
                    <li class="nav-item">
                        <a href="{{ route('admin.customers.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                            <i class="bi bi-people-fill"></i> <span>Customers</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('vendors'))
                    <li class="nav-item">
                        <a href="{{ route('admin.vendors.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
                            <i class="bi bi-shop"></i> <span>Vendors</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('meetings'))
                    <li class="nav-item">
                        <a href="{{ route('admin.meetings.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.meetings.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-event"></i> <span>Meetings</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('trips') || auth()->user()->hasModuleAccess('tasks'))
                <li class="menu-title sidebar-section-heading" data-section="trips">
                    <span>Trips</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="trips">
                    @if(auth()->user()->hasModuleAccess('trips'))
                    <li class="nav-item">
                        <a href="{{ route('admin.trips.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.trips.*') ? 'active' : '' }}">
                            <i class="bi bi-kanban"></i> <span>Trips</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('tasks'))
                    <li class="nav-item">
                        <a href="{{ route('admin.tasks.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                            <i class="bi bi-list-task"></i> <span>Tasks</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('staff'))
                <li class="menu-title sidebar-section-heading" data-section="staff">
                    <span>Staff</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="staff">
                    <li class="nav-item">
                        <a href="{{ route('admin.staff.index') }}" class="nav-link menu-link {{ (request()->routeIs('admin.staff.*') && !request()->routeIs('admin.staff.salary-payments.*') && !request()->routeIs('admin.staff.advances.*')) ? 'active' : '' }}">
                            <i class="bi bi-person-badge"></i> <span>Staff</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.salary.index') }}" class="nav-link menu-link {{ (request()->routeIs('admin.salary.*') || request()->routeIs('admin.staff.salary-payments.*') || request()->routeIs('admin.staff.advances.*')) ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i> <span>Salary</span>
                        </a>
                    </li>
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('quotations') || auth()->user()->hasModuleAccess('invoices'))
                <li class="menu-title sidebar-section-heading" data-section="billing">
                    <span>Billing</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="billing">
                    @if(auth()->user()->hasModuleAccess('quotations'))
                    <li class="nav-item">
                        <a href="{{ route('admin.quotations.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i> <span>Quotations</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('invoices'))
                    <li class="nav-item">
                        <a href="{{ route('admin.invoices.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i> <span>Invoices</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('income') || auth()->user()->hasModuleAccess('expenses') || auth()->user()->hasModuleAccess('banks') || auth()->user()->hasModuleAccess('companies'))
                <li class="menu-title sidebar-section-heading" data-section="accounts">
                    <span>Accounts</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="accounts">
                    @if(auth()->user()->hasModuleAccess('income'))
                    <li class="nav-item">
                        <a href="{{ route('admin.income.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.income.*') ? 'active' : '' }}">
                            <i class="bi bi-graph-up-arrow"></i> <span>Income</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('expenses'))
                    <li class="nav-item">
                        <a href="{{ route('admin.expenses.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                            <i class="bi bi-graph-down-arrow"></i> <span>Expenses</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('banks'))
                    <li class="nav-item">
                        <a href="{{ route('admin.banks.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.banks.*') ? 'active' : '' }}">
                            <i class="bi bi-bank"></i> <span>Bank Accounts</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('companies'))
                    <li class="nav-item">
                        <a href="{{ route('admin.companies.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                            <i class="bi bi-building"></i> <span>Companies</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('masters'))
                <li class="menu-title sidebar-section-heading" data-section="masters">
                    <span>Masters</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="masters">

                    <li class="sidebar-subheading">CRM</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.work-leads') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.work-leads*') ? 'active' : '' }}">
                            <i class="bi bi-bullseye"></i> <span>Lead Sources</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.lead-statuses') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.lead-statuses*') ? 'active' : '' }}">
                            <i class="bi bi-flag"></i> <span>Lead Statuses</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.meeting-purposes') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.meeting-purposes*') ? 'active' : '' }}">
                            <i class="bi bi-chat-dots"></i> <span>Meeting Purposes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.update-types') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.update-types*') ? 'active' : '' }}">
                            <i class="bi bi-pencil-square"></i> <span>Update Types</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.vendor-categories') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.vendor-categories*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i> <span>Vendor Categories</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.services') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.services*') ? 'active' : '' }}">
                            <i class="bi bi-tools"></i> <span>Services</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.passenger-types') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.passenger-types*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> <span>Passenger Types</span>
                        </a>
                    </li>

                    <li class="sidebar-subheading">Trips</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.work-types') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.work-types*') ? 'active' : '' }}">
                            <i class="bi bi-briefcase"></i> <span>Work Types</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.trip-statuses') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.trip-statuses*') ? 'active' : '' }}">
                            <i class="bi bi-kanban"></i> <span>Trip Statuses</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.task-statuses') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.task-statuses*') ? 'active' : '' }}">
                            <i class="bi bi-list-task"></i> <span>Task Statuses</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.staff-positions') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.staff-positions*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge"></i> <span>Staff Positions</span>
                        </a>
                    </li>

                    <li class="sidebar-subheading">Finance</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.units') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.units*') ? 'active' : '' }}">
                            <i class="bi bi-rulers"></i> <span>Units</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.gst-rates') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.gst-rates*') ? 'active' : '' }}">
                            <i class="bi bi-percent"></i> <span>GST Rates</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.payment-modes') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.payment-modes*') ? 'active' : '' }}">
                            <i class="bi bi-credit-card"></i> <span>Payment Modes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.expense-categories') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.expense-categories*') ? 'active' : '' }}">
                            <i class="bi bi-wallet2"></i> <span>Expense Categories</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.expense-types') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.expense-types*') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i> <span>Expense Types</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.term-templates', 'terms-conditions') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.term-templates*') && request()->route('type') === 'terms-conditions' ? 'active' : '' }}">
                            <i class="bi bi-file-text"></i> <span>Terms &amp; Conditions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.masters.term-templates', 'payment-terms') }}" class="nav-link menu-link {{ request()->routeIs('admin.masters.term-templates*') && request()->route('type') === 'payment-terms' ? 'active' : '' }}">
                            <i class="bi bi-cash-coin"></i> <span>Payment Terms</span>
                        </a>
                    </li>
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('users') || auth()->user()->hasModuleAccess('roles'))
                <li class="menu-title sidebar-section-heading" data-section="users">
                    <span>User Management</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="users">
                    @if(auth()->user()->hasModuleAccess('users'))
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> <span>Users</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('roles'))
                    <li class="nav-item">
                        <a href="{{ route('admin.roles.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i> <span>Roles &amp; Permissions</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                <li class="menu-title sidebar-section-heading" data-section="settings">
                    <span>Settings</span> <i class="bi bi-chevron-down section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="settings">
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.account') }}" class="nav-link menu-link {{ request()->routeIs('admin.settings.account') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i> <span>Account Settings</span>
                        </a>
                    </li>
                </div>
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>

<style>
/* Collapsible section headings (trip feature preserved on Velzon menu-title) */
.navbar-menu .sidebar-section-heading {
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
    letter-spacing: 0.4px;
}
.navbar-menu .sidebar-section-heading .section-icon {
    transition: transform 0.2s ease;
    font-size: 0.7rem;
}
.navbar-menu .sidebar-section-heading.collapsed .section-icon {
    transform: rotate(-90deg);
}
.navbar-menu .sidebar-section {
    overflow: hidden;
    transition: max-height 0.3s ease;
}
.navbar-menu .sidebar-section.collapsed {
    max-height: 0 !important;
}
.navbar-menu .sidebar-subheading {
    font-size: 0.78rem;
    text-transform: uppercase;
    padding: 10px 22px 4px;
    margin-top: 4px;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: rgba(255, 255, 255, 0.4);
}

/* White sidebar font (links, icons, section headings) */
.navbar-menu .nav-link,
.navbar-menu .nav-link span,
.navbar-menu .nav-link i,
.navbar-menu .sidebar-section-heading,
.navbar-menu .sidebar-section-heading span,
.navbar-menu .sidebar-section-heading i {
    color: #ffffff !important;
}

/* Active menu item: white background with contrasting (dark) text + icon */
.navbar-menu .nav-link.active,
.navbar-menu .nav-link.active span,
.navbar-menu .nav-link.active i {
    background-color: #ffffff !important;
    color: #405189 !important;
    font-weight: 600;
}
.navbar-menu .nav-link.active {
    border-radius: 6px;
}
.navbar-menu .nav-link:hover:not(.active),
.navbar-menu .nav-link:hover:not(.active) span,
.navbar-menu .nav-link:hover:not(.active) i {
    color: #ffffff !important;
    background-color: rgba(255, 255, 255, 0.12);
    border-radius: 6px;
}
</style>

<script>
(function () {
    'use strict';
    const storageKey = 'sidebarCollapsedSections';

    function load() {
        try { return JSON.parse(localStorage.getItem(storageKey) || '[]'); }
        catch (e) { return []; }
    }
    function save(list) {
        localStorage.setItem(storageKey, JSON.stringify(list));
    }
    function names() {
        return Array.from(document.querySelectorAll('.sidebar-section'))
            .map(s => s.dataset.section);
    }

    // Apply the persisted collapsed state to the DOM. Runs on load AND again
    // whenever Velzon's app.js resets .navbar-menu.innerHTML (a MutationObserver
    // re-fires it), so section state and open-heights always survive a reset.
    function apply() {
        const collapsed = load();
        document.querySelectorAll('.sidebar-section').forEach(function (section) {
            const name = section.dataset.section;
            // A section holding the active link is always forced open, so the
            // main menu of the active sub-menu is expanded regardless of any
            // previously-stored collapsed state.
            const hasActive = !!section.querySelector('.nav-link.active');
            const isCollapsed = hasActive ? false : collapsed.includes(name);
            // Give the open section an explicit height for the CSS transition.
            // .collapsed forces max-height:0 !important so this is ignored while shut.
            if (!isCollapsed) section.style.maxHeight = section.scrollHeight + 'px';
            section.classList.toggle('collapsed', isCollapsed);
            const heading = document.querySelector(
                '.sidebar-section-heading[data-section="' + name + '"]');
            if (heading) heading.classList.toggle('collapsed', isCollapsed);
        });
    }

    // Toggle one section by name.
    function toggleOne(name) {
        let collapsed = load();
        const willCollapse = !collapsed.includes(name);
        if (willCollapse) collapsed.push(name);
        else collapsed = collapsed.filter(n => n !== name);
        save(collapsed);
        apply();
    }

    // Collapse everything, or expand everything if all are already collapsed.
    function toggleAll() {
        const all = names();
        const collapsed = load();
        save(collapsed.length >= all.length ? [] : all);
        apply();
    }

    // --- Section-heading clicks: delegated so they survive a menu reset. ----
    document.addEventListener('click', function (e) {
        const heading = e.target.closest('.sidebar-section-heading');
        if (heading && heading.dataset.section) {
            toggleOne(heading.dataset.section);
        }
    });

    // --- Hamburger drives collapse/expand-all on desktop --------------------
    // Bound in the CAPTURE phase with stopImmediatePropagation so Velzon's own
    // hamburger handler (which shrinks the sidebar to icon-only) never runs on
    // desktop. Below lg we let Velzon handle it (mobile off-canvas toggle).
    const hamburger = document.getElementById('topnav-hamburger-icon');
    if (hamburger) {
        hamburger.addEventListener('click', function (e) {
            if (window.innerWidth >= 992) {
                e.preventDefault();
                e.stopImmediatePropagation();
                document.documentElement.setAttribute('data-sidebar-size', 'lg');
                toggleAll();
            }
        }, true);
    }

    // --- Re-apply state after Velzon's app.js resets the menu DOM -----------
    const menu = document.querySelector('.navbar-menu');
    if (menu && window.MutationObserver) {
        let raf = null;
        new MutationObserver(function () {
            // Debounce: coalesce the burst of innerHTML mutations into one apply.
            if (raf) cancelAnimationFrame(raf);
            raf = requestAnimationFrame(apply);
        }).observe(menu, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply);
    } else {
        apply();
    }
})();
</script>
