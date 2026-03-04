-- 유저별 위젯 설정 (사이드바 순서, 표시, 커스텀 config)
CREATE TABLE IF NOT EXISTS mg_user_widget (
    uw_id INT AUTO_INCREMENT PRIMARY KEY,
    mb_id VARCHAR(20) NOT NULL,
    widget_name VARCHAR(50) NOT NULL,
    widget_order INT NOT NULL DEFAULT 0,
    widget_visible TINYINT(1) NOT NULL DEFAULT 1,
    widget_config TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_mb_widget (mb_id, widget_name),
    KEY idx_mb_order (mb_id, widget_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
