-- 인벤토리 선물 기능: mg_gift 테이블에 gf_type 컬럼 추가
-- shop = 상점에서 구매 선물, inventory = 인벤토리에서 보유 아이템 선물
ALTER TABLE mg_gift ADD COLUMN IF NOT EXISTS gf_type VARCHAR(20) NOT NULL DEFAULT 'shop' AFTER si_id;
