# SOP: Timezone Handling di Calendar WordPress Scheduling

## Overview

Dokumen ini menjelaskan Standard Operating Procedure (SOP) untuk menangani timezone di Calendar WordPress Scheduling agar waktu yang ditampilkan di calendar grid dan popup card sesuai dengan timezone user (Asia/Jakarta / WIB).

## Problem Statement

Ketika user menjadwalkan post WordPress di jam 08:00 WIB, terjadi masalah timezone:
- **Database**: Menyimpan waktu dalam UTC (01:00 UTC = 08:00 WIB)
- **Calendar Grid**: Menampilkan waktu salah (15:00 instead of 08:00)
- **Popup Card**: Menampilkan waktu salah (15:00 instead of 08:00)
- **Event Title di Grid**: Menampilkan waktu salah (15:00 instead of 08:00)

Root cause: **Double timezone conversion** - JavaScript FullCalendar memparsing datetime string dan menambahkan offset timezone lagi.

---

## Solution Architecture

### 1. Database Storage (UTC)

**Rule**: SELALU simpan waktu di database dalam **UTC timezone**.

**File**: `app/Http/Controllers/PostController.php`

```php
// Saat user input jam 08:00 WIB, convert ke UTC sebelum save
$target = $post->targets()->create([
    'website_id' => $request->website_id,
    'status' => 'scheduled',
    'scheduled_at' => \Carbon\Carbon::parse($request->scheduled_at, auth()->user()->timezone ?? 'UTC')
        ->setTimezone('UTC'), // Convert user timezone → UTC
]);
```

**Database Value**: `2026-01-08 01:00:00` (UTC)

---

### 2. Backend API Response (UTC → User Timezone)

**File**: `app/Http/Controllers/CalendarController.php` - method `getWordPressEvents()`

**Step-by-step Conversion:**

```php
// 1. Parse database value as UTC (CRITICAL: database always stores UTC)
$utcTime = \Carbon\Carbon::createFromFormat(
    'Y-m-d H:i:s',
    $target->scheduled_at->format('Y-m-d H:i:s'),
    'UTC'
);

// 2. Convert UTC → User Timezone (WIB)
$localTime = $utcTime->copy()->setTimezone($userTimezone);

// 3. Format untuk FullCalendar (WITHOUT timezone offset)
$formattedStart = $localTime->format('Y-m-d\TH:i:s');
// Result: "2026-01-08T08:00:00" (tanpa offset +07:00)

// 4. Kirim data ke frontend
return [
    'id' => 'wp-' . $target->id,
    'title' => $post->title ?? 'Untitled',
    'start' => $formattedStart, // "2026-01-08T08:00:00"
    'backgroundColor' => '#10B981',
    'extendedProps' => [
        'type' => 'wordpress',
        'local_time' => $localTime->format('Y-m-d H:i:s'), // "2026-01-08 08:00:00"
        'utc_time' => $utcTime->toISOString(), // "2026-01-08T01:00:00.000Z"
        'timezone' => $userTimezone, // "Asia/Jakarta"
    ],
];
```

**CRITICAL POINTS:**
1. ✅ **DO**: Parse database value as UTC dengan `createFromFormat()`
2. ✅ **DO**: Kirim datetime **TANPA timezone offset** (`Y-m-d\TH:i:s`)
3. ✅ **DO**: Sertakan `local_time` di `extendedProps` untuk JavaScript
4. ❌ **DON'T**: Kirim ISO string dengan offset (`toISOString()`) ke FullCalendar
5. ❌ **DON'T**: Gunakan `Carbon::parse()` tanpa explicit timezone

---

### 3. Frontend FullCalendar Configuration

**File**: `resources/views/calendar/index.blade.php`

**FullCalendar Config:**

```javascript
calendar = new FullCalendar.Calendar(calendarEl, {
    timeZone: userTimezone, // 'Asia/Jakarta'
    initialView: 'timeGridWeek',
    // ... other config
});
```

**CRITICAL**: Set `timeZone: 'Asia/Jakarta'` agar FullCalendar tahu timezone user.

---

### 4. Display Waktu di Calendar Grid (Event Card)

**File**: `resources/views/calendar/index.blade.php` - function `eventContent()`

**Problem**: FullCalendar `arg.event.start.toLocaleTimeString()` menerapkan browser timezone lagi.

**Solution**: Gunakan `props.local_time` dari backend (sudah correct).

