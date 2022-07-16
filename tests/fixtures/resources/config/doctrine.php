<?php

$container->loadFromExtension('doctrine', [
    'dbal' => [
        'driver' => 'pdo_sqlite',
        'path' => '%kernel.project_dir%/cache/test_database.sqlite',
    ],
    'orm' => [
        'auto_generate_proxy_classes' => true,
        'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
        'auto_mapping' => true,
        'mappings' => [
            'TestCmsEntities' => [
                'is_bundle' => false,
                'type' => 'attribute',
                'dir' => '%kernel.project_dir%/vendor/dades/cms-bundle/src/Entity',
                'prefix' => 'Dades\CmsBundle\Entity',
            ],
        ],
    ],
]);
