-- 라디오 위젯 테이블 생성

CREATE TABLE IF NOT EXISTS mg_radio_config (
    config_id       INT PRIMARY KEY DEFAULT 1,
    is_active       TINYINT NOT NULL DEFAULT 1,
    play_mode       ENUM('sequential','random') NOT NULL DEFAULT 'sequential',
    weather_mode    ENUM('api','manual') NOT NULL DEFAULT 'manual',
    weather_city    VARCHAR(100) NULL,
    weather_api_key VARCHAR(100) NULL,
    manual_temp     INT NULL,
    manual_weather  VARCHAR(20) NULL,
    ment_mode       ENUM('sequential','random') NOT NULL DEFAULT 'sequential',
    ment_interval   INT NOT NULL DEFAULT 12,
    weather_cache   JSON NULL,
    weather_cached_at DATETIME NULL,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO mg_radio_config (config_id) VALUES (1);

CREATE TABLE IF NOT EXISTS mg_radio_playlist (
    track_id    INT AUTO_INCREMENT PRIMARY KEY,
    youtube_url VARCHAR(255) NOT NULL,
    youtube_vid VARCHAR(20) NOT NULL,
    title       VARCHAR(200) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT NOT NULL DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mg_radio_ments (
    ment_id     INT AUTO_INCREMENT PRIMARY KEY,
    content     VARCHAR(200) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT NOT NULL DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
