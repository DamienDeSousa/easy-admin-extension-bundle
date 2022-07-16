<?php

$container->loadFromExtension('framework', [
    'secret' => 'F00',
    'csrf_protection' => true,
    'session' => [
        'handler_id' => null,
        'storage_factory_id' => 'session.storage.factory.mock_file',
    ],
    'test' => true,
    'router' => [
        'utf8' => true,
    ],
]);
