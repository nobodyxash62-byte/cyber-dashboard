<?php
// Force clean JSON responses back to the AJAX frontend
header('Content-Type: application/json');

// Prevent direct URL access if no payload string is sent
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['target'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Execution Protocol']);
    exit;
}

$targetString = $_POST['target'];

// 1. Convert the plain text password into a SHA-1 hash (uppercase matches API specifications)
$sha1Hash = strtoupper(sha1($targetString));

// 2. Extract the first 5 characters (The Prefix) and the rest of the string (The Suffix)
$prefix = substr($sha1Hash, 0, 5);
$suffix = substr($sha1Hash, 5);

// 3. Set up the HaveIBeenPwned API remote connection URL
$apiUrl = "https://api.pwnedpasswords.com/range/" . $prefix;

// Initialize cURL for a secure remote web request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
// Crucial: Spoof a user-agent header because the HIBP API blocks anonymous automated scrapers
curl_setopt($ch, CURLOPT_USERAGENT, 'ShieldOS-Academic-Project-Dashboard');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// If the remote API doesn't return an HTTP 200 OK status, fail gracefully
if ($httpCode !== 200 || !$response) {
    echo json_encode(['status' => 'error', 'message' => 'Remote Registry API Database unreachable']);
    exit;
}

// 4. Parse the response lines to find our matching suffix string
$lines = explode("\r\n", trim($response));
$breachCount = 0;

foreach ($lines as $line) {
    // The API responds in the format: SUFFIX:COUNT (e.g., C5E8A2DABEDE0F3B482CD9AEA9434D:14205)
    list($remoteSuffix, $count) = explode(':', $line);
    
    if ($remoteSuffix === $suffix) {
        $breachCount = (int)$count;
        break; // Match found, break loop early
    }
}

// 5. Return the exact match counts cleanly back to your JavaScript dashboard engine
echo json_encode([
    'status' => 'success',
    'breach_count' => $breachCount
]);
exit;