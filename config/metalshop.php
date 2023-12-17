<?php

return [
    'pagination' => [
        'product' => [
            'home' => 12,
            'collection' => 9,
        ]
    ],

    'rss' => [
        'url' => 'https://www.cookson-clal.com/mp/rss_mpfr_cdl.jsp1',
        'unit' => 'kg',
        'currency_code' => 'EUR',
        'currency_exchange_rate_preg' => '/(Parité €\/[$].*): (.*)/i',
        'update_per_minutes' => 10,
    ],

    'metals' => [
        'gold' => [
            'name' => 'Gold',
            'rss_preg' => '/(Cours de l\' Or.*fixing : )(.*)( €\/ kg.*)/i',
        ],
        'silver' => [
            'name' => 'Silver',
            'rss_preg' => '/(Cours de l\' Argent.*fixing : )(.*)( €\/ kg.*)/i',
        ],
        'platinum' => [
            'name' => 'Platinum',
            'rss_preg' => '/(Cours du Platine.*fixing : )(.*)( €\/ kg.*)/i',
        ],
        'palladium' => [
            'name' => 'Palladium',
            'rss_preg' => '/(Cours du Palladium.*fixing : )(.*)( €\/ kg.*)/i',
        ],
    ],

    'units' => [
        'g' => [
            'exchange_rate' => 1.00,
        ],
        'kg' => [
            'exchange_rate' => 1000.00,
        ],
        'oz' => [
            'exchange_rate' => 28.35,
        ],
    ]
];
