-- 종이뽑기 미니게임 테이블
CREATE TABLE IF NOT EXISTS mg_game_lottery_prize (
  glp_id int NOT NULL AUTO_INCREMENT,
  glp_rank tinyint NOT NULL DEFAULT 1 COMMENT '등수 1~5',
  glp_name varchar(50) NOT NULL DEFAULT '' COMMENT '상 이름',
  glp_count int NOT NULL DEFAULT 1 COMMENT '한 판당 개수',
  glp_point int NOT NULL DEFAULT 10 COMMENT '포인트 보상',
  glp_item_id int DEFAULT NULL COMMENT '상점 아이템 ID',
  glp_use tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
  PRIMARY KEY (glp_id),
  KEY idx_rank (glp_rank)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mg_game_lottery_board (
  glb_id int NOT NULL AUTO_INCREMENT,
  glb_size int NOT NULL DEFAULT 100 COMMENT '판 크기',
  glb_bonus_point int NOT NULL DEFAULT 500 COMMENT '판 완성 보너스',
  glb_bonus_item_id int DEFAULT NULL COMMENT '완성 보너스 아이템',
  glb_use tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
  PRIMARY KEY (glb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mg_game_lottery_user (
  glu_id int NOT NULL AUTO_INCREMENT,
  mb_id varchar(20) NOT NULL COMMENT '회원 ID',
  glb_id int NOT NULL DEFAULT 1 COMMENT '현재 판 ID',
  glu_picked text COMMENT '뽑은 번호 JSON',
  glu_count int NOT NULL DEFAULT 0 COMMENT '뽑은 개수',
  glu_completed_count int NOT NULL DEFAULT 0 COMMENT '완료한 판 수',
  PRIMARY KEY (glu_id),
  UNIQUE KEY idx_mb_id (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 기본 판 설정 (1판)
INSERT IGNORE INTO mg_game_lottery_board (glb_id, glb_size, glb_bonus_point, glb_use)
VALUES (1, 100, 500, 1);

-- 기본 등수 설정
INSERT IGNORE INTO mg_game_lottery_prize (glp_rank, glp_name, glp_count, glp_point, glp_use) VALUES
  (1, '1등', 1, 500, 1),
  (2, '2등', 3, 200, 1),
  (3, '3등', 6, 100, 1),
  (4, '4등', 15, 50, 1),
  (5, '5등', 75, 10, 1);
