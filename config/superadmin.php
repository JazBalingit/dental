<?php

// Hardcoded super admin login — grants full admin session access without
// a row in tbl_useraccount. Credentials live in .env, never in the DB.
// Leave either value empty in .env to disable this login path entirely.
return [
    'email' => env('SUPERADMIN_EMAIL'),
    'password' => env('SUPERADMIN_PASSWORD'),

    // Sentinel session UserID — deliberately outside the real autoincrement
    // range so it never collides with an actual tbl_useraccount row.
    'user_id' => 999999999,
];
