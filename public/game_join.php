<?php
require 'db.php';
$input = require_json_input();
$player_id = intval($input['player_id'] ?? 0);
$game_id = intval($input['game_id'] ?? 0);
if (!$player_id || !$game_id) json_resp(['status'=>'error','message'=>'MISSING'],400);


$pdo->beginTransaction();
$gstmt = $pdo->prepare('SELECT * FROM games WHERE id = ? FOR UPDATE');
$gstmt->execute([$game_id]);
$game = $gstmt->fetch();
if (!$game) { $pdo->rollBack(); json_resp(['status'=>'error','message'=>'NO_GAME'],404); }
$state = json_decode($game['state'], true);
$playersInGame = $pdo->prepare('SELECT player_id FROM game_players WHERE game_id = ?');
$playersInGame->execute([$game_id]);
$rows = $playersInGame->fetchAll(PDO::FETCH_COLUMN);
if (in_array($player_id, $rows)) { $pdo->rollBack(); json_resp(['status'=>'error','message'=>'ALREADY_IN_GAME'],400); }
if (count($rows) >= $game['max_players']) { $pdo->rollBack(); json_resp(['status'=>'error','message'=>'GAME_FULL'],400); }


$seat = count($rows);
$gp = $pdo->prepare('INSERT INTO game_players (game_id,player_id,seat,is_dealer) VALUES (?,?,?,0)');
$gp->execute([$game_id,$player_id,$seat]);

$state['players'][(string)$player_id] = ['hand'=>[], 'captured'=>[], 'xeri_count'=>0];
$playerIds = array_keys($state['players']);
foreach ($playerIds as $pid) {
$take = min(6, count($state['deck']));
$state['players'][$pid]['hand'] = array_splice($state['deck'], 0, $take);
}
if (count($playerIds) == $game['max_players']){
$dealer = $game['status'] == 'waiting' ? intval($game['state'] ? $state['dealer'] ?? $playerIds[0] : $playerIds[0]) : $state['dealer'];
$pids = array_values($playerIds);
$turn = ($pids[0] == $dealer) ? $pids[1] : $pids[0];
$state['turn'] = $turn;
$newStatus = 'playing';
} else {
$state['turn'] = null;
$newStatus = $game['status'];
}


$update = $pdo->prepare('UPDATE games SET state = ?, status = ? WHERE id = ?');
$update->execute([json_encode($state), $newStatus, $game_id]);
$pdo->commit();
json_resp(['status'=>'ok','game_id'=>$game_id,'state'=>$state]);