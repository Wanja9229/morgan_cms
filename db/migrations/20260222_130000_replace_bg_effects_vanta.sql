-- =============================================
-- 프로필 배경 효과: tsParticles → Vanta.js 전환
-- 기존 6종 삭제 + Vanta.js 14종 등록
-- =============================================

-- 1. ch_profile_bg_color 컬럼 추가 (유저 커스텀 색상)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_character' AND COLUMN_NAME = 'ch_profile_bg_color');
SET @ddl = IF(@col_exists = 0,
    'ALTER TABLE mg_character ADD COLUMN ch_profile_bg_color VARCHAR(7) NOT NULL DEFAULT \'#f59f0a\' AFTER ch_profile_bg',
    'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. 기존 tsParticles 배경 아이템 관련 데이터 정리
-- 인벤토리 & 활성 아이템에서 기존 profile_bg 아이템 제거
DELETE ia FROM mg_item_active ia
    JOIN mg_shop_item si ON ia.si_id = si.si_id
    WHERE si.si_type = 'profile_bg' AND si.si_effect LIKE '%starfield%'
       OR si.si_effect LIKE '%aurora%'
       OR si.si_effect LIKE '%particles%'
       OR si.si_effect LIKE '%grid_pulse%'
       OR si.si_effect LIKE '%amber_shimmer%'
       OR si.si_effect LIKE '%deep_mist%';

DELETE iv FROM mg_inventory iv
    JOIN mg_shop_item si ON iv.si_id = si.si_id
    WHERE si.si_type = 'profile_bg' AND si.si_effect LIKE '%starfield%'
       OR si.si_effect LIKE '%aurora%'
       OR si.si_effect LIKE '%particles%'
       OR si.si_effect LIKE '%grid_pulse%'
       OR si.si_effect LIKE '%amber_shimmer%'
       OR si.si_effect LIKE '%deep_mist%';

-- 기존 tsParticles 상점 아이템 삭제
DELETE FROM mg_shop_item WHERE si_type = 'profile_bg'
    AND JSON_UNQUOTE(JSON_EXTRACT(si_effect, '$.bg_id')) IN ('starfield','aurora','particles','grid_pulse','amber_shimmer','deep_mist');

-- 캐릭터 테이블에서 구 bg_id 초기화
UPDATE mg_character SET ch_profile_bg = ''
    WHERE ch_profile_bg IN ('starfield','aurora','particles','grid_pulse','amber_shimmer','deep_mist');

-- 3. Vanta.js 14종 등록 (중복 방지)
INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '새 떼', 'profile_bg', 200, 'Vanta.js BIRDS - 마우스에 반응하는 새 떼 애니메이션', 1, '{"bg_id":"birds"}', 1, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"birds"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '안개', 'profile_bg', 250, 'Vanta.js FOG - 몽환적인 안개 효과', 1, '{"bg_id":"fog"}', 2, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"fog"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '물결', 'profile_bg', 200, 'Vanta.js WAVES - 물결치는 파도 표면', 1, '{"bg_id":"waves"}', 3, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"waves"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '구름', 'profile_bg', 180, 'Vanta.js CLOUDS - 하늘 위 구름', 1, '{"bg_id":"clouds"}', 4, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"clouds"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '먹구름', 'profile_bg', 220, 'Vanta.js CLOUDS2 - 극적인 먹구름', 1, '{"bg_id":"clouds2"}', 5, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"clouds2"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '글로브', 'profile_bg', 300, 'Vanta.js GLOBE - 와이어프레임 회전 지구본', 1, '{"bg_id":"globe"}', 6, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"globe"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '네트워크', 'profile_bg', 250, 'Vanta.js NET - 디지털 네트워크 연결', 1, '{"bg_id":"net"}', 7, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"net"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '세포', 'profile_bg', 180, 'Vanta.js CELLS - 유기적 세포 구조', 1, '{"bg_id":"cells"}', 8, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"cells"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '나뭇가지', 'profile_bg', 200, 'Vanta.js TRUNK - 가지치기 성장 구조', 1, '{"bg_id":"trunk"}', 9, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"trunk"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '지형도', 'profile_bg', 220, 'Vanta.js TOPOLOGY - 3D 지형도', 1, '{"bg_id":"topology"}', 10, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"topology"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '점 그리드', 'profile_bg', 180, 'Vanta.js DOTS - 맥동하는 점 그리드', 1, '{"bg_id":"dots"}', 11, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"dots"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '동심원', 'profile_bg', 250, 'Vanta.js RINGS - 동심원 리플', 1, '{"bg_id":"rings"}', 12, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"rings"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '수면', 'profile_bg', 200, 'Vanta.js RIPPLE - 잔잔한 수면 리플', 1, '{"bg_id":"ripple"}', 13, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"ripple"%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '빛 번짐', 'profile_bg', 350, 'Vanta.js HALO - 우주적 빛 번짐 글로우', 1, '{"bg_id":"halo"}', 14, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"halo"%');
