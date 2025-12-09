<?php
require 'db.php';
$input = require_json_input();
$player_id = intval($input['player_id'] ?? 0);
$max_players = intval($input['max_players'] ?? 2);
if ($player_id <= 0) json_resp(['status'=>'error','message'=>'NO_PLAYER'],400);
if ($max_players < 2) $max_players = 2;

function new_deck(){
$suits = ['C','D','H','S'];
$ranks = ['A','2','3','4','5','6','7','8','9','10','J','Q','K'];
$deck = [];
foreach($suits as $s) foreach($ranks as $r) $deck[] = $r.$s;
shuffle($deck);
return $deck;
}

$deck = new_deck();
$initialTable = array_splice($deck, 0, 4);

$state = [
'deck'=>$deck,
'table'=>$initialTable,
'players'=> [ (string)$player_id => [ 'hand'=>[], 'captured'=>[], 'xeri_count'=>0 ] ],
'turn'=>null,
'dealer'=>$player_id,
'round'=>1,
'history'=>[],
];

$ins = $pdo->prepare('INSERT INTO games (variant,max_players,state,state_hash_history,status) VALUES (?,?,?,?,?)');
$ins->execute(['xeri',$max_players,json_encode($state),json_encode([]),'waiting']);
$game_id = $pdo->lastInsertId();
$gp = $pdo->prepare('INSERT INTO game_players (game_id,player_id,seat,is_dealer) VALUES (?,?,?,?)');
$gp->execute([$game_id,$player_id,0,1]);


json_resp(['status'=>'ok','game_id'=>intval($game_id),'state'=>$state]);