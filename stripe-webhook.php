<?php
/**
 * Stripe Webhook Handler
 * Håndterer subscription events fra Stripe
 */

require_once __DIR__ . '/stripe-php-13.18.0/init.php';
require_once __DIR__ . '/wp-load.php';

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
        
        // Opdater bruger subscription status
        $customer_email = $session->customer_email;
        $subscription_id = $session->subscription;
        $customer_id = $session->customer;
        
        // Find bruger
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
            $customer_email
        ));
        
        if (!$user) {
            error_log('RTF Webhook ERROR: User not found with email: ' . $customer_email);
            break;
        }
        
        error_log('RTF Webhook: User found - ID: ' . $user->id . ', Username: ' . $user->username);
        
        // Beregn end date (30 dage fra nu)
        $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Opdater bruger
        $update_result = $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            [
                'subscription_status' => 'active',
                'stripe_customer_id' => $customer_id,
                'subscription_end_date' => $end_date
            ],
            ['email' => $customer_email],
            ['%s', '%s', '%s'],
            ['%s']
        );
        
        if ($update_result === false) {
            error_log('RTF Webhook ERROR: Database update failed: ' . $wpdb->last_error);
        } else {
            error_log('RTF Webhook SUCCESS: User activated! Rows affected: ' . $update_result);
        }
        
        // Log transaction
        $wpdb->insert($wpdb->prefix . 'rtf_stripe_payments', [
            'user_id' => $user->id,
            'stripe_customer_id' => $customer_id,
            'stripe_subscription_id' => $subscription_id,
            'amount' => 4900, // 49.00 DKK i øre
            'currency' => 'DKK',
            'status' => 'completed',
            'payment_intent_id' => $session->payment_intent ?? '',
            'created_at' => current_time('mysql')
        ]);
        
        error_log('RTF Webhook: Payment logged to database');
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
                'subscription_end_date' => current_time('mysql')
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
