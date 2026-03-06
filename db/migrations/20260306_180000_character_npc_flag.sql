-- NPC 캐릭터 플래그 추가
ALTER TABLE mg_character ADD COLUMN IF NOT EXISTS ch_is_npc tinyint(1) NOT NULL DEFAULT 0 COMMENT 'NPC 여부' AFTER ch_main;
