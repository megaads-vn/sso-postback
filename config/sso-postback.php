<?php 
return [
    'debug' => false, // If debug == true. Ignore check DNS
    'user_table' => 'users',
    'user_account_column' => 'email',
    'active_status' => 'active',
    'deactive_status' => 'inactive',
    'map' => [
        'full_name' => 'name',
        'active' => 'status',
    ]
];