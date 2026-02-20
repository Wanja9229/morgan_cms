-- 운세뽑기 미니게임 (M6-1)

CREATE TABLE IF NOT EXISTS `mg_game_fortune` (
  `gf_id` int NOT NULL AUTO_INCREMENT,
  `gf_star` tinyint NOT NULL DEFAULT 1 COMMENT '별 개수 1~5',
  `gf_text` varchar(255) NOT NULL COMMENT '운세 텍스트',
  `gf_point` int NOT NULL DEFAULT 10 COMMENT '획득 포인트',
  `gf_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
  `gf_sort` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`gf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `mg_game_fortune` (`gf_star`, `gf_text`, `gf_point`, `gf_use`, `gf_sort`) VALUES
(1, '오늘은 조용히 쉬는 게 좋겠어요', 10, 1, 1),
(2, '작은 행운이 찾아올 수 있어요', 25, 1, 2),
(3, '좋은 소식이 들려올 조짐이에요', 50, 1, 3),
(4, '하는 일마다 술술 풀리겠어요', 100, 1, 4),
(5, '모든 일이 완벽하게 맞아떨어져요', 200, 1, 5);
