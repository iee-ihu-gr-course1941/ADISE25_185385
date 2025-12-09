<?php
return [
'db' => [
'dsn' => 'mysql:host=127.0.0.1;dbname=xeri;charset=utf8mb4',
'user' => 'xeri_user',
'pass' => 'xeri_pass',
'opts' => [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
],
],
'token_length' => 40,
'deadlock_repeat_threshold' => 3,
'deadlock_move_threshold' => 200
];