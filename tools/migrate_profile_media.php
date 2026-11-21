<?php
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/database/migrate_profile_media.php';
echo 'Profile/media migration: '.migrateProfileMedia()." columns added.\n";
