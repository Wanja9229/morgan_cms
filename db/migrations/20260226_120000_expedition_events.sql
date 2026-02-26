-- 파견 이벤트 시스템
-- 이벤트 풀 + 파견지별 이벤트 매칭

CREATE TABLE IF NOT EXISTS mg_expedition_event (
    ee_id INT AUTO_INCREMENT PRIMARY KEY,
    ee_name VARCHAR(100) NOT NULL DEFAULT '',
    ee_desc TEXT,
    ee_icon VARCHAR(255) NOT NULL DEFAULT '',
    ee_effect_type ENUM('point_bonus','point_penalty','material_bonus','material_penalty','reward_loss') NOT NULL DEFAULT 'point_bonus',
    ee_effect JSON,
    ee_order INT NOT NULL DEFAULT 0,
    ee_created DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mg_expedition_event_area (
    eea_id INT AUTO_INCREMENT PRIMARY KEY,
    ea_id INT NOT NULL DEFAULT 0,
    ee_id INT NOT NULL DEFAULT 0,
    eea_chance INT NOT NULL DEFAULT 10,
    INDEX idx_ea_id (ea_id),
    INDEX idx_ee_id (ee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
