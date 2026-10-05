<?php

return [
    'enabled' => (bool) env('MONGODB_SYNC_ENABLED', false),
    'uri' => env('MONGODB_URI', 'mongodb://127.0.0.1:27017'),
    'database' => env('MONGODB_DATABASE', 'quiz_bbgtk'),
    'collection' => env('MONGODB_ASSIGNMENT_COLLECTION', 'assessment_assignment'),
    'validator_collection' => env('MONGODB_VALIDATOR_ASSIGNMENT_COLLECTION', 'validator_assignment'),
    'sync_driver' => env('MONGODB_SYNC_DRIVER', 'php'),
    'outbox_connection' => env('MONGODB_OUTBOX_DB_CONNECTION'),
    'outbox_lease_seconds' => (int) env('MONGODB_OUTBOX_LEASE_SECONDS', 600),
    // 100 keeps queue overhead low while keeping the in-memory document
    // batch bounded. Lower this for unusually large assessment schemas.
    'batch_size' => (int) env('MONGODB_SYNC_BATCH_SIZE', 100),
];
