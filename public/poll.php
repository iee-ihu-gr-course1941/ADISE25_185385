<?php
require 'db.php';
$game_id = intval($_GET['game_id'] ?? 0);
$since = intval($_GET['since'] ?? 0);
if (!$game_id) json_resp(['status'=>'error','message'=>'NO_GAME_ID'],400);

$stmt = $pdo->prepare('SELECT * FROM moves WHERE game_id = ? AND UNIX_TIMESTAMP(timestamp) > ? ORDER BY id ASC');
$stmt->execute([$game_id, $since]);
$events = $stmt->fetchAll();
json_resp(['status'=>'ok','events'=>$events,'now'=>time()]);