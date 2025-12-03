<?php
/**
 * Stripe Webhook Handler
 * Håndterer subscription events fra Stripe
 */

require_once __DIR__ . '/stripe-php-13.18.0/init.php';

// Load WordPress (theme file - need to go up to WordPress root)
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    __DIR__ . '/../wp-load.php',
    __DIR__ . '/wp-load.php'
];

foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        break;
    }
}

if (!function_exists('wp')) {
    error_log('RTF Webhook ERROR: Could not load WordPress!');
    http_response_code(500);
    exit();
}

// Stripe configuration
\Stripe\Stripe::setApiKey(defined('RTF_STRIPE_SECRET_KEY') ? RTF_STRIPE_SECRET_KEY : 'sk_test_placeholder');
$endpoint_secret = defined('RTF_STRIPE_WEBHOOK_SECRET') ? RTF_STRIPE_WEBHOOK_SECRET : '';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(\UnexpectedValueException $e) {
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}

global $wpdb, $rtf_user_system;

// Håndter forskellige event types
switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        
        // Log webhook modtaget
        error_log('RTF Webhook: ========================================');
        error_log('RTF Webhook: checkout.session.completed received');
        error_log('RTF Webhook: Session ID: ' . $session->id);
        error_log('RTF Webhook: Customer email: ' . ($session->customer_email ?? 'MISSING'));
        error_log('RTF Webhook: Customer ID: ' . ($session->customer ?? 'MISSING'));
        error_log('RTF Webhook: Subscription ID: ' . ($session->subscription ?? 'MISSING'));
        error_log('RTF Webhook: Payment status: ' . ($session->payment_status ?? 'unknown'));
        
        $customer_email = $session->customer_email;
        $subscription_id = $session->subscription ?? null;
        $customer_id = $session->customer ?? null;
        
        // Validate required data
        if (!$customer_email) {
            error_log('RTF Webhook ERROR: No customer email in session!');
            http_response_code(400);
            exit('Missing customer email');
        }
        
        if (!$customer_id) {
            error_log('RTF Webhook ERROR: No customer ID in session!');
            http_response_code(400);
            exit('Missing customer ID');
        }
        
        // Find user using robust system
        $user = $rtf_user_system->get_user_by_email($customer_email);
        
        if (!$user) {
            error_log('RTF Webhook ERROR: User not found with email: ' . $customer_email);
            error_log('RTF Webhook: Checking database for similar emails...');
            
            // Debug: Check for email case mismatch
            $similar = $wpdb->get_results($wpdb->prepare(
                "SELECT id, email, username FROM {$wpdb->prefix}rtf_platform_users WHERE LOWER(email) = LOWER(%s)",
                $customer_email
            ));
            
            if ($similar) {
                error_log('RTF Webhook: Found similar email(s): ' . json_encode($similar));
            } else {
                error_log('RTF Webhook: No matching user found at all');
            }
            
            http_response_code(404);
            exit('User not found');
        }
        
        error_log('RTF Webhook: ✓ User found - ID: ' . $user->id . ', Username: ' . $user->username . ', Email: ' . $user->email);
        
        // CRITICAL: Activate subscription with customer ID
        $activated = $rtf_user_system->activate_subscription_by_email($customer_email, $customer_id, 30);
        
        if ($activated) {
            error_log('RTF Webhook: ✓ Subscription activated for user ' . $user->username);
            
            // Verify stripe_customer_id was saved
            $verify = $rtf_user_system->get_user($user->id);
            if ($verify && $verify->stripe_customer_id === $customer_id) {
                error_log('RTF Webhook: ✓ Stripe customer ID saved: ' . $customer_id);
            } else {
                error_log('RTF Webhook: WARNING - Stripe customer ID may not be saved correctly');
            }
            
            // Log payment using robust system
            $payment_logged = $rtf_user_system->log_payment([
                'user_id' => $user->id,
                'stripe_customer_id' => $customer_id,
                'stripe_subscription_id' => $subscription_id,
                'amount' => 4900, // 49.00 DKK
                'currency' => 'DKK',
                'status' => 'completed',
                'payment_intent_id' => $session->payment_intent ?? null
            ]);
            
            if ($payment_logged) {
                error_log('RTF Webhook: ✓ Payment logged successfully');
            } else {
                error_log('RTF Webhook: ERROR - Failed to log payment');
            }
            
            // Verify activation
            if ($rtf_user_system->has_active_subscription($user->id)) {
                error_log('RTF Webhook: ✓✓✓ COMPLETE SUCCESS - Subscription is ACTIVE and valid');
                error_log('RTF Webhook: User ' . $user->username . ' can now access platform');
            } else {
                error_log('RTF Webhook: WARNING - Subscription check failed after activation');
            }
            
        } else {
            error_log('RTF Webhook: ✗✗✗ CRITICAL ERROR - Failed to activate subscription for ' . $customer_email);
            http_response_code(500);
            exit('Failed to activate subscription');
        }
        
        error_log('RTF Webhook: ========================================');
        break;
        
    case 'customer.subscription.updated':
        $subscription = $event->data->object;
        
        error_log('RTF Webhook: subscription.updated - ID: ' . $subscription->id . ', Status: ' . $subscription->status);
        
        $status_map = [
            'active' => 'active',
            'past_due' => 'past_due',
            'canceled' => 'canceled',
            'unpaid' => 'canceled'
        ];
        
        $new_status = $status_map[$subscription->status] ?? 'inactive';
        
        // Opdater end date hvis aktiv
        $update_data = ['subscription_status' => $new_status];
        if ($new_status === 'active') {
            $update_data['subscription_end_date'] = date('Y-m-d H:i:s', $subscription->current_period_end);
        }
        
        $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            $update_data,
            ['stripe_customer_id' => $subscription->customer]
        );
        break;
        
    case 'customer.subscription.deleted':
        $subscription = $event->data->object;
        
        error_log('RTF Webhook: subscription.deleted - ID: ' . $subscription->id);
        
        $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            [
                'subscription_status' => 'canceled',
                'subscription_end_date' => date('Y-m-d H:i:s')
            ],
            ['stripe_customer_id' => $subscription->customer]
        );
        break;
        
    case 'invoice.payment_failed':
        $invoice = $event->data->object;
        $customer_id = $invoice->customer;
        
        error_log('RTF Webhook: invoice.payment_failed - Customer: ' . $customer_id);
        
        $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            ['subscription_status' => 'past_due'],
            ['stripe_customer_id' => $customer_id]
        );
        break;
}

http_response_code(200);
