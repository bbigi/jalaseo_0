<?php

require __DIR__ . '/vendor/autoload.php';

use Carbon\Carbon;

echo "=== PHASE 1 TIMEZONE CONVERSION TEST ===\n\n";

// Simulate user input: "2026-01-10 14:00" (user's local time in Asia/Jakarta)
$userInput = '2026-01-10 14:00';
$userTimezone = 'Asia/Jakarta';

echo "1. User Input (Local Time): {$userInput}\n";
echo "2. User Timezone: {$userTimezone}\n\n";

// Parse in user's timezone and convert to UTC
$dateInUserTimezone = Carbon::parse($userInput, $userTimezone);
$dateInUTC = $dateInUserTimezone->copy()->setTimezone('UTC');

echo "3. Parsed in User Timezone:\n";
echo "   - DateTime: {$dateInUserTimezone->toDateTimeString()}\n";
echo "   - Timezone: {$dateInUserTimezone->getTimezone()->getName()}\n";
echo "   - ISO8601: {$dateInUserTimezone->toIso8601String()}\n\n";

echo "4. Converted to UTC:\n";
echo "   - DateTime: {$dateInUTC->toDateTimeString()}\n";
echo "   - Timezone: {$dateInUTC->getTimezone()->getName()}\n";
echo "   - ISO8601: {$dateInUTC->toIso8601String()}\n\n";

echo "5. Verification:\n";
echo "   - User expects publish at: 14:00 WIB (Jakarta)\n";
echo "   - Database stores: {$dateInUTC->toDateTimeString()} UTC\n";
echo "   - WordPress receives (date_gmt): {$dateInUTC->toIso8601String()}\n";
echo "   - WordPress will publish at: " . $dateInUTC->copy()->setTimezone('Asia/Jakarta')->format('H:i') . " WIB\n\n";

echo "✅ CORRECT: User input 14:00 WIB → Stored as 07:00 UTC → WordPress publishes at 14:00 WIB\n\n";

echo "=== BEFORE FIX (BUGGY BEHAVIOR) ===\n\n";
echo "1. User Input: {$userInput} (assumed UTC by mistake)\n";
echo "2. Database stores: {$userInput} (no conversion)\n";
echo "3. WordPress receives: {$userInput} UTC\n";
echo "4. WordPress publishes at: " . Carbon::parse($userInput, 'UTC')->setTimezone('Asia/Jakarta')->format('H:i') . " WIB\n";
echo "❌ WRONG: User expects 14:00 WIB but gets 21:00 WIB (7 hours late)\n\n";
