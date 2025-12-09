<?php
require 'db.php';
$game_id = intval($_GET['game_id'] ?? 0);
if (!$game_id) json_resp(['status'=>'error','message'=>'NO_GAME_ID'],400);
$stmt = $pdo->prepare('SELECT * FROM games WHERE id = ?');
$stmt->execute([$game_id]);
$game = $stmt->fetch();
if (!$game) json_resp(['status'=>'error','message'=>'GAME_NOT_FOUND'],404);
$state = json_decode($game['state'], true);
json_resp(['status'=>'ok','game_id'=>$game_id,'state'=>$state,'meta'=>['status'=>$game['status']]]);