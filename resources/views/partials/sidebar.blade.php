<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        <div class="logo-src"></div>
        <div class="header__pane ms-auto">
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
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <img src="{{ asset('logo.png') }}" alt="TripMantra Travel Tourism Fz LLC" class="width-100">
                <li class="app-sidebar__heading sidebar-section-heading" data-section="main">
                    Main <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="main">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'mm-active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </div>

                @if(auth()->user()->hasModuleAccess('leads') || auth()->user()->hasModuleAccess('customers') || auth()->user()->hasModuleAccess('vendors') || auth()->user()->hasModuleAccess('meetings'))
                <li class="app-sidebar__heading sidebar-section-heading" data-section="crm">
                    CRM <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="crm">
                    @if(auth()->user()->hasModuleAccess('leads'))
                    <li>
                        <a href="{{ route('admin.leads.index') }}" class="{{ request()->routeIs('admin.leads.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Leads</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('customers'))
                    <li>
                        <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-people-fill"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('vendors'))
                    <li>
                        <a href="{{ route('admin.vendors.index') }}" class="{{ request()->routeIs('admin.vendors.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-shop"></i>
                            <span>Vendors</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('meetings'))
                    <li>
                        <a href="{{ route('admin.meetings.index') }}" class="{{ request()->routeIs('admin.meetings.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-calendar-event"></i>
                            <span>Meetings</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('projects') || auth()->user()->hasModuleAccess('tasks'))
                <li class="app-sidebar__heading sidebar-section-heading" data-section="projects">
                    Trips <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="projects">
                    @if(auth()->user()->hasModuleAccess('projects'))
                    <li>
                        <a href="{{ route('admin.projects.index') }}" class="{{ request()->routeIs('admin.projects.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-kanban"></i>
                            <span>Trips</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('tasks'))
                    <li>
                        <a href="{{ route('admin.tasks.index') }}" class="{{ request()->routeIs('admin.tasks.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-list-task"></i>
                            <span>Tasks</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('staff'))
                <li class="app-sidebar__heading sidebar-section-heading" data-section="staff">
                    Staff <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="staff">
                    <li>
                        <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-person-badge"></i>
                            <span>Staff & Salary</span>
                        </a>
                    </li>
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('quotations') || auth()->user()->hasModuleAccess('invoices'))
                <li class="app-sidebar__heading sidebar-section-heading" data-section="billing">
                    Billing <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="billing">
                    @if(auth()->user()->hasModuleAccess('quotations'))
                    <li>
                        <a href="{{ route('admin.quotations.index') }}" class="{{ request()->routeIs('admin.quotations.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Quotations</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('invoices'))
                    <li>
                        <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-receipt"></i>
                            <span>Invoices</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('income') || auth()->user()->hasModuleAccess('expenses') || auth()->user()->hasModuleAccess('banks'))
                <li class="app-sidebar__heading sidebar-section-heading" data-section="accounts">
                    Accounts <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="accounts">
                    @if(auth()->user()->hasModuleAccess('income'))
                    <li>
                        <a href="{{ route('admin.income.index') }}" class="{{ request()->routeIs('admin.income.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Income</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('expenses'))
                    <li>
                        <a href="{{ route('admin.expenses.index') }}" class="{{ request()->routeIs('admin.expenses.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-graph-down-arrow"></i>
                            <span>Expenses</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('banks'))
                    <li>
                        <a href="{{ route('admin.banks.index') }}" class="{{ request()->routeIs('admin.banks.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-bank"></i>
                            <span>Bank Accounts</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('masters'))
                <li class="app-sidebar__heading sidebar-section-heading" data-section="masters">
                    Masters <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="masters">
                    
                    <li class="sidebar-subheading">CRM</li>
                    <li>
                        <a href="{{ route('admin.masters.work-leads') }}" class="{{ request()->routeIs('admin.masters.work-leads*') ? 'mm-active' : '' }}">
                            <i class="bi bi-bullseye"></i>
                            <span>Lead Sources</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.lead-statuses') }}" class="{{ request()->routeIs('admin.masters.lead-statuses*') ? 'mm-active' : '' }}">
                            <i class="bi bi-flag"></i>
                            <span>Lead Statuses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.meeting-purposes') }}" class="{{ request()->routeIs('admin.masters.meeting-purposes*') ? 'mm-active' : '' }}">
                            <i class="bi bi-chat-dots"></i>
                            <span>Meeting Purposes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.update-types') }}" class="{{ request()->routeIs('admin.masters.update-types*') ? 'mm-active' : '' }}">
                            <i class="bi bi-pencil-square"></i>
                            <span>Update Types</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.vendor-categories') }}" class="{{ request()->routeIs('admin.masters.vendor-categories*') ? 'mm-active' : '' }}">
                            <i class="bi bi-tags"></i>
                            <span>Vendor Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.services') }}" class="{{ request()->routeIs('admin.masters.services*') ? 'mm-active' : '' }}">
                            <i class="bi bi-tools"></i>
                            <span>Services</span>
                        </a>
                    </li>

                    <li class="sidebar-subheading">Trips</li>
                    <li>
                        <a href="{{ route('admin.masters.work-types') }}" class="{{ request()->routeIs('admin.masters.work-types*') ? 'mm-active' : '' }}">
                            <i class="bi bi-briefcase"></i>
                            <span>Work Types</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.project-statuses') }}" class="{{ request()->routeIs('admin.masters.project-statuses*') ? 'mm-active' : '' }}">
                            <i class="bi bi-kanban"></i>
                            <span>Trip Statuses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.task-statuses') }}" class="{{ request()->routeIs('admin.masters.task-statuses*') ? 'mm-active' : '' }}">
                            <i class="bi bi-list-task"></i>
                            <span>Task Statuses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.staff-positions') }}" class="{{ request()->routeIs('admin.masters.staff-positions*') ? 'mm-active' : '' }}">
                            <i class="bi bi-person-badge"></i>
                            <span>Staff Positions</span>
                        </a>
                    </li>

                    <li class="sidebar-subheading">Finance</li>
                    <li>
                        <a href="{{ route('admin.masters.units') }}" class="{{ request()->routeIs('admin.masters.units*') ? 'mm-active' : '' }}">
                            <i class="bi bi-rulers"></i>
                            <span>Units</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.gst-rates') }}" class="{{ request()->routeIs('admin.masters.gst-rates*') ? 'mm-active' : '' }}">
                            <i class="bi bi-percent"></i>
                            <span>GST Rates</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.payment-modes') }}" class="{{ request()->routeIs('admin.masters.payment-modes*') ? 'mm-active' : '' }}">
                            <i class="bi bi-credit-card"></i>
                            <span>Payment Modes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.expense-categories') }}" class="{{ request()->routeIs('admin.masters.expense-categories*') ? 'mm-active' : '' }}">
                            <i class="bi bi-wallet2"></i>
                            <span>Expense Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.masters.expense-types') }}" class="{{ request()->routeIs('admin.masters.expense-types*') ? 'mm-active' : '' }}">
                            <i class="bi bi-cash-stack"></i>
                            <span>Expense Types</span>
                        </a>
                    </li>
                </div>
                @endif

                @if(auth()->user()->hasModuleAccess('users') || auth()->user()->hasModuleAccess('roles'))
                <li class="app-sidebar__heading sidebar-section-heading" data-section="users">
                    User Management <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="users">
                    @if(auth()->user()->hasModuleAccess('users'))
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasModuleAccess('roles'))
                    <li>
                        <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'mm-active' : '' }}">
                            <i class="bi bi-shield-lock"></i>
                            <span>Roles & Permissions</span>
                        </a>
                    </li>
                    @endif
                </div>
                @endif

                <li class="app-sidebar__heading sidebar-section-heading" data-section="settings">
                    Settings <i class="bi bi-chevron-down float-end section-icon"></i>
                </li>
                <div class="sidebar-section" data-section="settings">
                    <li>
                        <a href="{{ route('admin.settings.account') }}" class="{{ request()->routeIs('admin.settings.account') ? 'mm-active' : '' }}">
                            <i class="bi bi-person-gear"></i>
                            <span>Account Settings</span>
                        </a>
                    </li>
                </div>
            </ul>
        </div>
    </div>
