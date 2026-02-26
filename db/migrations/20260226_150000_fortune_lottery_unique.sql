-- 운세/종이뽑기 테이블 UNIQUE 제약조건 추가 (중복 방지)
-- mg_game_fortune: INSERT IGNORE가 PK(auto_increment)만 있어 중복 미방지
-- mg_game_lottery_prize: 동일 이슈

-- Step 1: 운세 중복 제거 (같은 star+text 중 가장 낮은 gf_id만 남기기)
DELETE f1 FROM mg_game_fortune f1
INNER JOIN (
    SELECT gf_star, gf_text, MIN(gf_id) AS keep_id
    FROM mg_game_fortune
    GROUP BY gf_star, gf_text
    HAVING COUNT(*) > 1
) dup ON f1.gf_star = dup.gf_star AND f1.gf_text = dup.gf_text AND f1.gf_id > dup.keep_id;

-- Step 2: 운세 UNIQUE 제약조건 추가 (이미 존재하면 스킵)
SELECT IF(
    (SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_game_fortune' AND INDEX_NAME='uk_star_text') = 0,
    'ALTER TABLE mg_game_fortune ADD UNIQUE KEY uk_star_text (gf_star, gf_text)',
    'SELECT 1'
) INTO @_sql_f;
PREPARE _stmt_f FROM @_sql_f;
EXECUTE _stmt_f;
DEALLOCATE PREPARE _stmt_f;

-- Step 3: 종이뽑기 상품 중복 제거 (테이블 존재 시만)
DELETE p1 FROM mg_game_lottery_prize p1
INNER JOIN (
    SELECT glp_rank, glp_name, MIN(glp_id) AS keep_id
    FROM mg_game_lottery_prize
    GROUP BY glp_rank, glp_name
    HAVING COUNT(*) > 1
) dup ON p1.glp_rank = dup.glp_rank AND p1.glp_name = dup.glp_name AND p1.glp_id > dup.keep_id;

-- Step 4: 종이뽑기 상품 UNIQUE 제약조건 추가 (이미 존재하면 스킵)
SELECT IF(
    (SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_game_lottery_prize' AND INDEX_NAME='uk_rank_name') = 0,
    'ALTER TABLE mg_game_lottery_prize ADD UNIQUE KEY uk_rank_name (glp_rank, glp_name)',
    'SELECT 1'
) INTO @_sql_l;
PREPARE _stmt_l FROM @_sql_l;
EXECUTE _stmt_l;
DEALLOCATE PREPARE _stmt_l;
