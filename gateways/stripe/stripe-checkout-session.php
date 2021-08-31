<?php

require_once LEYKA_PLUGIN_DIR.'gateways/stripe/lib/init.php';

$secret_key = leyka_options()->opt('stripe_key_secret');

\Stripe\Stripe::setApiKey($secret_key);

header('Content-Type: application/json');

$checkout_session = \Stripe\Checkout\Session::create([
    'line_items' => [[
        'price' => 777,
        'quantity' => 1,
    ]],
    'payment_method_types' => [
        'card',
    ],
    'mode' => 'payment',
    'success_url' => leyka_get_success_page_url(),
    'cancel_url' => leyka_get_failure_page_url(),
]);
header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);