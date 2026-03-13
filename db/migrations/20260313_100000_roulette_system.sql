-- 룰렛 시스템 테이블 + 상점 아이템 타입 + 기본 설정

-- 1. 룰렛 보상/벌칙 항목
CREATE TABLE IF NOT EXISTS mg_roulette_prize (
    rp_id          INT AUTO_INCREMENT PRIMARY KEY,
    rp_name        VARCHAR(100) NOT NULL COMMENT '표시명',
    rp_desc        TEXT COMMENT '상세 설명',
    rp_type        ENUM('reward','penalty','blank','jackpot') NOT NULL DEFAULT 'blank',
    rp_icon        VARCHAR(200) COMMENT 'game-icons 아이콘명',
    rp_image       VARCHAR(500) COMMENT '벌칙 프사 이미지',
    rp_color       VARCHAR(7) NOT NULL DEFAULT '#6b7280' COMMENT '룰렛 칸 색상',
    rp_reward_type ENUM('point','material','item','title','nickname','profile_image','log','log_nickname','log_image','none') NOT NULL DEFAULT 'none',
    rp_reward_value TEXT COMMENT 'JSON',
    rp_duration_hours INT NOT NULL DEFAULT 0 COMMENT '시간제 벌칙 지속시간',
    rp_require_log TINYINT(1) NOT NULL DEFAULT 0 COMMENT '벌칙 로그 제출 필요',
    rp_weight      INT NOT NULL DEFAULT 10 COMMENT '확률 가중치',
    rp_order       INT NOT NULL DEFAULT 0,
    rp_use         TINYINT(1) NOT NULL DEFAULT 1,
    rp_created     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='룰렛 항목';

-- 2. 룰렛 사용 이력
CREATE TABLE IF NOT EXISTS mg_roulette_log (
    rl_id            INT AUTO_INCREMENT PRIMARY KEY,
    mb_id            VARCHAR(20) NOT NULL,
    rp_id            INT NOT NULL COMMENT '당첨 항목',
    rl_source        ENUM('spin','transfer_random','transfer_target') NOT NULL DEFAULT 'spin',
    rl_from_mb_id    VARCHAR(20) DEFAULT NULL COMMENT '떠넘기기 원래 주인',
    rl_status        ENUM('pending','active','completed','nullified','transferred') NOT NULL DEFAULT 'pending',
    rl_transfer_count TINYINT NOT NULL DEFAULT 0 COMMENT '전달 횟수',
    rl_original_nick VARCHAR(255) DEFAULT NULL COMMENT '닉변 시 원래 닉네임',
    rl_penalty_image VARCHAR(500) DEFAULT NULL COMMENT '프사 강제 변경 이미지',
    rl_bo_table      VARCHAR(20) DEFAULT NULL COMMENT '벌칙 로그 게시판',
    rl_wr_id         INT DEFAULT NULL COMMENT '벌칙 로그 글 ID',
    rl_expires_at    DATETIME DEFAULT NULL COMMENT '시간제 벌칙 만료',
    rl_cost          INT NOT NULL DEFAULT 0 COMMENT '소모 포인트',
    rl_datetime      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mb_status (mb_id, rl_status),
    INDEX idx_expires (rl_status, rl_expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='룰렛 이력';

-- 3. si_type ENUM 확장
ALTER TABLE mg_shop_item MODIFY si_type ENUM('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','emoticon_reg','furniture','material','seal_bg','seal_frame','seal_hover','seal_effect','profile_skin','profile_bg','profile_effect','char_slot','concierge_extra','title_prefix','title_suffix','radio_song','radio_ment','relation_slot','concierge_direct_pick','rp_pin','expedition_time','expedition_reward','expedition_stamina','expedition_slot','write_expand','achievement_slot','concierge_boost','nick_bg','stamina_recover','battle_weapon','battle_armor','battle_accessory','battle_consumable','battle_skill_book','stat_reset','roulette_nullify','roulette_transfer_random','roulette_transfer_target','etc') NOT NULL DEFAULT 'etc' COMMENT '타입';

-- 4. 기본 설정
INSERT IGNORE INTO mg_config (cf_key, cf_value, cf_desc) VALUES
('roulette_use', '0', '룰렛 활성화'),
('roulette_cost', '100', '룰렛 1회 비용'),
('roulette_daily_limit', '3', '1일 제한 횟수'),
('roulette_cooldown', '0', '쿨다운(분)'),
('roulette_board', 'roulette', '벌칙 로그 게시판'),
('roulette_jackpot_pool', '0', '잭팟 누적 풀'),
('roulette_transfer_reveal', '0', '떠넘기기 보낸 사람 공개'),
('roulette_pending_hours', '24', '미확인 벌칙 자동 확정 시간');

-- 5. 시드 상점 아이템
INSERT INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'roulette_nullify', '벌칙 무효화권', '룰렛 벌칙을 즉시 무효화합니다.', 300, '{}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'roulette_nullify');

INSERT INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'roulette_transfer_random', '랜덤 떠넘기기권', '룰렛 벌칙을 랜덤 회원에게 전달합니다.', 200, '{}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'roulette_transfer_random');

INSERT INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'roulette_transfer_target', '지목 떠넘기기권', '룰렛 벌칙을 특정 회원에게 전달합니다.', 400, '{}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'roulette_transfer_target');

-- 6. 시드 룰렛 항목
INSERT IGNORE INTO mg_roulette_prize (rp_name, rp_type, rp_reward_type, rp_reward_value, rp_weight, rp_color, rp_icon, rp_desc, rp_duration_hours, rp_require_log, rp_use, rp_order) VALUES
('100 포인트', 'reward', 'point', '100', 30, '#4ade80', '💰', '100 포인트를 획득합니다', 0, 0, 1, 1),
('300 포인트', 'reward', 'point', '300', 15, '#22d3ee', '💎', '300 포인트를 획득합니다', 0, 0, 1, 2),
('500 포인트', 'reward', 'point', '500', 5, '#a78bfa', '🌟', '500 포인트를 획득합니다', 0, 0, 1, 3),
('꽝', 'blank', 'none', '', 25, '#6b7280', '💨', '아쉽지만 꽝입니다', 0, 0, 1, 4),
('닉네임 변경', 'penalty', 'nickname', '🐔치킨러버', 10, '#f87171', '🔥', '닉네임이 강제 변경됩니다', 24, 1, 1, 5),
('프사 변경', 'penalty', 'profile_image', '', 8, '#fb923c', '😈', '프로필 이미지가 강제 변경됩니다', 24, 1, 1, 6),
('잭팟!', 'jackpot', 'none', '', 2, '#fbbf24', '🎉', '축적된 잭팟 포인트를 모두 획득합니다!', 0, 0, 1, 7),
('50 포인트', 'reward', 'point', '50', 5, '#86efac', '🪙', '50 포인트를 획득합니다', 0, 0, 1, 8);
