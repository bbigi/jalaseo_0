@extends('layouts.dashboard')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header with Stats Cards -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Content Calendar</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600">Total Scheduled</div>
                <div id="statTotal" class="text-3xl font-bold text-gray-900">-</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600">Pending</div>
                <div id="statPending" class="text-3xl font-bold text-orange-600">-</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600">Published</div>
                <div id="statPublished" class="text-3xl font-bold text-green-600">-</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600">Failed</div>
                <div id="statFailed" class="text-3xl font-bold text-red-600">-</div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions Toolbar -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <!-- Left: Filters -->
            <div class="flex items-center gap-3 flex-wrap">
                <label class="text-sm text-gray-700 font-medium">Filter:</label>
                <select id="filterChannel" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Channels</option>
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                    <option value="twitter">Twitter</option>
                    <option value="linkedin">LinkedIn</option>
                </select>
                <select id="filterWebsite" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Websites</option>
                    @foreach($websites as $site)
                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                    @endforeach
                </select>
                <select id="filterStatus" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="success">Published</option>
                    <option value="failed">Failed</option>
                </select>

                <!-- Color Legend -->
                <div class="flex items-center gap-4 ml-4 text-xs text-gray-600">
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 rounded-full" style="background-color: #3B82F6;"></div>
                        <span>Social Media</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 rounded-full" style="background-color: #10B981;"></div>
                        <span>WordPress</span>
                    </div>
                </div>
            </div>

            <!-- Right: Bulk Actions -->
            <div class="flex items-center gap-2">
                <span id="selectedCount" class="text-sm text-gray-600 hidden">0 selected</span>
                <button id="btnBulkDelay" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition hidden">
                    Delay +1h
                </button>
                <button id="btnBulkDelete" class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm rounded-lg transition hidden">
                    Delete Selected
                </button>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div id="calendar"></div>
    </div>

    <!-- Day Detail Modal -->
    <div id="dayDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[95vh] flex flex-col">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50 flex-shrink-0">
                <div>
                    <h3 id="modalDayTitle" class="text-xl font-bold text-gray-900">Day View</h3>
                    <p id="modalDaySubtitle" class="text-sm text-gray-600 mt-1"></p>
                </div>
                <button onclick="closeDayModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Content - SCROLLABLE -->
            <div id="dayDetailContent" class="p-6 overflow-y-auto flex-1 bg-white">
                <!-- 24-hour timeline will be rendered here -->
            </div>
        </div>
    </div>

    <!-- WordPress Reschedule Modal -->
    <div id="rescheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">Reschedule WordPress Post</h3>
                <p id="reschedulePostTitle" class="text-sm text-gray-600 mt-1"></p>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Current Schedule</label>
                        <p id="rescheduleCurrentTime" class="text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded"></p>
                    </div>

                    <div>
                        <label for="rescheduleNewTime" class="block text-sm font-semibold text-gray-700 mb-2">New Schedule Time</label>
                        <input type="datetime-local" id="rescheduleNewTime"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Select the new date and time for publishing</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex gap-2">
                <button onclick="closeRescheduleModal()" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                    Cancel
                </button>
                <button onclick="confirmReschedule()" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition">
                    Reschedule
                </button>
            </div>
        </div>
    </div>

</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/main.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/main.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/main.min.css" rel="stylesheet">
<style>
/* FullCalendar Custom Styles */
.fc {
    --fc-border-color: #E5E7EB;
    --fc-today-bg-color: #EFF6FF;
}

.fc .fc-toolbar-title {
    color: #111827;
    font-size: 1.25rem;
    font-weight: 600;
}

.fc .fc-button {
    background-color: #F3F4F6;
    border-color: #E5E7EB;
    color: #374151;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.fc .fc-button:hover {
    background-color: #E5E7EB;
    border-color: #D1D5DB;
}

.fc .fc-button-primary:not(:disabled):active,
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: #3B82F6;
    border-color: #3B82F6;
    color: #fff;
}

.fc .fc-timegrid-slot {
    height: 3.5rem;
}

.fc .fc-timegrid-event {
    border-radius: 4px;
    border: none;
    padding: 4px 8px;
    cursor: pointer;
    transition: all 0.2s;
    max-width: 120px !important;
    min-width: 100px !important;
    width: auto !important;
}

