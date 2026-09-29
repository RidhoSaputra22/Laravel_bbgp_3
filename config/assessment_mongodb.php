<?php

return [
    'enabled' => (bool) env('MONGODB_SYNC_ENABLED', false),
    'uri' => env('MONGODB_URI', 'mongodb://127.0.0.1:27017'),
    'database' => env('MONGODB_DATABASE', 'quiz_bbgtk'),
    'collection' => env('MONGODB_ASSIGNMENT_COLLECTION', 'assessment-assignment'),
    // 100 keeps queue overhead low while keeping the in-memory document
    // batch bounded. Lower this for unusually large assessment schemas.
    'batch_size' => (int) env('MONGODB_SYNC_BATCH_SIZE', 100),
];
