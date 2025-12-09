<?php
header("Content-Type: application/json");
echo json_encode([
    "message" => "Xeri Web API",
    "version" => "1.0",
    "endpoints" => [
        "/game_create.php",
        "/game_join.php",
        "/state.php",
        "/play.php",
        "/poll.php",
        "/forfeit.php"
    ]
]);
