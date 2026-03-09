-- 전투 스탯 확정(잠금) 컬럼 추가
-- 저장 후 스탯 변경은 초기화 아이템으로만 가능
ALTER TABLE mg_battle_stat ADD COLUMN IF NOT EXISTS stat_locked TINYINT(1) NOT NULL DEFAULT 0 AFTER stat_stress;
