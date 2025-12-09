<?php
require 'db.php';
$input = require_json_input();
$username = trim($input['username'] ?? '');
if ($username === '') json_resp(['status'=>'error','message'=>'NO_USERNAME'],400);

$stmt = $pdo->prepare('SELECT * FROM players WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();
if ($user) {
if (empty($user['token'])){
$token = gen_token($config['token_length']);
$upd = $pdo->prepare('UPDATE players SET token = ? WHERE id = ?');
$upd->execute([$token, $user['id']]);
} else $token = $user['token'];
json_resp(['status'=>'ok','player_id'=>$user['id'],'token'=>$token]);
}
$token = gen_token($config['token_length']);
$ins = $pdo->prepare('INSERT INTO players (username,token) VALUES (?,?)');
$ins->execute([$username,$token]);
$id = $pdo->lastInsertId();
json_resp(['status'=>'ok','player_id'=>intval($id),'token'=>$token]);