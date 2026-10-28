<?php

return [
    'levels' => [
        'beginner' => ['name' => 'Beginner', 'min' => 0, 'max' => 999, 'color' => '#34d399'],
        'upper-beginner' => ['name' => 'Upper Beginner', 'min' => 1000, 'max' => 1999, 'color' => '#38bdf8'],
        'intermediate' => ['name' => 'Intermediate', 'min' => 2000, 'max' => 2999, 'color' => '#fbbf24'],
        'upper-intermediate' => ['name' => 'Upper Intermediate', 'min' => 3000, 'max' => 3999, 'color' => '#f472b6'],
    ],
    'level_order' => ['beginner', 'upper-beginner', 'intermediate', 'upper-intermediate'],
    'surfaces' => ['Synthetic', 'Hard', 'Clay', 'Carpet'],
    'areas' => ['Indoor', 'Outdoor', 'Semi-outdoor'],
    'slots' => ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'],
    'payment_methods' => ['QRIS', 'Transfer Bank', 'Saldo'],
    'max_active_challenges' => 3,
    'booking_days' => 30,
];
