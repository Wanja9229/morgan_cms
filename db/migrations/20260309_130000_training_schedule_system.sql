-- 수업 스케줄 시스템

CREATE TABLE IF NOT EXISTS mg_training_class (
    tc_id       int AUTO_INCREMENT PRIMARY KEY,
    tc_name     varchar(100) NOT NULL,
    tc_desc     text,
    tc_icon     varchar(255) DEFAULT '',
    tc_stat     varchar(20) NOT NULL DEFAULT 'stat_str' COMMENT 'stat_hp, stat_str, stat_dex, stat_int, stat_con, stat_luk, none',
    tc_stat_amount int NOT NULL DEFAULT 1 COMMENT '이수 완료 시 스탯 증가량',
    tc_required int NOT NULL DEFAULT 10 COMMENT '이수에 필요한 총 슬롯 횟수',
    tc_cost     int NOT NULL DEFAULT 0 COMMENT '슬롯당 수강료 (포인트)',
    tc_stress   int NOT NULL DEFAULT 5 COMMENT '슬롯당 스트레스 변화 (음수=감소)',
    tc_max_repeat int NOT NULL DEFAULT 0 COMMENT '캐릭터당 최대 이수 횟수 (0=무제한)',
    tc_order    int NOT NULL DEFAULT 0,
    tc_use      tinyint NOT NULL DEFAULT 1,
    tc_created  datetime DEFAULT CURRENT_TIMESTAMP,
    tc_updated  datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mg_training_schedule (
    ts_id       int AUTO_INCREMENT PRIMARY KEY,
    ch_id       int NOT NULL,
    mb_id       varchar(20) NOT NULL,
    ts_year     smallint NOT NULL COMMENT '연도',
    ts_week     tinyint NOT NULL COMMENT 'ISO 주차 (1~53)',
    ts_slots    json NOT NULL COMMENT '15슬롯 배열',
    ts_total_cost int NOT NULL DEFAULT 0 COMMENT '총 수강료',
    ts_settled  tinyint NOT NULL DEFAULT 0 COMMENT '0=미정산, 1=정산완료',
    ts_created  datetime DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ch_week (ch_id, ts_year, ts_week),
    KEY idx_mb (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mg_training_progress (
    tp_id       int AUTO_INCREMENT PRIMARY KEY,
    ch_id       int NOT NULL,
    tc_id       int NOT NULL,
    tp_progress decimal(6,1) NOT NULL DEFAULT 0 COMMENT '현재 누적 진행도',
    tp_completed int NOT NULL DEFAULT 0 COMMENT '이수 완료 횟수',
    tp_updated  datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ch_class (ch_id, tc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 스트레스 컬럼 추가
ALTER TABLE mg_battle_stat ADD COLUMN IF NOT EXISTS stat_stress int NOT NULL DEFAULT 0 COMMENT '현재 스트레스 (0~100)';
