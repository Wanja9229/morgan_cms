-- 칭호 뽑기 시스템

-- 1. 칭호 풀 테이블
CREATE TABLE IF NOT EXISTS mg_title_pool (
    tp_id INT NOT NULL AUTO_INCREMENT,
    tp_type ENUM('prefix','suffix') NOT NULL COMMENT '접두/접미',
    tp_name VARCHAR(50) NOT NULL COMMENT '칭호 이름',
    tp_use TINYINT(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    tp_order INT NOT NULL DEFAULT 0 COMMENT '정렬',
    PRIMARY KEY (tp_id),
    INDEX idx_type_use (tp_type, tp_use)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='칭호 풀';

-- 2. 유저 보유 칭호
CREATE TABLE IF NOT EXISTS mg_member_title (
    mt_id INT NOT NULL AUTO_INCREMENT,
    mb_id VARCHAR(20) NOT NULL COMMENT '회원 ID',
    tp_id INT NOT NULL COMMENT '칭호 풀 ID',
    mt_datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '획득일',
    PRIMARY KEY (mt_id),
    UNIQUE KEY uk_mb_tp (mb_id, tp_id),
    INDEX idx_mb_id (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='유저 보유 칭호';

-- 3. 프로필 칭호 설정
CREATE TABLE IF NOT EXISTS mg_title_setting (
    mb_id VARCHAR(20) NOT NULL COMMENT '회원 ID',
    prefix_tp_id INT DEFAULT NULL COMMENT '접두칭호 ID',
    suffix_tp_id INT DEFAULT NULL COMMENT '접미칭호 ID',
    PRIMARY KEY (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='프로필 칭호 설정';

-- 4. 캐릭터 칭호 컬럼 (MySQL 5.7 호환: 존재하면 에러 무시)
-- ALTER TABLE mg_character ADD COLUMN ch_title_prefix_id INT DEFAULT NULL COMMENT '접두칭호 ID';
-- ALTER TABLE mg_character ADD COLUMN ch_title_suffix_id INT DEFAULT NULL COMMENT '접미칭호 ID';
-- 위 구문은 migrate.php에서 자동 실행 시 에러가 날 수 있으므로 프로시저로 처리
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_character' AND COLUMN_NAME = 'ch_title_prefix_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_character ADD COLUMN ch_title_prefix_id INT DEFAULT NULL COMMENT "접두칭호 ID"', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col_exists2 = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_character' AND COLUMN_NAME = 'ch_title_suffix_id');
SET @sql2 = IF(@col_exists2 = 0, 'ALTER TABLE mg_character ADD COLUMN ch_title_suffix_id INT DEFAULT NULL COMMENT "접미칭호 ID"', 'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- 5. 상점 타입 확장
ALTER TABLE mg_shop_item MODIFY COLUMN si_type
    ENUM('title','badge','nick_color','nick_effect','profile_border','equip',
         'emoticon_set','emoticon_reg','furniture','material','seal_bg',
         'seal_frame','seal_hover','profile_skin','profile_bg','char_slot',
         'concierge_extra','title_prefix','title_suffix','etc')
    NOT NULL DEFAULT 'etc' COMMENT '타입';

-- 6. 접두칭호 풀 시드 (53종)
INSERT IGNORE INTO mg_title_pool (tp_type, tp_name, tp_order) VALUES
('prefix','유쾌한',1),('prefix','우울한',2),('prefix','불쾌한',3),('prefix','다정한',4),('prefix','냉혹한',5),
('prefix','오만한',6),('prefix','나태한',7),('prefix','성실한',8),('prefix','게으른',9),('prefix','명석한',10),
('prefix','어리석은',11),('prefix','수상한',12),('prefix','고독한',13),('prefix','평범한',14),('prefix','기묘한',15),
('prefix','뻔뻔한',16),('prefix','소심한',17),('prefix','대범한',18),('prefix','변덕스러운',19),('prefix','시니컬한',20),
('prefix','날카로운',21),('prefix','전설의',22),('prefix','고대의',23),('prefix','타락한',24),('prefix','신성한',25),
('prefix','버림받은',26),('prefix','잊혀진',27),('prefix','저주받은',28),('prefix','축복받은',29),('prefix','무자비한',30),
('prefix','피에 굶주린',31),('prefix','불타는',32),('prefix','얼어붙은',33),('prefix','찬란한',34),('prefix','칠흑의',35),
('prefix','보이지 않는',36),('prefix','위대한',37),('prefix','꺾이지 않는',38),
('prefix','킹받는',39),('prefix','작고 소중한',40),('prefix','지나가던',41),('prefix','밥 굶은',42),('prefix','퇴근하고 싶은',43),
('prefix','운수 좋은',44),('prefix','운 없는',45),('prefix','주사위가 망한',46),('prefix','자본주의',47),('prefix','방구석',48),
('prefix','심심한',49),('prefix','길 잃은',50),('prefix','눈치 없는',51),('prefix','할 일 없는',52),('prefix','돈 많은',53);

-- 7. 접미칭호 풀 시드 (57종)
INSERT IGNORE INTO mg_title_pool (tp_type, tp_name, tp_order) VALUES
('suffix','검호',1),('suffix','검사',2),('suffix','마법사',3),('suffix','저격수',4),('suffix','총잡이',5),
('suffix','해결사',6),('suffix','암살자',7),('suffix','치유사',8),('suffix','성기사',9),('suffix','대장장이',10),
('suffix','연금술사',11),('suffix','해커',12),('suffix','정보상',13),('suffix','용병',14),('suffix','사냥꾼',15),
('suffix','도적',16),('suffix','음유시인',17),('suffix','탐험가',18),
('suffix','개척자',19),('suffix','관찰자',20),('suffix','이단아',21),('suffix','방랑자',22),('suffix','불청객',23),
('suffix','조력자',24),('suffix','흑막',25),('suffix','이방인',26),('suffix','그림자',27),('suffix','수집가',28),
('suffix','망령',29),('suffix','돌연변이',30),('suffix','귀족',31),('suffix','왕',32),('suffix','배신자',33),
('suffix','광대',34),('suffix','희생양',35),('suffix','생존자',36),
('suffix','감자',37),('suffix','고양이',38),('suffix','까마귀',39),('suffix','늑대',40),('suffix','올빼미',41),
('suffix','들개',42),('suffix','유령',43),('suffix','천사',44),('suffix','악마',45),('suffix','슬라임',46),
('suffix','먼지',47),('suffix','톱니바퀴',48),('suffix','고인물',49),('suffix','뉴비',50),('suffix','월급루팡',51),
('suffix','구경꾼',52),('suffix','샌드백',53),('suffix','팝콘러',54),('suffix','설명충',55),('suffix','과몰입러',56),('suffix','깍두기',57);

-- 8. 상점 아이템 시드 (뽑기 상품) — 이미 존재하면 스킵
INSERT INTO mg_shop_item (si_name, si_desc, si_price, si_type, si_effect, si_stock, si_limit_per_user, si_consumable, si_display, si_use, si_order, si_datetime)
SELECT '접두칭호 뽑기', '랜덤으로 접두칭호 1종을 획득합니다. 이미 보유한 칭호가 나올 수 있습니다.', 100, 'title_prefix', '{}', -1, 0, 1, 1, 1, 1, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='title_prefix' AND si_name='접두칭호 뽑기');
INSERT INTO mg_shop_item (si_name, si_desc, si_price, si_type, si_effect, si_stock, si_limit_per_user, si_consumable, si_display, si_use, si_order, si_datetime)
SELECT '접미칭호 뽑기', '랜덤으로 접미칭호 1종을 획득합니다. 이미 보유한 칭호가 나올 수 있습니다.', 100, 'title_suffix', '{}', -1, 0, 1, 1, 1, 2, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='title_suffix' AND si_name='접미칭호 뽑기');