.fc .fc-timegrid-event:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 10;
}

.fc .fc-timegrid-event.fc-event-selected {
    box-shadow: 0 0 0 2px #3B82F6;
    transform: scale(1.02);
}

.fc .fc-event-title {
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #ffffff;
}

.fc .fc-event-time {
    font-size: 0.7rem;
    font-weight: 400;
    color: rgba(255,255,255,0.9);
}

.fc .fc-col-header-cell {
    background-color: #F9FAFB;
    color: #6B7280;
    font-weight: 600;
    padding: 12px 0;
}

.fc .fc-daygrid-day:hover {
    background-color: #F9FAFB;
    cursor: pointer;
}

.fc .fc-day-today .fc-daygrid-day-number {
    color: #3B82F6;
    font-weight: 700;
}

/* DayGrid Month View Event Styles */
.fc-daygrid-event {
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    transition: all 0.2s;
    border: none !important;
}

.fc-daygrid-event:hover {
    transform: translateX(2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Override dot-event mode - force block style with full background color */
.fc-daygrid-dot-event {
    /* Inline style from FullCalendar will apply here automatically */
}

.fc-daygrid-dot-event .fc-event-dot {
    display: none !important;
}

/* Make sure text is white on colored backgrounds */
.fc-daygrid-event .fc-event-title {
    color: #ffffff !important;
}

.fc-daygrid-event .fc-event-time {
    color: rgba(255,255,255,0.9) !important;
}

/* Day Detail Modal Timeline Styles */
.hour-slot {
    padding: 1rem;
    border-bottom: 1px solid #E5E7EB;
    min-height: 4rem;
    transition: background-color 0.15s;
}

.hour-slot:hover {
    background-color: #F9FAFB;
}

.hour-slot:last-child {
    border-bottom: none;
}

.event-card {
    padding: 0.75rem;
    border-radius: 0.5rem;
    border-left: 4px solid;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 0.5rem;
}

.event-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.event-card:last-child {
    margin-bottom: 0;
}

/* Channel Badge Colors */
.badge-instagram { background-color: #E1306C15; border-color: #E1306C; color: #E1306C; }
.badge-facebook { background-color: #1877F215; border-color: #1877F2; color: #1877F2; }
.badge-twitter { background-color: #00000015; border-color: #000000; color: #000000; }
.badge-linkedin { background-color: #0A66C215; border-color: #0A66C2; color: #0A66C2; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.10/index.global.min.js"></script>

<script>
const userTimezone = '{{ auth()->user()->timezone ?? "UTC" }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let calendar;
let selectedEvents = new Set();

document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
    loadStats();
    setupFilters();
    setupBulkActions();
});

function initCalendar() {
    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        timeZone: userTimezone,
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        nowIndicator: true,
        editable: true,
        droppable: false,
        selectable: true,
        selectMirror: true,
        height: 'auto',
        slotMinTime: '06:00:00',
        slotMaxTime: '23:00:00',
        slotDuration: '01:00:00',
        allDaySlot: false,
        scrollTime: '08:00:00',

        // Show past events (extend view range backwards)
        validRange: {
            start: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000), // 90 days ago
            end: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000)    // 90 days future
        },

        // Multiple event sources: Social Media + WordPress
        eventSources: [
            // Source 1: Social Media Schedules (existing)
            {
                url: '/calendar/events',
                method: 'GET',
                extraParams: function() {
                    return {
                        channel: document.getElementById('filterChannel').value,
                        status: document.getElementById('filterStatus').value,
                    };
                },
                success: function(events) {
                    console.log('✅ Social Media events loaded:', events.length, 'events');
                },
                failure: function(error) {
                    console.error('❌ Failed to load social media events', error);
                }
            },

            // Source 2: WordPress Scheduled Posts (NEW!)
            {
                url: '/calendar/wordpress-events',
                method: 'GET',
                extraParams: function() {
                    return {
                        website: document.getElementById('filterWebsite').value,
                    };
                },
                success: function(events) {
                    console.log('✅ WordPress events loaded:', events.length, 'events');
                },
                failure: function(error) {
                    console.error('❌ Failed to load WordPress events', error);
                }
            }
        ],

        // Drag & Drop - Reschedule
        eventDrop: function(info) {
            rescheduleEvent(info);
        },

        // Click event - Show detail popup or toggle selection
        eventClick: function(info) {
            if (info.jsEvent.ctrlKey || info.jsEvent.metaKey) {
                // Ctrl+Click - Toggle selection for bulk actions
                toggleEventSelection(info.event);
            } else {
                // Normal click - Show event detail popup
                openEventDetailPopup(info.event);
            }
        },

        // Click empty slot or day - Open day detail modal
        dateClick: function(info) {
            // IMPORTANT: info.date is in browser's local timezone (UTC in your case)
            // We need to convert it to user's timezone for correct day detection
            // Example: Click 18:00 UTC Friday -> Should open Friday modal, not Saturday

            // Get clicked date in UTC
            const clickedDateUTC = info.date;

            // Format as ISO string and replace timezone with user's timezone for parsing
            const isoStr = clickedDateUTC.toISOString();
            const dateOnly = isoStr.substring(0, 10); // "2026-01-02"
            const timeOnly = isoStr.substring(11, 19); // "18:00:00"

            // Create new date in user's timezone by parsing as local time
            // This ensures we open the modal for the DAY user clicked visually
            const clickedDateLocal = new Date(`${dateOnly}T${timeOnly}`);

            console.log('Clicked UTC:', clickedDateUTC.toISOString());
            console.log('Clicked Local:', clickedDateLocal.toISOString());
            console.log('Opening modal for:', dateOnly);

            openDayModal(clickedDateLocal);
        },

        // Event rendering - Custom list content
        eventContent: function(arg) {
            const props = arg.event.extendedProps;

            // For WordPress events, use local_time from backend
            let time;
            if (props.type === 'wordpress' && props.local_time) {
                // Extract time from "2026-01-08 08:00:00" -> "08:00"
                time = props.local_time.split(' ')[1].substring(0, 5);
            } else {
                time = arg.event.start.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            }

            // Handle WordPress events differently
            if (props.type === 'wordpress') {
                const statusIcon = props.status === 'scheduled' ? '○' : (props.status === 'published' ? '✓' : '✗');
                return {
                    html: `
                        <div class="flex items-center gap-2 w-full">
                            <div class="fc-event-time flex-shrink-0">${time}</div>
                            <div class="fc-event-title flex-1 truncate">${arg.event.title}</div>
                            <div class="flex-shrink-0 text-xs opacity-80">WP ${statusIcon}</div>
                        </div>
                    `
                };
            }

            // Social Media events
            const statusIcon = props.status === 'success' ? '✓' : (props.status === 'failed' ? '✗' : '○');

            // Channel icon mapping
            const channelIcons = {
                'instagram': 'IG',
                'facebook': 'FB',
                'twitter': 'X',
                'linkedin': 'in'
            };
            const channelIcon = channelIcons[props.channel] || props.channel.substring(0, 2).toUpperCase();

            return {
                html: `
                    <div class="flex items-center gap-2 w-full">
                        <div class="fc-event-time flex-shrink-0">${time}</div>
                        <div class="fc-event-title flex-1 truncate">${arg.event.title}</div>
                        <div class="flex-shrink-0 text-xs opacity-80">${channelIcon} ${statusIcon}</div>
                    </div>
                `
            };
        },

        // Double click - Open day modal
        eventDidMount: function(info) {
            info.el.addEventListener('dblclick', function() {
                openDayModal(info.event.start);
            });

            // FORCE background color for dayGrid month view
            if (info.view.type.includes('dayGrid')) {
                const bgColor = info.event.backgroundColor;
                if (bgColor) {
                    info.el.style.backgroundColor = bgColor;
                    info.el.style.borderColor = bgColor;
                }
            }
        }
    });

    calendar.render();
}

