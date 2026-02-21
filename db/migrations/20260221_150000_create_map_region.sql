-- 세계관 맵 지역 테이블 생성
CREATE TABLE IF NOT EXISTS mg_map_region (
    mr_id INT AUTO_INCREMENT PRIMARY KEY,
    mr_name VARCHAR(100) NOT NULL COMMENT '지역명',
    mr_desc TEXT COMMENT '지역 설명',
    mr_image VARCHAR(500) DEFAULT NULL COMMENT '지역 이미지 URL',
    mr_map_x FLOAT DEFAULT NULL COMMENT '맵 X좌표 (%)',
    mr_map_y FLOAT DEFAULT NULL COMMENT '맵 Y좌표 (%)',
    mr_order INT DEFAULT 0 COMMENT '정렬 순서',
    mr_use TINYINT(1) DEFAULT 1 COMMENT '사용 여부',
    mr_created DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
