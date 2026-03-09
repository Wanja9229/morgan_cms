-- 전투 스탯 테이블에 CON(근성), LUK(행운) 컬럼 추가
ALTER TABLE mg_battle_stat ADD COLUMN IF NOT EXISTS stat_con int DEFAULT 5 AFTER stat_int;
ALTER TABLE mg_battle_stat ADD COLUMN IF NOT EXISTS stat_luk int DEFAULT 5 AFTER stat_con;
