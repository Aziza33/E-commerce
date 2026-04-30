<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'home_page_index' => [
        'path' => './assets/js/home_page/index.js',
        'entrypoint' => true
    ],
    'home_page_show' => [
        'path' => './assets/js/home_page/show.js',
        'entrypoint' => true
    ],
    'home_page_filter' => [
        'path' => './assets/js/home_page/filter.js',
        'entrypoint' => true
    ],
    'bill_index' => [
        'path' => './assets/js/bill/index.js',
        'entrypoint' => true
    ],
    'mail_orderConfirm' => [
        'path' => './assets/js/mail/orderConfirm.js',
        'entrypoint' => true
    ],
    'order_orders' => [
        'path' => './assets/js/order/orders.js',
        'entrypoint' => true
    ],
    'villa_villa58' => [
        'path' => './assets/js/villa/villa58.js',
        'entrypoint' => true
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
];
