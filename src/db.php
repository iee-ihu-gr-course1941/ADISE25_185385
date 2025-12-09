<?php
$config = require __DIR__ . '/config.php';
try {
$pdo = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['pass'], $config['db']['opts']);
} catch (Exception $e) {
http_response_code(500);
echo json_encode(['status'=>'error','message'=>'DB_CONN', 'detail'=>$e->getMessage()]);
exit;
}


function json_resp($data, $code=200){
http_response_code($code);
header('Content-Type: application/json');
echo json_encode($data);
exit;
}


function require_json_input(){
$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) json_resp(['status'=>'error','message'=>'INVALID_JSON'],400);
return $data;
}


function gen_token($len=40){
return bin2hex(random_bytes($len/2));
}