DROP TABLE IF EXISTS moves;
DROP TABLE IF EXISTS game_players;
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS cards;


CREATE TABLE players (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(80) UNIQUE NOT NULL,
token CHAR(40),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE games (
id INT AUTO_INCREMENT PRIMARY KEY,
variant VARCHAR(32) NOT NULL DEFAULT 'xeri',
max_players TINYINT NOT NULL DEFAULT 2,
status ENUM('waiting','playing','finished','abandoned') NOT NULL DEFAULT 'waiting',
state JSON NOT NULL,
state_hash CHAR(64) NULL,
state_hash_history JSON NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE game_players (
game_id INT NOT NULL,
player_id INT NOT NULL,
seat TINYINT,
is_dealer TINYINT DEFAULT 0,
PRIMARY KEY(game_id,player_id),
FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE moves (
id BIGINT AUTO_INCREMENT PRIMARY KEY,
game_id INT NOT NULL,
player_id INT NOT NULL,
card VARCHAR(4),
move_type ENUM('play','deal','forfeit','join','start') NOT NULL,
timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
meta JSON NULL,
FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    suit ENUM('S','H','D','C') NOT NULL,   
    rank ENUM('A','2','3','4','5','6','7','8','9','10','J','Q','K') NOT NULL,
    value INT NOT NULL    
);
INSERT INTO cards (suit, rank, value) VALUES
('S','A',1), ('S','2',2), ('S','3',3), ('S','4',4), ('S','5',5), ('S','6',6),
('S','7',7), ('S','8',8), ('S','9',9), ('S','10',10), ('S','J',10), ('S','Q',10), ('S','K',10),

('H','A',1), ('H','2',2), ('H','3',3), ('H','4',4), ('H','5',5), ('H','6',6),
('H','7',7), ('H','8',8), ('H','9',9), ('H','10',10), ('H','J',10), ('H','Q',10), ('H','K',10),

('D','A',1), ('D','2',2), ('D','3',3), ('D','4',4), ('D','5',5), ('D','6',6),
('D','7',7), ('D','8',8), ('D','9',9), ('D','10',10), ('D','J',10), ('D','Q',10), ('D','K',10),

('C','A',1), ('C','2',2), ('C','3',3), ('C','4',4), ('C','5',5), ('C','6',6),
('C','7',7), ('C','8',8), ('C','9',9), ('C','10',10), ('C','J',10), ('C','Q',10), ('C','K',10);


CREATE INDEX idx_games_status ON games(status);
CREATE INDEX idx_moves_game ON moves(game_id);