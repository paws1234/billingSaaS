<?php

// Test API Endpoints
$baseUrl = "http://web/api";  // Use nginx service name

echo "=== BILLING API TEST ===\n\n";

// Test 1: Get Plans
echo "1. Getting all plans...\n";
$ch = curl_init("$baseUrl/plans");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$plans = json_decode(curl_exec($ch), true);
curl_close($ch);
echo "✅ Found " . count($plans) . " plans\n";
foreach ($plans as $plan) {
    echo "   - {$plan['name']}: \${$plan['amount']}/100 CAD/{$plan['interval']}\n";
}

// Test 2: Login
echo "\n2. Logging in...\n";
$ch = curl_init("$baseUrl/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'user@example.com',
    'password' => 'password'
]));
$loginResponseRaw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: " . substr($loginResponseRaw, 0, 500) . "...\n";

$loginResponse = json_decode($loginResponseRaw, true);

if (isset($loginResponse['token'])) {
    $token = $loginResponse['token'];
    echo "✅ Login successful!\n";
    echo "   User: {$loginResponse['user']['name']}\n";
    echo "   Token: " . substr($token, 0, 20) . "...\n";
    
    // Test 3: Get Profile
    echo "\n3. Getting user profile...\n";
    $ch = curl_init("$baseUrl/me");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    $profile = json_decode(curl_exec($ch), true);
    curl_close($ch);
    echo "✅ Profile retrieved:\n";
    print_r($profile);
    
    // Test 4: Get Subscriptions
    echo "\n4. Getting subscriptions...\n";
    $ch = curl_init("$baseUrl/subscriptions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    $subscriptions = json_decode(curl_exec($ch), true);
    curl_close($ch);
    echo "✅ Found " . count($subscriptions) . " subscriptions\n";
    
    // Test 5: Get Invoices
    echo "\n5. Getting invoices...\n";
    $ch = curl_init("$baseUrl/invoices");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    $invoices = json_decode(curl_exec($ch), true);
    curl_close($ch);
    echo "✅ Found " . count($invoices) . " invoices\n";
    
} else {
    echo "❌ Login failed!\n";
    print_r($loginResponse);
}

echo "\n=== ALL TESTS COMPLETE ===\n";
