<?php

return [
    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),
    'mysql_path' => env('MYSQL_PATH', 'mysql'),
    'disk' => 'local', // storage/app
    'directory' => 'backups',
    'max_keep' => 20, // oldest backups beyond this count are auto-deleted
];