</div>

<style>
.sidebar-section-heading {
    cursor: pointer;
    user-select: none;
}
.sidebar-section-heading:hover {
    opacity: 0.8;
}
.sidebar-section-heading .section-icon {
    transition: transform 0.2s ease;
    font-size: 0.7rem;
}
.sidebar-section-heading.collapsed .section-icon {
    transform: rotate(-90deg);
}
.sidebar-section {
    overflow: hidden;
    transition: max-height 0.3s ease;
}
.sidebar-section.collapsed {
    max-height: 0 !important;
}
.closed-sidebar .sidebar-section.collapsed {
    max-height: none !important;
}
.sidebar-subheading {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #ffffff;
    padding: 8px 15px 4px 15px;
    margin-top: 5px;
    font-weight: 600;
    letter-spacing: 0.5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.sidebar-section');
    const headings = document.querySelectorAll('.sidebar-section-heading');
    const toggleAllBtn = document.getElementById('toggleAllSections');
    const storageKey = 'sidebarCollapsedSections';

    let collapsedSections = JSON.parse(localStorage.getItem(storageKey) || '[]');

    sections.forEach(function(section) {
        const sectionName = section.dataset.section;
        section.style.maxHeight = section.scrollHeight + 'px';

        if (collapsedSections.includes(sectionName)) {
            section.classList.add('collapsed');
            const heading = document.querySelector('.sidebar-section-heading[data-section="' + sectionName + '"]');
            if (heading) heading.classList.add('collapsed');
        }
    });

    headings.forEach(function(heading) {
        heading.addEventListener('click', function() {
            const sectionName = this.dataset.section;
            const section = document.querySelector('.sidebar-section[data-section="' + sectionName + '"]');

            if (section) {
                section.classList.toggle('collapsed');
                this.classList.toggle('collapsed');

                if (section.classList.contains('collapsed')) {
                    if (!collapsedSections.includes(sectionName)) {
                        collapsedSections.push(sectionName);
                    }
                } else {
                    collapsedSections = collapsedSections.filter(s => s !== sectionName);
                }
                localStorage.setItem(storageKey, JSON.stringify(collapsedSections));
                updateToggleAllText();
            }
        });
    });

    if (toggleAllBtn) toggleAllBtn.addEventListener('click', function() {
        const allCollapsed = sections.length === collapsedSections.length;

        if (allCollapsed) {
            sections.forEach(function(section) {
                section.classList.remove('collapsed');
            });
            headings.forEach(function(heading) {
                heading.classList.remove('collapsed');
            });
            collapsedSections = [];
        } else {
            sections.forEach(function(section) {
                section.classList.add('collapsed');
            });
            headings.forEach(function(heading) {
                heading.classList.add('collapsed');
            });
            collapsedSections = Array.from(sections).map(s => s.dataset.section);
        }

        localStorage.setItem(storageKey, JSON.stringify(collapsedSections));
        updateToggleAllText();
    });

    function updateToggleAllText() {
        const allCollapsed = sections.length === collapsedSections.length;
        if (toggleAllBtn) {
            toggleAllBtn.textContent = allCollapsed ? 'Expand All' : 'Collapse All';
        }
    }

    updateToggleAllText();
});
</script>