async function rescheduleEvent(info) {
    const scheduleId = info.event.id;
    const newTime = info.event.start;

    // Convert to UTC for backend
    const utcTime = new Date(newTime.getTime() - (newTime.getTimezoneOffset() * 60000)).toISOString();

    try {
        const res = await fetch(`/calendar/events/${scheduleId}/reschedule`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                scheduled_at: utcTime,
            }),
        });

        const data = await res.json();

        if (!data.success) {
            info.revert();
            alert('Failed to reschedule: ' + (data.error || 'Unknown error'));
        } else {
            // Reload stats
            loadStats();
        }
    } catch (error) {
        console.error('Reschedule error:', error);
        info.revert();
        alert('Network error while rescheduling');
    }
}

async function openDayModal(date) {
    const modal = document.getElementById('dayDetailModal');
    const titleEl = document.getElementById('modalDayTitle');
    const subtitleEl = document.getElementById('modalDaySubtitle');
    const contentEl = document.getElementById('dayDetailContent');

    const dateStr = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    titleEl.textContent = dateStr;

    // Show timezone info with browser offset warning
    const browserTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const tzWarning = browserTz !== userTimezone
        ? ` (Calendar shows ${browserTz}, times below are in ${userTimezone})`
        : '';
    subtitleEl.textContent = `Timezone: ${userTimezone}${tzWarning}`;

    // Show loading state
    contentEl.innerHTML = '<div class="text-center py-8 text-gray-400">Loading events...</div>';
    modal.classList.remove('hidden');

    // Debug: Verify modal is visible
    console.log('Modal element:', modal);
    console.log('Modal classList:', modal.classList.toString());
    console.log('Modal display:', window.getComputedStyle(modal).display);

    // Calculate day start/end in UTC for API
    const dayStart = new Date(date);
    dayStart.setHours(0, 0, 0, 0);
    const dayEnd = new Date(date);
    dayEnd.setHours(23, 59, 59, 999);

    // Convert to UTC for API request
    const utcStart = new Date(dayStart.getTime() - dayStart.getTimezoneOffset() * 60000);
    const utcEnd = new Date(dayEnd.getTime() - dayEnd.getTimezoneOffset() * 60000);

    try {
        // Fetch both social and WordPress events for this specific day
        const [socialRes, wpRes] = await Promise.all([
            fetch(`/calendar/events?start=${utcStart.toISOString()}&end=${utcEnd.toISOString()}`),
            fetch(`/calendar/wordpress-events?start=${utcStart.toISOString()}&end=${utcEnd.toISOString()}`)
        ]);

        const socialEvents = await socialRes.json();
        const wpEvents = await wpRes.json();
        const dayEvents = [...socialEvents, ...wpEvents];

        console.log(`Events for ${dateStr}:`, {
            social: socialEvents.length,
            wordpress: wpEvents.length,
            total: dayEvents.length
        });

        // Render 24-hour timeline
        let html = '<div class="space-y-0">';

        for (let hour = 0; hour < 24; hour++) {
            const hourStr = String(hour).padStart(2, '0') + ':00';

            // Filter events for this hour
            const eventsAtHour = dayEvents.filter(e => {
                const props = e.extendedProps;

                // For WordPress events, use local_time from backend
                if (props.type === 'wordpress' && props.local_time) {
                    // Extract hour from "2026-01-08 08:00:00"
                    const eventHour = parseInt(props.local_time.split(' ')[1].split(':')[0]);
                    return eventHour === hour;
                } else {
                    // For social events, parse start time
                    const timeStr = e.start;
                    const eventHour = parseInt(timeStr.substring(11, 13));
                    return eventHour === hour;
                }
            });

            html += `
                <div class="hour-slot flex gap-4 py-3 border-b border-gray-200">
                    <div class="text-sm text-gray-600 font-semibold w-20 flex-shrink-0">${hourStr}</div>
                    <div class="flex-1">
            `;

            if (eventsAtHour.length === 0) {
                html += `<div class="text-xs text-gray-400 italic">No posts scheduled</div>`;
            } else {
                eventsAtHour.forEach(event => {
                    const props = event.extendedProps;
                    const color = event.backgroundColor;

                    // Get time based on event type
                    let time;
                    if (props.type === 'wordpress' && props.local_time) {
                        // Extract time from "2026-01-08 08:00:00" -> "08:00"
                        time = props.local_time.split(' ')[1].substring(0, 5);
                    } else {
                        const eventTime = new Date(event.start);
                        time = eventTime.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        });
                    }

                    // Status badge - handle both social and WordPress
                    let statusBadge;
                    if (props.type === 'wordpress') {
                        statusBadge = props.status === 'published'
                            ? '<span class="text-xs text-green-600 font-medium">✓ Published</span>'
                            : props.status === 'scheduled'
                            ? '<span class="text-xs text-orange-600 font-medium">⏱ Scheduled</span>'
                            : '<span class="text-xs text-gray-600 font-medium">○ Draft</span>';
                    } else {
                        statusBadge = props.status === 'success'
                            ? '<span class="text-xs text-green-600 font-medium">✓ Published</span>'
                            : (props.status === 'failed'
                                ? '<span class="text-xs text-red-600 font-medium">✗ Failed</span>'
                                : '<span class="text-xs text-orange-600 font-medium">⏱ Pending</span>');
                    }

                    // Channel name - handle WordPress
                    const channelName = props.type === 'wordpress'
                        ? 'WordPress'
                        : (props.channel.charAt(0).toUpperCase() + props.channel.slice(1));

                    html += `
                        <div class="bg-gray-50 rounded-lg p-3 mb-2 border-l-4 cursor-pointer hover:bg-gray-100 transition-colors shadow-sm"
                             style="border-left-color: ${color};"
                             onclick="window.location.href='/posts/${props.post_id}/edit'">
                            <div class="flex items-start justify-between mb-1">
                                <div class="text-sm font-bold text-gray-900">${event.title}</div>
                                ${statusBadge}
                            </div>
                            <div class="text-xs text-gray-600 mb-1">
                                <span class="font-semibold">${time}</span> · ${channelName}
                            </div>
                            <div class="text-xs text-gray-500 line-clamp-2">${props.copy_preview || 'No preview available'}</div>
                        </div>
                    `;
                });
            }

            html += `
                    </div>
                </div>
            `;
        }

        html += '</div>';

        contentEl.innerHTML = html;
    } catch (error) {
        console.error('Failed to load day events:', error);
        contentEl.innerHTML = '<div class="text-center py-8 text-red-400">Failed to load events</div>';
    }
}

