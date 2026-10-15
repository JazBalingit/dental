<?php

namespace App\Exceptions;

use Exception;

/**
 * A backup/restore problem whose message is written for the super admin
 * (unlike a raw PDO/Zip error, which never reaches the page).
 */
class BackupException extends Exception
{
}
