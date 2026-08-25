<?php

// Hardcoded super admin login — authentication always runs against these
// .env values, never a DB password hash. Leave either value empty to
// disable this login path entirely. See LoginController::loginAsSuperAdmin()
// for how this gets a real (auto-provisioned) tbl_useraccount row.
return [
    'email' => env('SUPERADMIN_EMAIL'),
    'password' => env('SUPERADMIN_PASSWORD'),
];
