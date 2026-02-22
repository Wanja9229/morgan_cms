-- 상점 아이템 중복 제거: si_name + si_type 기준으로 가장 작은 si_id만 남기고 삭제
-- 관련 인벤토리/활성/선물 데이터도 정리

-- 1. 중복 아이템의 인벤토리 → 원본으로 합산
UPDATE mg_inventory iv
JOIN (
    SELECT si_id, si_name, si_type,
           (SELECT MIN(s2.si_id) FROM mg_shop_item s2 WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type) as keep_id
    FROM mg_shop_item s1
    WHERE si_id != (SELECT MIN(s2.si_id) FROM mg_shop_item s2 WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type)
) dup ON iv.si_id = dup.si_id
SET iv.si_id = dup.keep_id;

-- 2. 중복 활성 아이템 → 원본으로 변경
UPDATE mg_item_active ia
JOIN (
    SELECT si_id,
           (SELECT MIN(s2.si_id) FROM mg_shop_item s2 WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type) as keep_id
    FROM mg_shop_item s1
    WHERE si_id != (SELECT MIN(s2.si_id) FROM mg_shop_item s2 WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type)
) dup ON ia.si_id = dup.si_id
SET ia.si_id = dup.keep_id;

-- 3. 중복 선물 → 원본으로 변경
UPDATE mg_gift g
JOIN (
    SELECT si_id,
           (SELECT MIN(s2.si_id) FROM mg_shop_item s2 WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type) as keep_id
    FROM mg_shop_item s1
    WHERE si_id != (SELECT MIN(s2.si_id) FROM mg_shop_item s2 WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type)
) dup ON g.si_id = dup.si_id
SET g.si_id = dup.keep_id;

-- 4. 중복 구매 로그 → 원본으로 변경
UPDATE mg_shop_log sl
JOIN (
    SELECT si_id,
           (SELECT MIN(s2.si_id) FROM mg_shop_item s2 WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type) as keep_id
    FROM mg_shop_item s1
    WHERE si_id != (SELECT MIN(s2.si_id) FROM mg_shop_item s2 WHERE s2.si_name = s1.si_name AND s2.si_type = s1.si_type)
) dup ON sl.si_id = dup.si_id
SET sl.si_id = dup.keep_id;

-- 5. 중복 아이템 삭제 (원본 = 가장 작은 si_id만 유지)
DELETE s1 FROM mg_shop_item s1
INNER JOIN mg_shop_item s2
WHERE s1.si_name = s2.si_name AND s1.si_type = s2.si_type AND s1.si_id > s2.si_id;
