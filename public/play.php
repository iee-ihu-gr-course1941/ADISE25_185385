<?php
$captureType = (count($tableBefore) == 1) ? 'xeri' : 'normal';
} elseif (strpos($card,'J') === 0) {
$capturedCards = array_merge($tableBefore, [$card]);
$state['table'] = [];
$moveResult = 'captured';
$captureType = 'jack';
}
}


if ($moveResult === 'captured'){
$state['players'][(string)$player_id]['captured'] = array_merge($state['players'][(string)$player_id]['captured'], $capturedCards);
if ($captureType === 'xeri') $state['players'][(string)$player_id]['xeri_count'] = ($state['players'][(string)$player_id]['xeri_count'] ?? 0) + 1;
}

$state['history'][] = ['player'=>$player_id,'card'=>$card,'result'=>$moveResult,'ctype'=>$captureType,'time'=>time()];

$pids = array_keys($state['players']);
$turnIndex = array_search((string)$player_id, $pids);
$nextIndex = ($turnIndex === 0) ? 1 : 0;
$state['turn'] = intval($pids[$nextIndex]);

$allEmpty = true;
foreach ($state['players'] as $p) if (count($p['hand'])>0) { $allEmpty = false; break; }
if ($allEmpty && count($state['deck'])>0) {
foreach ($pids as $pid) {
$take = min(6, count($state['deck']));
$handTake = array_splice($state['deck'], 0, $take);
$state['players'][$pid]['hand'] = array_merge($state['players'][$pid]['hand'], $handTake);
}
}
$stateCopy = $state; unset($stateCopy['history']);
$stateJson = json_encode($stateCopy);
$stateHash = hash('sha256', $stateJson);
$hashHistory = json_decode($game['state_hash_history'] ?? '[]', true) ?: [];
$hashHistory[] = $stateHash;
if (count($hashHistory) > 500) $hashHistory = array_slice($hashHistory, -500);


$reps = 0; foreach($hashHistory as $h) if ($h === $stateHash) $reps++;
$deadlock = ($reps >= ($config['deadlock_repeat_threshold'] ?? 3));


if ($deadlock) {
$scores = [];
foreach ($state['players'] as $pid => $p) $scores[$pid] = count($p['captured']) + 2*($p['xeri_count'] ?? 0);
$state['final_scores'] = $scores;
$state['end_reason'] = 'deadlock_repetition';
$newStatus = 'finished';
} else {
$deckEmpty = (count($state['deck']) === 0);
$allHandsEmpty = true; foreach($state['players'] as $p) if (count($p['hand'])>0) { $allHandsEmpty=false; break; }
if ($deckEmpty && $allHandsEmpty) {
$scores = [];
foreach ($state['players'] as $pid => $p) $scores[$pid] = count($p['captured']) + 2*($p['xeri_count'] ?? 0);
$state['final_scores'] = $scores;
$state['end_reason'] = 'normal_end';
$newStatus = 'finished';
} else {
$newStatus = $game['status'];
}
}

$upd = $pdo->prepare('UPDATE games SET state = ?, state_hash = ?, state_hash_history = ?, status = ? WHERE id = ?');
$upd->execute([json_encode($state), $stateHash, json_encode($hashHistory), $newStatus, $game_id]);

$ins = $pdo->prepare('INSERT INTO moves (game_id,player_id,card,move_type,meta) VALUES (?,?,?,?,?)');
$ins->execute([$game_id,$player_id,$card,'play', json_encode(['result'=>$moveResult,'ctype'=>$captureType])]);


$pdo->commit();


json_resp(['status'=>'ok','move_result'=>$moveResult,'capture_type'=>$captureType,'deadlock'=>$deadlock,'state'=>$state]);