function closeDayModal() {
    document.getElementById('dayDetailModal').classList.add('hidden');
}

// WordPress Reschedule Modal Functions
let currentRescheduleTargetId = null;

function openRescheduleModal(targetId, title, currentTime) {
    currentRescheduleTargetId = targetId;
    document.getElementById('reschedulePostTitle').textContent = title;
    document.getElementById('rescheduleCurrentTime').textContent = currentTime;

    // Set min datetime to now (can't schedule in the past)
    const now = new Date();
    const minDatetime = now.toISOString().slice(0, 16);
    document.getElementById('rescheduleNewTime').setAttribute('min', minDatetime);

    // Clear previous value
    document.getElementById('rescheduleNewTime').value = '';

    document.getElementById('rescheduleModal').classList.remove('hidden');
}

function closeRescheduleModal() {
    document.getElementById('rescheduleModal').classList.add('hidden');
    currentRescheduleTargetId = null;
}

async function confirmReschedule() {
    const newTime = document.getElementById('rescheduleNewTime').value;

    if (!newTime) {
        alert('Please select a new time');
        return;
    }

    if (!currentRescheduleTargetId) {
        alert('Error: No target ID');
        return;
    }

    // Convert to UTC ISO string
    const localDate = new Date(newTime);
    const utcDate = new Date(localDate.getTime() - localDate.getTimezoneOffset() * 60000);

    try {
        const res = await fetch(`/calendar/wordpress/${currentRescheduleTargetId}/reschedule`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                scheduled_at: utcDate.toISOString()
            })
        });

        const data = await res.json();

        if (data.success) {
            closeRescheduleModal();
            calendar.refetchEvents();
            showNotification('success', 'Schedule updated successfully!');
        } else {
            alert('Reschedule failed: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Reschedule error:', error);
        alert('Network error during reschedule');
    }
}