```javascript
eventContent: function(arg) {
    const props = arg.event.extendedProps;

    // ✅ CORRECT: Gunakan local_time dari backend
    let time;
    if (props.type === 'wordpress' && props.local_time) {
        // Extract time dari "2026-01-08 08:00:00" → "08:00"
        time = props.local_time.split(' ')[1].substring(0, 5);
    } else {
        // For social media events, use event.start
        time = arg.event.start.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    return {
        html: `
            <div class="flex items-center gap-2 w-full">
                <div class="fc-event-time">${time}</div>
                <div class="fc-event-title">${arg.event.title}</div>
                <div>WP ○</div>
            </div>
        `
    };
}
```

**Result**: Card di grid menampilkan "08:00 R... WP ○"

---

### 5. Display Waktu di Popup Card

**File**: `resources/views/calendar/index.blade.php` - function `openEventDetailPopup()`

**Problem**: Sama seperti di grid, `event.start.toLocaleString()` salah.

**Solution**: Gunakan `props.local_time` dari backend.

```javascript
function openEventDetailPopup(event) {
    const props = event.extendedProps;

    // ✅ CORRECT: Gunakan local_time dari backend
    let time;
    if (props.type === 'wordpress' && props.local_time) {
        // Parse local_time "2026-01-08 08:00:00"
        const dateObj = new Date(props.local_time.replace(' ', 'T'));
        time = dateObj.toLocaleString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    } else {
        time = event.start.toLocaleString('en-US', { /* ... */ });
    }

    // Render popup dengan waktu yang benar
    const html = `
        <div class="popup">
            <h3>${event.title}</h3>
            <p>${time}</p> <!-- "Thu, Jan 8, 08:00" -->
        </div>
    `;
}
```

**Result**: Popup menampilkan "Thu, Jan 8, 08:00"

---

## Critical Rules (DO & DON'T)

### ✅ DO

1. **Database Storage**:
   - ALWAYS store datetime in UTC
   - Convert user timezone → UTC before saving

2. **Backend API**:
   - Parse database value as UTC using `createFromFormat()`
   - Convert UTC → user timezone for display
   - Send datetime WITHOUT timezone offset (`Y-m-d\TH:i:s`)
   - Include `local_time` in `extendedProps`

3. **Frontend Display**:
   - Use `props.local_time` from backend for WordPress events
   - Never rely on JavaScript `Date` object parsing alone
   - Set FullCalendar `timeZone` config to user's timezone

### ❌ DON'T

1. **DON'T** use `Carbon::parse()` without explicit timezone parameter
2. **DON'T** send ISO string with offset to FullCalendar (`toISOString()`)
3. **DON'T** use `event.start.toLocaleTimeString()` for WordPress events
4. **DON'T** assume `$model->scheduled_at` has correct timezone (always re-parse as UTC)
5. **DON'T** forget to check `props.type === 'wordpress'` before using `local_time`

---

## Flow Diagram

```
User Input (Frontend)
    ↓
"08:00 WIB" → Convert to UTC → "01:00 UTC"
    ↓
Database Storage
    ↓
scheduled_at = "2026-01-08 01:00:00" (UTC)
    ↓
Backend API (CalendarController)
    ↓
Parse as UTC → Convert to WIB → Format without offset
    ↓
Response JSON:
{
  "start": "2026-01-08T08:00:00",
  "extendedProps": {
    "local_time": "2026-01-08 08:00:00",
    "utc_time": "2026-01-08T01:00:00.000Z"
  }
}
    ↓
Frontend JavaScript
    ↓
Use "local_time" → Extract "08:00"
    ↓
Display: "08:00 R... WP ○" (Grid)
Display: "Thu, Jan 8, 08:00" (Popup)
```

---

## Testing Checklist

### ✅ Test Scenarios

1. **Schedule New Post**:
   - User input: `2026-01-08 08:00` (WIB)
   - Database: `2026-01-08 01:00:00` (UTC) ✅
   - Calendar grid: Shows at 08:00 slot ✅
   - Event card: Shows "08:00" ✅
   - Popup: Shows "Thu, Jan 8, 08:00" ✅

2. **Different Timezones**:
   - User timezone = `America/New_York` (UTC-5)
   - User input: `2026-01-08 08:00` (EST)
   - Database: `2026-01-08 13:00:00` (UTC) ✅
   - Calendar grid: Shows at 08:00 EST ✅

3. **Cross-Day Boundary**:
   - User timezone = `Asia/Tokyo` (UTC+9)
   - User input: `2026-01-08 02:00` (JST)
   - Database: `2026-01-07 17:00:00` (UTC) ✅
   - Calendar grid: Shows at Jan 8, 02:00 JST ✅

### ❌ Known Issues (Solved)

