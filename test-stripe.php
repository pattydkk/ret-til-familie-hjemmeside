<?php
// Test Stripe connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Stripe Connection Test</h1>";

// Load Stripe library
$stripe_init = __DIR__ . '/stripe-php-13.18.0/init.php';
echo "<p>Looking for Stripe library at: " . htmlspecialchars($stripe_init) . "</p>";

if (!file_exists($stripe_init)) {
    echo "<p style='color: red;'>❌ ERROR: Stripe library not found!</p>";
    exit;
}

echo "<p style='color: green;'>✅ Stripe library found</p>";

require_once($stripe_init);

echo "<p style='color: green;'>✅ Stripe library loaded</p>";

// Set API key
$secret_key = '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';
$price_id = 'price_1SFMobL8XSb2lnp6ulwzpiAb';

\Stripe\Stripe::setApiKey($secret_key);

echo "<p style='color: green;'>✅ API key set</p>";

// Test creating a checkout session
try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'success_url' => 'http://example.com/success',
        'cancel_url' => 'http://example.com/cancel',
        'payment_method_types' => ['card'],
        'mode' => 'subscription',
        'customer_email' => 'test@example.com',
        'client_reference_id' => '123',
        'line_items' => [[
            'price' => $price_id,
            'quantity' => 1
        ]],
        'subscription_data' => [
            'metadata' => [
                'user_id' => '123',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'rtf_platform' => 'true'
            ]
        ]
    ]);
    
    echo "<p style='color: green;'>✅ SUCCESS! Checkout session created</p>";
    echo "<p>Session ID: " . htmlspecialchars($checkout_session->id) . "</p>";
    echo "<p>Checkout URL: <a href='" . htmlspecialchars($checkout_session->url) . "' target='_blank'>Open Stripe Checkout</a></p>";
    
} catch (\Stripe\Exception\InvalidRequestException $e) {
    echo "<p style='color: red;'>❌ Invalid Request Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (\Stripe\Exception\AuthenticationException $e) {
    echo "<p style='color: red;'>❌ Authentication Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Error type: " . get_class($e) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