function toggleEventSelection(event) {
    const eventId = event.id;

    if (selectedEvents.has(eventId)) {
        selectedEvents.delete(eventId);
        event.setProp('classNames', []);
    } else {
        selectedEvents.add(eventId);
        event.setProp('classNames', ['fc-event-selected']);
    }

    updateBulkActionsUI();
}

function updateBulkActionsUI() {
    const count = selectedEvents.size;
    const countEl = document.getElementById('selectedCount');
    const delayBtn = document.getElementById('btnBulkDelay');
    const deleteBtn = document.getElementById('btnBulkDelete');

    if (count > 0) {
        countEl.textContent = `${count} selected`;
        countEl.classList.remove('hidden');
        delayBtn.classList.remove('hidden');
        deleteBtn.classList.remove('hidden');
    } else {
        countEl.classList.add('hidden');
        delayBtn.classList.add('hidden');
        deleteBtn.classList.add('hidden');
    }
}

async function loadStats() {
    try {
        const res = await fetch('/calendar/stats');
        const stats = await res.json();

        document.getElementById('statTotal').textContent = stats.total || 0;
        document.getElementById('statPending').textContent = stats.by_status?.pending || 0;
        document.getElementById('statPublished').textContent = stats.by_status?.success || 0;
        document.getElementById('statFailed').textContent = stats.by_status?.failed || 0;
    } catch (error) {
        console.error('Failed to load stats', error);
    }
}

