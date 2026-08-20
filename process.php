<?php
/**
 * Password Breach Checker - HaveIBeenPwned API Integration
 * 
 * This script checks if a password has been compromised using the
 * HaveIBeenPwned API's range-based lookup method.
 * 
 * @author ShieldOS Academic Project
 * @version 1.0
 */

// ============================================================================
// CONFIGURATION & INITIALIZATION
// ============================================================================

// Set JSON response header for AJAX requests
header('Content-Type: application/json');

// API Configuration Constants
const HIBP_API_BASE = 'https://api.pwnedpasswords.com/range/';
const API_TIMEOUT = 10;
const USER_AGENT = 'ShieldOS-Academic-Project-Dashboard';
const HASH_PREFIX_LENGTH = 5;

// ============================================================================
// VALIDATION & REQUEST PROCESSING
// ============================================================================

/**
 * Validate and respond to invalid requests
 */
function validateRequest() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['target'])) {
        respondWithError('Invalid Request Execution Protocol');
        exit;
    }
}

/**
 * Send error response as JSON
 */
function respondWithError($message) {
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
}

/**
 * Send success response as JSON
 */
function respondWithSuccess($breachCount) {
    echo json_encode([
        'status' => 'success',
        'breach_count' => $breachCount
    ]);
}

validateRequest();

// ============================================================================
// HASH GENERATION & API PROCESSING
// ============================================================================

$password = $_POST['target'];

// Step 1: Generate SHA-1 hash of the password
$sha1Hash = strtoupper(sha1($password));

// Step 2: Split hash into prefix and suffix for range-based lookup
$prefix = substr($sha1Hash, 0, HASH_PREFIX_LENGTH);
$suffix = substr($sha1Hash, HASH_PREFIX_LENGTH);

// ============================================================================
// API REQUEST & RESPONSE HANDLING
// ============================================================================

// Step 3: Query HaveIBeenPwned API
$apiUrl = HIBP_API_BASE . $prefix;

$curlHandle = curl_init();
curl_setopt($curlHandle, CURLOPT_URL, $apiUrl);
curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curlHandle, CURLOPT_TIMEOUT, API_TIMEOUT);
curl_setopt($curlHandle, CURLOPT_USERAGENT, USER_AGENT);

$apiResponse = curl_exec($curlHandle);
$httpStatusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

curl_close($curlHandle);

// Validate API response
if ($httpStatusCode !== 200 || !$apiResponse) {
    respondWithError('Remote Registry API Database unreachable');
    exit;
}

// ============================================================================
// RESPONSE PARSING & BREACH COUNT LOOKUP
// ============================================================================

// Step 4: Parse API response and find matching suffix
$responseLines = explode("\r\n", trim($apiResponse));
$breachCount = 0;

foreach ($responseLines as $line) {
    if (empty($line)) {
        continue;
    }
    
    list($remoteSuffix, $count) = explode(':', $line);
    
    // Check if this line contains our password hash suffix
    if ($remoteSuffix === $suffix) {
        $breachCount = (int)$count;
        break;
    }
}

// ============================================================================
// RESPONSE DELIVERY
// ============================================================================

// Step 5: Return result to JavaScript frontend
respondWithSuccess($breachCount);
exit;
?>