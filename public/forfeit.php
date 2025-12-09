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
$state['end_reason'] = 'forfeit';
$pids = array_keys($state['players']);
$winner = null;
foreach ($pids as $pid) if (intval($pid) !== $player_id) { $winner = intval($pid); break; }
$state['final_scores'] = ['winner'=>$winner,'forfeit_by'=>$player_id];
$upd = $pdo->prepare('UPDATE games SET state = ?, status = ? WHERE id = ?');
$upd->execute([json_encode($state),'finished',$game_id]);
$pdo->commit();
json_resp(['status'=>'ok','winner'=>$winner]);