function setupFilters() {
    const channelFilter = document.getElementById('filterChannel');
    const websiteFilter = document.getElementById('filterWebsite');
    const statusFilter = document.getElementById('filterStatus');

    channelFilter.addEventListener('change', applyFilters);
    websiteFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // Restore filters from localStorage
    const savedChannel = localStorage.getItem('calendar_filter_channel');
    const savedWebsite = localStorage.getItem('calendar_filter_website');
    const savedStatus = localStorage.getItem('calendar_filter_status');

    if (savedChannel) channelFilter.value = savedChannel;
    if (savedWebsite) websiteFilter.value = savedWebsite;
    if (savedStatus) statusFilter.value = savedStatus;
}

function applyFilters() {
    const channel = document.getElementById('filterChannel').value;
    const website = document.getElementById('filterWebsite').value;
    const status = document.getElementById('filterStatus').value;

    // Save to localStorage
    localStorage.setItem('calendar_filter_channel', channel);
    localStorage.setItem('calendar_filter_website', website);
    localStorage.setItem('calendar_filter_status', status);

    // Refetch events with filters
    calendar.refetchEvents();
    loadStats();
}

function setupBulkActions() {
    document.getElementById('btnBulkDelay').addEventListener('click', () => {
        if (selectedEvents.size === 0) return;

        if (confirm(`Delay ${selectedEvents.size} selected events by 1 hour?`)) {
            bulkReschedule('delay', 1);
        }
    });

    document.getElementById('btnBulkDelete').addEventListener('click', () => {
        if (selectedEvents.size === 0) return;

        if (confirm(`Delete ${selectedEvents.size} selected events? This cannot be undone.`)) {
            bulkDelete();
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape - Clear selection
        if (e.key === 'Escape' && selectedEvents.size > 0) {
            selectedEvents.forEach(id => {
                const event = calendar.getEventById(id);
                if (event) event.setProp('classNames', []);
            });
            selectedEvents.clear();
            updateBulkActionsUI();
        }

        // Delete - Delete selected
        if (e.key === 'Delete' && selectedEvents.size > 0) {
            e.preventDefault();
            if (confirm(`Delete ${selectedEvents.size} events?`)) {
                bulkDelete();
            }
        }
    });
}

async function bulkReschedule(action, hours) {
    const scheduleIds = Array.from(selectedEvents);

    try {
        const res = await fetch('/calendar/events/bulk-reschedule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                schedule_ids: scheduleIds,
                action: action,
                hours: hours,
            }),
        });

        const data = await res.json();

        if (data.success) {
            calendar.refetchEvents();
            selectedEvents.clear();
            updateBulkActionsUI();
            loadStats();
            alert(`${data.updated_count} events rescheduled successfully`);
        } else {
            alert('Bulk reschedule failed: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Bulk reschedule error:', error);
        alert('Network error during bulk reschedule');
    }
}