1. ~~Calendar grid shows wrong time (15:00 instead of 08:00)~~ ✅ FIXED
2. ~~Popup card shows wrong time~~ ✅ FIXED
3. ~~Event auto-publishes immediately~~ ✅ FIXED (separate issue)
4. ~~Double timezone conversion~~ ✅ FIXED

---

## Code References

### Backend Files

1. **CalendarController.php** (lines 167-198):
   - `getWordPressEvents()` method
   - UTC → User timezone conversion
   - Response formatting

2. **PostController.php** (lines 67-123, 188-243):
   - `store()` and `update()` methods
   - User timezone → UTC conversion
   - Schedule action handling

### Frontend Files

1. **calendar/index.blade.php** (lines 347-348):
   - FullCalendar `timeZone` config

2. **calendar/index.blade.php** (lines 453-481):
   - `eventContent()` function
   - Event card rendering

3. **calendar/index.blade.php** (lines 898-928):
   - `openEventDetailPopup()` function
   - Popup rendering

---

## Debugging Tips

### Log Analysis

**Backend Log** (`storage/logs/laravel.log`):

```
[2026-01-08 ...] local.INFO: WordPress event formatted
{
  "target_id": 25,
  "raw_db": "2026-01-08 01:00:00",
  "utc_parsed": "2026-01-08T01:00:00+00:00",
  "local_time": "2026-01-08 08:00:00 WIB",
  "formatted_start": "2026-01-08T08:00:00"
}
```

**Check Points**:
- ✅ `raw_db` = UTC time from database
- ✅ `utc_parsed` = Correct UTC ISO string (offset +00:00)
- ✅ `local_time` = Correct user timezone (08:00 WIB)
- ✅ `formatted_start` = WITHOUT offset (no +07:00)

**Browser Console Log**:

```javascript
console.log('Event data:', {
  'event.start': event.start, // Date object
  'props.local_time': props.local_time, // "2026-01-08 08:00:00"
  'props.utc_time': props.utc_time // "2026-01-08T01:00:00.000Z"
});
```

### Common Mistakes

1. **Forgetting to set timezone in createFromFormat()**:
   ```php
   // ❌ WRONG
   $utcTime = Carbon::parse($target->scheduled_at);

   // ✅ CORRECT
   $utcTime = Carbon::createFromFormat('Y-m-d H:i:s', $target->scheduled_at->format('Y-m-d H:i:s'), 'UTC');
   ```

2. **Using event.start directly in JavaScript**:
   ```javascript
   // ❌ WRONG (double timezone conversion)
   const time = event.start.toLocaleTimeString();

   // ✅ CORRECT (use backend data)
   const time = props.local_time.split(' ')[1].substring(0, 5);
   ```

3. **Sending ISO string with offset to FullCalendar**:
   ```php
   // ❌ WRONG
   'start' => $utcTime->toISOString(), // "2026-01-08T01:00:00.000Z"

   // ✅ CORRECT
   'start' => $localTime->format('Y-m-d\TH:i:s'), // "2026-01-08T08:00:00"
   ```

---

## Migration Guide

Jika ada WordPress events lama yang masih punya timezone issue:

### Step 1: Verify Database

```sql
SELECT id, scheduled_at, created_at
FROM ane_post_targets
WHERE status = 'scheduled'
AND scheduled_at IS NOT NULL;
```

**Expected**: Semua `scheduled_at` dalam UTC.

### Step 2: Fix Backend Code

Pastikan `CalendarController::getWordPressEvents()` menggunakan pattern yang benar (lihat section 2).

### Step 3: Clear Browser Cache

```bash
# Hard refresh browser
Cmd+Shift+R (Mac)
Ctrl+Shift+R (Windows/Linux)
```

### Step 4: Test

1. Create new schedule
2. Verify calendar grid shows correct time
3. Click event → verify popup shows correct time
4. Check database → verify UTC storage

---

## Future Improvements

1. **Add Timezone Indicator**:
   - Show "WIB" or "JST" in event card
   - Add tooltip with UTC time

2. **User Timezone Setting**:
   - Add UI to change user timezone in profile
   - Update `users.timezone` column

3. **Reschedule Modal**:
   - Fix timezone conversion in reschedule modal
   - Ensure datetime-local input respects user timezone

4. **Background Publishing**:
   - Create job to publish WordPress posts at scheduled time
   - Use UTC for cron comparison

---

## Conclusion

**Key Takeaway**:
- Database = UTC (always)
- Backend API = Convert UTC → User Timezone
- Frontend Display = Use `props.local_time` (never rely on JavaScript Date parsing alone)

Follow this SOP untuk memastikan timezone handling yang konsisten di seluruh aplikasi.

---

**Last Updated**: 2026-01-08
**Author**: Development Team
**Version**: 1.0
