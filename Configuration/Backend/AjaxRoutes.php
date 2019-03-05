<?php

return [
    'masterrecord_sync' => [
        'path' => '/ajax/masterrecord/sync',
        'target' => \NamelessCoder\MasterRecord\Controller\SyncController::class . '::sync'
    ],
];