function openEventDetailPopup(event) {
    const props = event.extendedProps;

    // For WordPress events, use local_time from backend directly
    let time;
    if (props.type === 'wordpress' && props.local_time) {
        // Parse local_time string "2026-01-08 08:00:00" and format it
        // Don't use event.start as it may have timezone issues
        time = props.local_time.replace(' ', ', ').replace(/-/g, '/');

        // Format to match: "Thu, Jan 8, 08:00"
        const dateObj = new Date(props.local_time.replace(' ', 'T'));
        const options = {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        };
        time = dateObj.toLocaleString('en-US', options);
    } else {
        time = event.start.toLocaleString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    // Handle WordPress events differently
    if (props.type === 'wordpress') {
        const statusBadge = props.status === 'published' ?
            '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">Published</span>' :
            props.status === 'scheduled' ?
            '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-medium">Scheduled</span>' :
            '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium">Draft</span>';

        const html = `
            <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="this.remove()">
                <div class="bg-gray-800 rounded-lg max-w-md w-full p-6 text-white" onclick="event.stopPropagation()">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold pr-4">${event.title}</h3>
                            <p class="text-sm text-gray-400 mt-1">WordPress Post</p>
                        </div>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 rounded text-xs font-medium text-white bg-green-600">WordPress</span>
                            <span class="px-2 py-1 rounded text-xs font-medium text-gray-300 bg-gray-700">${props.website_name}</span>
                            ${statusBadge}
                        </div>

                        <div class="flex items-center gap-2 text-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>${time}</span>
                        </div>

                        ${props.wp_post_id ? `
                            <div class="text-gray-400 text-xs">
                                <p>WordPress Post ID: #${props.wp_post_id}</p>
                            </div>
                        ` : ''}
                    </div>

                    <div class="mt-6 flex flex-col gap-2">
                        <div class="flex gap-2">
                            <a href="/posts/${props.post_id}/edit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium text-center">
                                Edit Post
                            </a>
                            <button onclick="this.closest('.fixed').remove(); openRescheduleModal('${event.id}', '${event.title}', '${time}')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm font-medium">
                                Reschedule
                            </button>
                        </div>
                        <button onclick="cancelWordPressSchedule('${event.id}')" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium">
                            Cancel Schedule
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        return;
    }

    // Social Media events (existing code)
    const statusBadge = props.status === 'success' ?
        '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">Published</span>' :
        props.status === 'failed' ?
        '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">Failed</span>' :
        '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-medium">Pending</span>';

    const channelBadge = `<span class="px-2 py-1 rounded text-xs font-medium text-white" style="background-color: ${event.backgroundColor}">${props.channel.toUpperCase()}</span>`;

    const html = `
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="this.remove()">
            <div class="bg-gray-800 rounded-lg max-w-md w-full p-6 text-white" onclick="event.stopPropagation()">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-semibold pr-4">${event.title}</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        ${channelBadge}
                        ${statusBadge}
                    </div>

                    <div class="flex items-center gap-2 text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>${time}</span>
                    </div>

                    <div class="text-gray-400 text-xs">
                        <p class="line-clamp-3">${props.copy_preview || 'No preview available'}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <a href="/posts/${props.post_id}/edit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium text-center">
                        Edit Post
                    </a>
                    <button onclick="deleteEvent(${event.id})" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', html);
}

async function deleteEvent(scheduleId) {
    if (!confirm('Delete this schedule?')) return;

    try {
        const res = await fetch(`/calendar/events/${scheduleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        const data = await res.json();

        if (data.success) {
            calendar.refetchEvents();
            loadStats();
            document.querySelector('.fixed.inset-0')?.remove();
            alert('Schedule deleted successfully');
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Failed to delete schedule');
    }
}

// Cancel WordPress schedule function
async function cancelWordPressSchedule(eventId) {
    if (!confirm('Cancel this WordPress schedule?')) return;

    const targetId = eventId.replace('wp-', ''); // Remove prefix

    try {
        const res = await fetch(`/posts/targets/${targetId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        const data = await res.json();

        if (data.success) {
            calendar.refetchEvents();
            document.querySelector('.fixed.inset-0')?.remove(); // Close popup
            alert('WordPress schedule cancelled successfully');
        } else {
            alert('Failed to cancel schedule: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Cancel WordPress schedule error:', error);
        alert('Network error during cancellation');
    }
}

async function bulkDelete() {
    const scheduleIds = Array.from(selectedEvents);

    const deletePromises = scheduleIds.map(id => {
        return fetch(`/calendar/events/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });
    });

    try {
        await Promise.all(deletePromises);
        calendar.refetchEvents();
        selectedEvents.clear();
        updateBulkActionsUI();
        loadStats();
        alert(`${scheduleIds.length} events deleted successfully`);
    } catch (error) {
        console.error('Bulk delete error:', error);
        alert('Some events failed to delete');
    }
}
</script>
@endpush
@endsection
