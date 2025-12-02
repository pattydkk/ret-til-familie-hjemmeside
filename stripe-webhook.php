<?php
/**
 * Stripe Webhook Handler
 * Håndterer subscription events fra Stripe
 */

require_once __DIR__ . '/vendor/autoload.php';
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
        
        // Opdater bruger subscription status
        $customer_email = $session->customer_email;
        $subscription_id = $session->subscription;
        
        $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            [
                'subscription_status' => 'active',
                'subscription_id' => $subscription_id,
                'subscription_start' => current_time('mysql')
            ],
            ['email' => $customer_email]
        );
        
        // Log transaction
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
            $customer_email
        ));
        
        if ($user) {
            $wpdb->insert($wpdb->prefix . 'rtf_platform_transactions', [
                'user_id' => $user->id,
                'amount' => 49.00,
                'currency' => 'DKK',
                'status' => 'completed',
                'stripe_payment_id' => $session->payment_intent,
                'description' => 'Månedligt abonnement - Borger Platform',
                'created_at' => current_time('mysql')
            ]);
        }
        break;
        
    case 'customer.subscription.updated':
        $subscription = $event->data->object;
        
        $status_map = [
            'active' => 'active',
            'past_due' => 'past_due',
            'canceled' => 'canceled',
            'unpaid' => 'canceled'
        ];
        
        $new_status = $status_map[$subscription->status] ?? 'inactive';
        
        $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            ['subscription_status' => $new_status],
            ['subscription_id' => $subscription->id]
        );
        break;
        
    case 'customer.subscription.deleted':
        $subscription = $event->data->object;
        
        $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            [
                'subscription_status' => 'canceled',
                'subscription_end' => current_time('mysql')
            ],
            ['subscription_id' => $subscription->id]
        );
        break;
        
    case 'invoice.payment_failed':
        $invoice = $event->data->object;
        $subscription_id = $invoice->subscription;
        
        $wpdb->update(
            $wpdb->prefix . 'rtf_platform_users',
            ['subscription_status' => 'past_due'],
            ['subscription_id' => $subscription_id]
        );
        break;
}

http_response_code(200);
