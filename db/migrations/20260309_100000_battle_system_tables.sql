-- =============================================
-- 전투 시스템 (Battle System) — 테이블 7개 + 기존 테이블 수정
-- =============================================

-- 1) 전투 스탯 (캐릭터당 1개)
CREATE TABLE IF NOT EXISTS mg_battle_stat (
    bs_id           int AUTO_INCREMENT PRIMARY KEY,
    ch_id           int NOT NULL,
    mb_id           varchar(20) NOT NULL,
    stat_hp         int DEFAULT 5,
    stat_str        int DEFAULT 5,
    stat_dex        int DEFAULT 5,
    stat_int        int DEFAULT 5,
    stat_points     int DEFAULT 0,
    equip_weapon    int DEFAULT 0,
    equip_armor     int DEFAULT 0,
    equip_accessory int DEFAULT 0,
    skill_slot_1    int DEFAULT 0,
    skill_slot_2    int DEFAULT 0,
    skill_slot_3    int DEFAULT 0,
    bs_created      datetime DEFAULT CURRENT_TIMESTAMP,
    bs_updated      datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (ch_id),
    KEY (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) 기력 (캐릭터당 1개)
CREATE TABLE IF NOT EXISTS mg_battle_energy (
    ben_id          int AUTO_INCREMENT PRIMARY KEY,
    ch_id           int NOT NULL,
    mb_id           varchar(20) NOT NULL,
    current_energy  int DEFAULT 5,
    max_energy      int DEFAULT 10,
    last_charge_at  datetime DEFAULT CURRENT_TIMESTAMP,
    last_hp_regen_at datetime DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (ch_id),
    KEY (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) 몬스터 템플릿 (관리자 등록)
CREATE TABLE IF NOT EXISTS mg_battle_monster (
    bm_id           int AUTO_INCREMENT PRIMARY KEY,
    bm_name         varchar(100) NOT NULL,
    bm_image        varchar(500) DEFAULT '',
    bm_type         enum('boss','mob','story_boss') DEFAULT 'mob',
    bm_hp           int DEFAULT 1000,
    bm_atk          int DEFAULT 50,
    bm_def          int DEFAULT 10,
    bm_time_limit   int DEFAULT 7200,
    bm_reward_point int DEFAULT 500,
    bm_reward_drops text,
    bm_areas        text,
    bm_mob_count    int DEFAULT 1,
    bm_story_regen_pct int DEFAULT 5,
    bm_use          tinyint(1) DEFAULT 1,
    bm_order        int DEFAULT 0,
    bm_created      datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) 전투 인카운터 (발견된 전투 인스턴스)
CREATE TABLE IF NOT EXISTS mg_battle_encounter (
    be_id           int AUTO_INCREMENT PRIMARY KEY,
    bm_id           int NOT NULL,
    be_type         enum('boss','mob_group','story_boss') DEFAULT 'boss',
    be_status       enum('discovered','active','cleared','failed','expired')
                    DEFAULT 'discovered',
    be_monsters     text,
    be_time_limit   int DEFAULT 7200,
    be_reward_point int DEFAULT 500,
    be_reward_drops text,
    taunt_queue     text,
    be_debuffs      text,
    discoverer_mb_id varchar(20) NOT NULL,
    discoverer_ch_id int NOT NULL,
    ea_id           int DEFAULT 0,
    el_id           int DEFAULT 0,
    be_story_group_id varchar(50) DEFAULT '',
    be_story_hp_carry int DEFAULT 0,
    be_story_round  int DEFAULT 1,
    be_discovered_at datetime DEFAULT CURRENT_TIMESTAMP,
    be_started_at   datetime DEFAULT NULL,
    be_ended_at     datetime DEFAULT NULL,
    KEY (be_status),
    KEY (discoverer_mb_id),
    KEY (be_story_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) 참여자 슬롯
CREATE TABLE IF NOT EXISTS mg_battle_slot (
    bsl_id          int AUTO_INCREMENT PRIMARY KEY,
    be_id           int NOT NULL,
    mb_id           varchar(20) NOT NULL,
    ch_id           int NOT NULL,
    slot_role       enum('discoverer','participant') DEFAULT 'participant',
    slot_status     enum('active','dead','retreated') DEFAULT 'active',
    current_hp      int DEFAULT 0,
    max_hp          int DEFAULT 0,
    total_damage    int DEFAULT 0,
    total_heal      int DEFAULT 0,
    buff_count      int DEFAULT 0,
    debuff_count    int DEFAULT 0,
    taunt_absorb    int DEFAULT 0,
    action_count    int DEFAULT 0,
    buffs_active    text,
    bsl_joined_at   datetime DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (be_id, ch_id),
    UNIQUE KEY (be_id, mb_id),
    KEY (be_id, slot_status),
    KEY (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6) 전투 로그
CREATE TABLE IF NOT EXISTS mg_battle_log (
    bl_id           int AUTO_INCREMENT PRIMARY KEY,
    be_id           int NOT NULL,
    mb_id           varchar(20) NOT NULL,
    ch_id           int NOT NULL,
    bl_action       varchar(30) NOT NULL,
    bl_target_type  enum('monster','player','self') DEFAULT 'monster',
    bl_target_id    int DEFAULT 0,
    bl_damage       int DEFAULT 0,
    bl_heal         int DEFAULT 0,
    bl_counter      int DEFAULT 0,
    bl_counter_target_ch int DEFAULT 0,
    bl_is_crit      tinyint(1) DEFAULT 0,
    bl_is_evade     tinyint(1) DEFAULT 0,
    bl_detail       text,
    bl_datetime     datetime DEFAULT CURRENT_TIMESTAMP,
    KEY (be_id, bl_datetime),
    KEY (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7) 스킬 정의
CREATE TABLE IF NOT EXISTS mg_battle_skill (
    sk_id           int AUTO_INCREMENT PRIMARY KEY,
    sk_code         varchar(30) NOT NULL,
    sk_name         varchar(50) NOT NULL,
    sk_desc         varchar(200) DEFAULT '',
    sk_icon         varchar(100) DEFAULT '',
    sk_type         enum('damage','heal','buff','debuff','taunt') NOT NULL,
    sk_stamina      int DEFAULT 2,
    sk_target       enum('enemy_single','enemy_all','ally_single','ally_multi','ally_all','self') NOT NULL,
    sk_target_count int DEFAULT 1,
    sk_base_stat    enum('str','dex','int','none') DEFAULT 'dex',
    sk_multiplier   decimal(3,2) DEFAULT 1.50,
    sk_buff_stat    varchar(20) DEFAULT '',
    sk_buff_value   int DEFAULT 0,
    sk_buff_turns   int DEFAULT 3,
    sk_guard_reduction int DEFAULT 0,
    sk_stat_req     varchar(30) DEFAULT '',
    sk_unlock_type  enum('default','shop','drop','achievement') DEFAULT 'default',
    sk_unlock_ref   int DEFAULT 0,
    sk_use          tinyint(1) DEFAULT 1,
    sk_order        int DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 기존 테이블 수정
-- =============================================

-- mg_expedition_event.ee_effect_type에 'battle_encounter' 추가
-- ENUM → VARCHAR 전환 (확장 용이)
ALTER TABLE mg_expedition_event
    MODIFY ee_effect_type varchar(30) DEFAULT 'point_bonus';

-- mg_shop_item.si_type에 전투 관련 타입 추가
ALTER TABLE mg_shop_item
    MODIFY si_type enum(
        'title','badge','nick_color','nick_effect','profile_border','equip',
        'emoticon_set','emoticon_reg','furniture','material',
        'seal_bg','seal_frame','seal_hover','seal_effect',
        'profile_skin','profile_bg','profile_effect','char_slot',
        'concierge_extra','title_prefix','title_suffix',
        'radio_song','radio_ment','relation_slot','concierge_direct_pick',
        'rp_pin','expedition_time','expedition_reward','expedition_stamina',
        'expedition_slot','write_expand','achievement_slot','concierge_boost',
        'nick_bg','stamina_recover',
        'battle_weapon','battle_armor','battle_accessory',
        'battle_consumable','battle_skill_book',
        'etc'
    ) NOT NULL DEFAULT 'etc';

-- =============================================
-- 기본 스킬 시드 데이터
-- =============================================

INSERT IGNORE INTO mg_battle_skill
    (sk_code, sk_name, sk_desc, sk_icon, sk_type, sk_stamina, sk_target, sk_target_count, sk_base_stat, sk_multiplier, sk_buff_stat, sk_buff_value, sk_buff_turns, sk_guard_reduction, sk_stat_req, sk_unlock_type, sk_order)
VALUES
    ('power_attack',  '강타',     'STR 기반 강화 물리 공격',           '💥', 'damage', 2, 'enemy_single', 1, 'str', 1.50, '',    0,  0, 0,  '', 'default', 1),
    ('skill_attack',  '스킬공격', 'DEX 기반 스킬 데미지',              '🎯', 'damage', 2, 'enemy_single', 1, 'dex', 1.50, '',    0,  0, 0,  '', 'default', 2),
    ('aoe_attack',    '전체공격', '모든 적에게 DEX 기반 데미지',        '🌀', 'damage', 3, 'enemy_all',    1, 'dex', 0.60, '',    0,  0, 0,  '', 'shop',    3),
    ('heal',          '힐링',     '아군 3명 HP 회복',                   '💚', 'heal',   2, 'ally_multi',   3, 'int', 1.00, '',    0,  0, 0,  '', 'default', 4),
    ('aoe_heal',      '전체힐',   '모든 아군 HP 회복 (0.4배)',          '💖', 'heal',   3, 'ally_all',     1, 'int', 0.40, '',    0,  0, 0,  '', 'shop',    5),
    ('buff',          '버프',     '아군 공격력 일시 증가',              '⬆️', 'buff',   2, 'ally_multi',   2, 'int', 0.00, 'atk', 20, 2, 0,  '', 'shop',    6),
    ('debuff',        '디버프',   '적 방어력 일시 감소',                '⬇️', 'debuff', 2, 'enemy_single', 1, 'int', 0.00, 'def', 20, 5, 0,  '', 'shop',    7),
    ('taunt',         '도발',     '모든 반격이 나에게 집중',            '🛡️', 'taunt',  2, 'self',         1, 'none', 0.00, '',   0,  5, 0,  '', 'shop',    8),
    ('guard',         '수호',     '도발 + 받는 데미지 30% 감소',        '🏰', 'taunt',  3, 'self',         1, 'none', 0.00, '',   0,  5, 30, '', 'shop',    9),
    ('revive',        '부활',     '전사한 아군 1명 부활 (HP=INT*3)',     '✨', 'heal',   3, 'ally_single',  1, 'int',  0.00, '',   0,  0, 0,  'int:15', 'shop', 10);
