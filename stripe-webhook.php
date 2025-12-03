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

global $wpdb;

// Håndter forskellige event types
switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        
        // Log webhook modtaget
        error_log('RTF Webhook: checkout.session.completed received');
        error_log('RTF Webhook: Customer email: ' . $session->customer_email);
        error_log('RTF Webhook: Subscription ID: ' . ($session->subscription ?? 'none'));
        error_log('RTF Webhook: Customer ID: ' . ($session->customer ?? 'none'));
        
        // Opdater bruger subscription status
        $customer_email = $session->customer_email;
        $subscription_id = $session->subscription ?? null;
        $customer_id = $session->customer ?? null;
        
        if (!$customer_email) {
            error_log('RTF Webhook ERROR: No customer email in session!');
            break;
        }
        
        // Find bruger
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
            $customer_email
        ));
        
        if (!$user) {
            error_log('RTF Webhook ERROR: User not found with email: ' . $customer_email);
            error_log('RTF Webhook: Checking if email exists in database...');
            
            // Debug: List all emails
            $all_emails = $wpdb->get_col("SELECT email FROM {$wpdb->prefix}rtf_platform_users LIMIT 10");
            error_log('RTF Webhook: First 10 emails in database: ' . implode(', ', $all_emails));
            
            break;
        }
        
        error_log('RTF Webhook: User found - ID: ' . $user->id . ', Username: ' . $user->username);
        
        // Beregn end date (30 dage fra nu)
        $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        error_log('RTF Webhook: Attempting to update user...');
        error_log('RTF Webhook: Setting subscription_status=active, end_date=' . $end_date);
        
        // Opdater bruger med EXPLICIT column names
        $update_data = [
            'subscription_status' => 'active',
            'subscription_end_date' => $end_date
        ];
        
        if ($customer_id) {
            $update_data['stripe_customer_id'] = $customer_id;
        }
        
        $update_result = $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            $update_data,
            ['email' => $customer_email],
            ['%s', '%s', '%s'],
            ['%s']
        );
        
        if ($update_result === false) {
            error_log('RTF Webhook ERROR: Database update failed!');
            error_log('RTF Webhook ERROR: ' . $wpdb->last_error);
            error_log('RTF Webhook ERROR: Last query: ' . $wpdb->last_query);
        } else {
            error_log('RTF Webhook SUCCESS: User activated! Rows affected: ' . $update_result);
            
            // Verify update
            $verify_user = $wpdb->get_row($wpdb->prepare(
                "SELECT subscription_status, subscription_end_date FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
                $customer_email
            ));
            
            if ($verify_user) {
                error_log('RTF Webhook VERIFY: Status is now: ' . $verify_user->subscription_status);
                error_log('RTF Webhook VERIFY: End date is now: ' . $verify_user->subscription_end_date);
            }
        }
        
        // Log transaction til payments tabel
        $payment_insert = $wpdb->insert($wpdb->prefix . 'rtf_stripe_payments', [
            'user_id' => $user->id,
            'stripe_customer_id' => $customer_id,
            'stripe_subscription_id' => $subscription_id,
            'amount' => 4900, // 49.00 DKK i øre
            'currency' => 'DKK',
            'status' => 'completed',
            'payment_intent_id' => $session->payment_intent ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($payment_insert === false) {
            error_log('RTF Webhook ERROR: Failed to log payment: ' . $wpdb->last_error);
        } else {
            error_log('RTF Webhook: Payment logged to database (insert_id: ' . $wpdb->insert_id . ')');
        }
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
