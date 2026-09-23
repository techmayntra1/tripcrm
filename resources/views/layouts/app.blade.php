<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Language" content="en">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'CRM & Accounts Management System')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32.png') }}?v=2" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">

    {{-- Velzon layout config (must run before paint) --}}
    <script src="{{ asset('velzon/js/layout.js') }}"></script>

    {{-- Velzon core theme --}}
    <link href="{{ asset('velzon/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/app.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('velzon/css/custom.min.css') }}" rel="stylesheet" type="text/css">

    {{-- Trip plugin styles (kept) --}}
    <link href="{{ asset('assets/styles/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/styles/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/styles/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/styles/flatpickr.min.css') }}" rel="stylesheet">

    {{-- Trip-specific tweaks + Velzon compatibility shims --}}
    <link href="{{ asset('assets/styles/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/styles/velzon-compat.css') }}" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @if(session('success') || session('error') || session('warning') || session('info'))
    <script>
        window.__koFlash = {
            @if(session('success')) success: @json(session('success')), @endif
            @if(session('error')) error: @json(session('error')), @endif
            @if(session('warning')) warning: @json(session('warning')), @endif
            @if(session('info')) info: @json(session('info')), @endif
        };
    </script>
    @endif
</head>
{{-- Pages tied to one company/bank set @section('currency_symbol', currencySymbol($model)) --}}
<body data-currency-symbol="@yield('currency_symbol', '₹')">
    <div id="layout-wrapper">

        @include('partials.header')

        @include('partials.sidebar')

        {{-- Stub for Velzon app.js: its layout setter y() does
             getElementById('two-column-menu').innerHTML='' unguarded and
             throws (this app has no two-column menu). An empty hidden node
             satisfies it so the console stays clean. --}}
        <div id="two-column-menu" class="d-none"></div>

        <div class="vertical-overlay"></div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            @include('partials.footer')
        </div>

    </div>
    <!-- END layout-wrapper -->

    @stack('modals')

    {{-- jQuery (trip) --}}
    <script src="{{ asset('assets/scripts/jquery.min.js') }}"></script>

    {{-- Velzon core scripts --}}
    <script src="{{ asset('velzon/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('velzon/libs/feather-icons/feather.min.js') }}"></script>
    {{-- velzon/js/plugins.js removed: it document.write()s unused demo libs
         (choices.js, flatpickr, toastify) with relative paths that 404 on
         every page. This app loads its own select2/flatpickr below. --}}
    <script src="{{ asset('velzon/js/app.js') }}"></script>

    {{-- Trip plugins & logic (kept) --}}
    <script src="{{ asset('assets/scripts/select2.min.js') }}"></script>
    <script src="{{ asset('assets/scripts/indian-cities.js') }}"></script>
    <script src="{{ asset('assets/scripts/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/scripts/custom.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[type="date"]').forEach(function(el) {
                flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd-m-Y',
                    allowInput: true
                });
            });

            const globalFYSelect = document.getElementById('globalFinancialYear');
            const savedFY = localStorage.getItem('selectedFinancialYear') || '2025-2026';

            if (globalFYSelect) {
                globalFYSelect.value = savedFY;

                document.querySelectorAll('.financial-year-select').forEach(function(select) {
                    select.value = savedFY;
                });

                globalFYSelect.addEventListener('change', function() {
                    const selectedFY = this.value;
                    localStorage.setItem('selectedFinancialYear', selectedFY);

                    document.querySelectorAll('.financial-year-select').forEach(function(select) {
                        select.value = selectedFY;
                    });

                    location.reload();
                });
            }

            document.querySelectorAll('.financial-year-select').forEach(function(select) {
                select.value = savedFY;
                select.addEventListener('change', function() {
                    localStorage.setItem('selectedFinancialYear', this.value);
                    if (globalFYSelect) {
                        globalFYSelect.value = this.value;
                    }
                    location.reload();
                });
            });
        });

        // Currency symbol follows the selected company/bank region (INR ₹ vs AED).
        // A <select class="js-currency-source"> whose <option>s carry data-currency drives
        // every .js-currency-symbol on the page. window.currencySymbol() gives the current one to scripts.
        (function() {
            var current = document.body.dataset.currencySymbol || '₹';
            window.currencySymbol = function() { return current; };

            function apply(symbol) {
                current = symbol || '₹';
                document.querySelectorAll('.js-currency-symbol').forEach(function(el) { el.textContent = current; });
                document.dispatchEvent(new CustomEvent('currency:changed', { detail: { symbol: current } }));
            }

            document.addEventListener('DOMContentLoaded', function() {
                var sources = document.querySelectorAll('select.js-currency-source');
                if (!sources.length) { apply(current); return; }
                sources.forEach(function(select) {
                    select.addEventListener('change', function() {
                        var opt = select.options[select.selectedIndex];
                        apply(opt && opt.dataset.currency ? opt.dataset.currency : select.dataset.currencyDefault);
                    });
                });
                var first = sources[0];
                var opt = first.options[first.selectedIndex];
                apply(opt && opt.dataset.currency ? opt.dataset.currency : (first.dataset.currencyDefault || current));
            });
        })();

        document.addEventListener('shown.bs.modal', function(e) {
            e.target.querySelectorAll('input[type="date"]:not(.flatpickr-input)').forEach(function(el) {
                flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd-m-Y',
                    allowInput: true
                });
            });
        });

        function getCurrentFYDates() {
            const fy = localStorage.getItem('selectedFinancialYear') || '2025-2026';
            const [startYear, endYear] = fy.split('-');
            return {
                start: startYear + '-04-01',
                end: endYear + '-03-31',
                label: 'FY ' + startYear.slice(-2) + '-' + endYear.slice(-2)
            };
        }

        document.querySelectorAll('.name-truncate[title], .email-truncate[title], .desc-truncate[title], .address-truncate[title], .badge[title]').forEach(function(el) {
            var title = el.getAttribute('title');
            if (title) {
                el.setAttribute('data-tooltip', title);
                el.removeAttribute('title');
            }
        });
    </script>
    @stack('scripts')

    <!-- Notification Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="notificationToastContainer" style="z-index: 9999;"></div>
    <style>
        #notificationToastContainer .toast {
            display: block;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.4s ease-in-out;
            min-width: 340px;
            max-width: 400px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            margin-bottom: 12px;
            border-radius: 12px;
            overflow: hidden;
            border: none;
        }
        #notificationToastContainer .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        #notificationToastContainer .toast .toast-header {
            border-radius: 0;
            padding: 12px 16px;
            border-bottom: none;
        }
        #notificationToastContainer .toast .toast-header i {
            font-size: 1.1rem;
        }
        #notificationToastContainer .toast .toast-header .btn-close {
            opacity: 0.8;
            font-size: 0.75rem;
        }
        #notificationToastContainer .toast .toast-body {
            background: #fff;
            padding: 16px;
        }
        #notificationToastContainer .toast .toast-body strong {
            font-size: 1.05rem;
            color: #333;
        }
        #notificationToastContainer .toast .toast-body p {
            color: #666;
        }
        #notificationToastContainer .toast .toast-body .btn {
            margin-top: 8px;
        }
        .text-bg-info { background-color: #17a2b8 !important; color: #fff !important; }
        .text-bg-primary { background-color: #3f6ad8 !important; color: #fff !important; }
        .text-bg-warning { background-color: #f7b924 !important; color: #000 !important; }
        .text-bg-danger { background-color: #dc3545 !important; color: #fff !important; }
        @keyframes pulse-border {
            0% { box-shadow: 0 8px 24px rgba(0,0,0,0.2), 0 0 0 0 rgba(23, 162, 184, 0.7); }
            70% { box-shadow: 0 8px 24px rgba(0,0,0,0.2), 0 0 0 10px rgba(23, 162, 184, 0); }
            100% { box-shadow: 0 8px 24px rgba(0,0,0,0.2), 0 0 0 0 rgba(23, 162, 184, 0); }
        }
        #notificationToastContainer .toast.show.pulse {
            animation: pulse-border 1.5s infinite;
        }
    </style>

    <!-- Notification System -->
    <script>
    (function() {
        const NOTIFY_BEFORE_MS = 60 * 60 * 1000; // 1 hour before
        const REFRESH_INTERVAL_MS = 60 * 1000; // Refresh every 1 minute
        const STORAGE_KEY = 'notified_items';
        const AUDIO_UNLOCKED_KEY = 'audio_unlocked';

        let scheduledTimeouts = {};
        let activeAlarms = {}; // Store active alarm audio elements
        let audioUnlocked = localStorage.getItem(AUDIO_UNLOCKED_KEY) === 'true';
        const NOTIFICATION_SOUND_URL = '{{ asset("assets/sounds/notification.wav") }}';

        // Request notification permission on page load
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Unlock audio on first user interaction
        function unlockAudio() {
            if (audioUnlocked) return;
            const silentAudio = new Audio(NOTIFICATION_SOUND_URL);
            silentAudio.volume = 0.01;
            silentAudio.play().then(() => {
                silentAudio.pause();
                audioUnlocked = true;
                localStorage.setItem(AUDIO_UNLOCKED_KEY, 'true');
            }).catch(() => {});
            document.removeEventListener('click', unlockAudio);
            document.removeEventListener('keydown', unlockAudio);
        }
        document.addEventListener('click', unlockAudio);
        document.addEventListener('keydown', unlockAudio);

        function getNotifiedItems() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            } catch {
                return {};
            }
        }

        function markAsNotified(id) {
            const items = getNotifiedItems();
            items[id] = Date.now();
            // Clean old entries (older than 24 hours)
            const oneDayAgo = Date.now() - (24 * 60 * 60 * 1000);
            Object.keys(items).forEach(key => {
                if (items[key] < oneDayAgo) delete items[key];
            });
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        }

        function wasNotified(id) {
            return getNotifiedItems().hasOwnProperty(id);
        }

        function startAlarm(notificationId) {
            if (activeAlarms[notificationId]) return;

            const audio = new Audio(NOTIFICATION_SOUND_URL);
            audio.loop = true;
            activeAlarms[notificationId] = audio;

            audio.play().catch(() => {});

            // Auto-stop after 20 seconds
            setTimeout(() => {
                stopAlarm(notificationId);
            }, 20000);
        }

        function stopAlarm(notificationId) {
            if (activeAlarms[notificationId]) {
                activeAlarms[notificationId].pause();
                activeAlarms[notificationId].currentTime = 0;
                delete activeAlarms[notificationId];
            }
        }

        function showToast(notification) {
            const container = document.getElementById('notificationToastContainer');
            if (!container) return;

            const toastId = 'toast_' + notification.id;
            const colorClass = {
                'primary': 'text-bg-primary',
                'warning': 'text-bg-warning',
                'info': 'text-bg-info',
                'success': 'text-bg-success',
                'danger': 'text-bg-danger'
            }[notification.color] || 'text-bg-secondary';

            // Dynamic label based on urgency
            let typeLabel;
            if (notification.urgent) {
                const mins = Math.round(notification.minutes_until);
                const timeText = mins <= 1 ? 'now' : `in ${mins} min`;
                typeLabel = {
                    'meeting': `Meeting ${timeText}`,
                    'task': `Task starts ${timeText}`,
                    'followup': `Follow-up ${timeText}`
                }[notification.type] || 'Reminder';
            } else {
                typeLabel = {
                    'meeting': 'Meeting in 1 hour',
                    'task': 'Task starts in 1 hour',
                    'followup': 'Follow-up in 1 hour'
                }[notification.type] || 'Reminder';
            }

            const toastHtml = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-notification-id="${notification.id}">
                    <div class="toast-header ${colorClass}">
                        <i class="bi ${notification.icon} me-2"></i>
                        <strong class="me-auto">${typeLabel}</strong>
                        <small class="ms-2">${notification.time_formatted}</small>
                        <button type="button" class="btn-close btn-close-white ms-2 notification-close" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <strong>${notification.title}</strong>
                                <p class="mb-0 small text-muted">${notification.subtitle}</p>
                            </div>
                            <a href="${notification.link}" class="btn btn-sm btn-primary ms-3 notification-view">View</a>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', toastHtml);
            const toastEl = document.getElementById(toastId);

            // Manually show toast with animation and pulse
            setTimeout(() => {
                toastEl.classList.add('show', 'pulse');
            }, 10);

            // Stop alarm and remove toast on View or Close
            const closeBtn = toastEl.querySelector('.notification-close');
            const viewBtn = toastEl.querySelector('.notification-view');
            let startAlarmOnce = null;
            let soundPlayed = false;

            const dismissNotification = () => {
                if (startAlarmOnce) {
                    document.removeEventListener('click', startAlarmOnce);
                }
                stopAlarm(notification.id);
                toastEl.classList.remove('show');
                setTimeout(() => toastEl.remove(), 400);
            };

            // Show browser notification (works even if tab is in background)
            if ('Notification' in window && Notification.permission === 'granted') {
                const browserNotif = new Notification(typeLabel, {
                    body: notification.title + ' - ' + notification.subtitle,
                    icon: '/assets/images/logo.png?v=2',
                    tag: notification.id,
                    requireInteraction: true
                });
                browserNotif.onclick = () => {
                    window.focus();
                    window.location.href = notification.link;
                };
            }

            // Try to play sound
            const audio = new Audio(NOTIFICATION_SOUND_URL);
            audio.loop = true;
            activeAlarms[notification.id] = audio;

            audio.play().then(() => {
                soundPlayed = true;
                setTimeout(() => stopAlarm(notification.id), 20000);
            }).catch(() => {
                // Autoplay blocked - try with user interaction
                const toastBody = toastEl.querySelector('.toast-body');
                const soundHint = document.createElement('div');
                soundHint.className = 'sound-hint text-muted small mt-2';
                soundHint.innerHTML = '<i class="bi bi-volume-up me-1"></i> Click to enable sound';
                soundHint.style.cursor = 'pointer';
                toastBody.appendChild(soundHint);

                const startSound = () => {
                    if (soundPlayed) return;
                    soundPlayed = true;
                    if (soundHint.parentNode) soundHint.remove();
                    if (!activeAlarms[notification.id]) {
                        const retryAudio = new Audio(NOTIFICATION_SOUND_URL);
                        retryAudio.loop = true;
                        activeAlarms[notification.id] = retryAudio;
                    }
                    activeAlarms[notification.id].play().catch(() => {});
                    setTimeout(() => stopAlarm(notification.id), 20000);
                };

                toastBody.addEventListener('click', (e) => {
                    if (!e.target.closest('.notification-view')) startSound();
                });

                startAlarmOnce = () => {
                    startSound();
                    document.removeEventListener('click', startAlarmOnce);
                };
                document.addEventListener('click', startAlarmOnce);
            });

            closeBtn.addEventListener('click', dismissNotification);
            viewBtn.addEventListener('click', () => {
                if (startAlarmOnce) {
                    document.removeEventListener('click', startAlarmOnce);
                }
                stopAlarm(notification.id);
            });

            markAsNotified(notification.id);
        }

        function scheduleNotification(notification, serverTime) {
            if (wasNotified(notification.id)) return;
            if (scheduledTimeouts[notification.id]) return;

            // Urgent notifications (within 1 hour) - show immediately
            if (notification.urgent) {
                showToast(notification);
                return;
            }

            // Non-urgent (1-2 hours away) - schedule for 1 hour before
            const eventTime = new Date(notification.time).getTime();
            const serverNow = new Date(serverTime).getTime();
            const notifyAt = eventTime - NOTIFY_BEFORE_MS;
            const delay = notifyAt - serverNow;

            // If notification time has passed or is within 5 seconds, show immediately
            if (delay <= 5000) {
                showToast(notification);
                return;
            }

            // Schedule for later
            scheduledTimeouts[notification.id] = setTimeout(() => {
                if (!wasNotified(notification.id)) {
                    showToast(notification);
                }
                delete scheduledTimeouts[notification.id];
            }, delay);
        }

        function fetchAndSchedule() {
            fetch('{{ route("admin.notifications.upcoming") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.notifications && Array.isArray(data.notifications)) {
                    data.notifications.forEach(notification => {
                        scheduleNotification(notification, data.server_time);
                    });
                }
            })
            .catch(() => {});
        }

        // Initial fetch
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fetchAndSchedule);
        } else {
            fetchAndSchedule();
        }

        // Refresh periodically
        setInterval(fetchAndSchedule, REFRESH_INTERVAL_MS);
    })();
    </script>
</body>
</html>
