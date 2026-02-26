-- =============================================
-- 중복 데이터 완전 정리 + UNIQUE 제약조건 추가
-- 원인: 마이그레이션 부분 실패 → 미기록 → 재실행 → INSERT 중복
-- 해결: FK 참조 정리 → 중복 삭제 → UNIQUE 제약조건으로 재발 방지
-- =============================================

-- ===== STEP 1: 인벤토리 정리 (UNIQUE(mb_id,si_id) 충돌 방지) =====

-- 1-a. 유저가 원본+중복 둘 다 보유 → 원본에 수량 합산
UPDATE mg_inventory iv_keep
JOIN (
    SELECT iv_orig.iv_id as keep_iv_id, SUM(iv_dup.iv_count) as total_extra
    FROM mg_inventory iv_orig
    JOIN mg_shop_item s_orig ON iv_orig.si_id = s_orig.si_id
    JOIN mg_shop_item s_dup ON s_dup.si_name = s_orig.si_name
        AND s_dup.si_type = s_orig.si_type AND s_dup.si_id > s_orig.si_id
    JOIN mg_inventory iv_dup ON iv_dup.mb_id = iv_orig.mb_id AND iv_dup.si_id = s_dup.si_id
    WHERE s_orig.si_id = (SELECT MIN(s3.si_id) FROM mg_shop_item s3
        WHERE s3.si_name = s_orig.si_name AND s3.si_type = s_orig.si_type)
    GROUP BY iv_orig.iv_id
) merge_data ON iv_keep.iv_id = merge_data.keep_iv_id
SET iv_keep.iv_count = iv_keep.iv_count + merge_data.total_extra;

-- 1-b. 합산 완료된 중복 인벤토리 행 삭제
DELETE iv_target FROM mg_inventory iv_target
JOIN (
    SELECT iv_dup.iv_id as dup_iv_id
    FROM mg_inventory iv_orig
    JOIN mg_shop_item s_orig ON iv_orig.si_id = s_orig.si_id
    JOIN mg_shop_item s_dup ON s_dup.si_name = s_orig.si_name
        AND s_dup.si_type = s_orig.si_type AND s_dup.si_id > s_orig.si_id
    JOIN mg_inventory iv_dup ON iv_dup.mb_id = iv_orig.mb_id AND iv_dup.si_id = s_dup.si_id
    WHERE s_orig.si_id = (SELECT MIN(s3.si_id) FROM mg_shop_item s3
        WHERE s3.si_name = s_orig.si_name AND s3.si_type = s_orig.si_type)
) to_del ON iv_target.iv_id = to_del.dup_iv_id;

-- 1-c. 중복만 보유한 경우 → si_id를 원본으로 변경 (UNIQUE 충돌 없음)
UPDATE mg_inventory iv
JOIN (
    SELECT s1.si_id as dup_id,
           (SELECT MIN(s2.si_id) FROM mg_shop_item s2
            WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type) as keep_id
    FROM mg_shop_item s1
    WHERE s1.si_id != (SELECT MIN(s2.si_id) FROM mg_shop_item s2
        WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type)
) dup ON iv.si_id = dup.dup_id
SET iv.si_id = dup.keep_id;

-- ===== STEP 2: 활성 아이템 정리 =====

UPDATE mg_item_active ia
JOIN (
    SELECT s1.si_id as dup_id,
           (SELECT MIN(s2.si_id) FROM mg_shop_item s2
            WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type) as keep_id
    FROM mg_shop_item s1
    WHERE s1.si_id != (SELECT MIN(s2.si_id) FROM mg_shop_item s2
        WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type)
) dup ON ia.si_id = dup.dup_id
SET ia.si_id = dup.keep_id;

-- 활성 아이템 중복 행 정리
DELETE ia1 FROM mg_item_active ia1
JOIN mg_item_active ia2
    ON ia1.mb_id = ia2.mb_id AND ia1.si_id = ia2.si_id
    AND ia1.ia_type = ia2.ia_type AND ia1.ia_id > ia2.ia_id;

-- ===== STEP 3: 선물 정리 =====

UPDATE mg_gift g
JOIN (
    SELECT s1.si_id as dup_id,
           (SELECT MIN(s2.si_id) FROM mg_shop_item s2
            WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type) as keep_id
    FROM mg_shop_item s1
    WHERE s1.si_id != (SELECT MIN(s2.si_id) FROM mg_shop_item s2
        WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type)
) dup ON g.si_id = dup.dup_id
SET g.si_id = dup.keep_id;

-- ===== STEP 4: 구매 로그 정리 =====

UPDATE mg_shop_log sl
JOIN (
    SELECT s1.si_id as dup_id,
           (SELECT MIN(s2.si_id) FROM mg_shop_item s2
            WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type) as keep_id
    FROM mg_shop_item s1
    WHERE s1.si_id != (SELECT MIN(s2.si_id) FROM mg_shop_item s2
        WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type)
) dup ON sl.si_id = dup.dup_id
SET sl.si_id = dup.keep_id;

-- ===== STEP 5: 중복 상점 아이템 삭제 =====

DELETE s1 FROM mg_shop_item s1
INNER JOIN mg_shop_item s2
WHERE s1.si_name = s2.si_name AND s1.si_type = s2.si_type AND s1.si_id > s2.si_id;

-- ===== STEP 6: mg_shop_item UNIQUE 제약조건 =====

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_shop_item' AND INDEX_NAME = 'uk_type_name');
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE mg_shop_item ADD UNIQUE KEY uk_type_name (si_type, si_name)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===== STEP 7: mg_expedition_drop 중복 정리 + UNIQUE =====

DELETE d1 FROM mg_expedition_drop d1
INNER JOIN mg_expedition_drop d2
WHERE d1.ea_id = d2.ea_id AND d1.mt_id = d2.mt_id AND d1.ed_id > d2.ed_id;

SET @idx_exists2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_expedition_drop' AND INDEX_NAME = 'uk_area_material');
SET @sql2 = IF(@idx_exists2 = 0,
    'ALTER TABLE mg_expedition_drop ADD UNIQUE KEY uk_area_material (ea_id, mt_id)',
    'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
