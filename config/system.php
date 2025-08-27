<?php

return [
    'roles' => [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'staff' => 'Staff',
    ],

    'permissions' => [
        'super_admin' => [
            'manage_all_users',
            'manage_all_departments',
            'view_all_reports',
            'system_settings',
        ],
        'admin' => [
            'manage_department_users',
            'manage_programs',
            'manage_services',
            'view_department_reports',
        ],
        'staff' => [
            'manage_clients',
            'register_services',
            'update_registrations',
        ],
    ],

    'registration_statuses' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
];
