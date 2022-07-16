<?php

use Symfony\Component\Security\Core\User\InMemoryUser;

$container->loadFromExtension('security', [
    'enable_authenticator_manager' => true,
    'password_hashers' => [
        InMemoryUser::class => 'plaintext',
    ],
    'role_hierarchy' => [
        'ROLE_ADMIN' => ['ROLE_USER'],
        'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN'],
    ],
    'providers' => [
        'test_users' => [
            'memory' => [
                'users' => [
                    'admin' => [
                        'password' => '1234',
                        'roles' => ['ROLE_ADMIN'],
                    ],
                    'super_admin' => [
                        'password' => '1234',
                        'roles' => ['ROLE_SUPER_ADMIN']
                    ],
                ],
            ],
        ],
    ],
    'firewalls' => [
        'secure_admin' => [
            'pattern' => '^/admin',
            'provider' => 'test_users',
            'http_basic' => null,
            'logout' => null,
        ],
    ],
    'access_control' => [
        ['path' => '^/admin/', 'role' => 'ROLE_ADMIN'],
    ],
]);
