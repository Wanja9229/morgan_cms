-- 앓이란(vent) 코르크 보드 + 판 시스템 활성화
-- 카테고리 기능을 활용하여 판(panel) 시스템 구현

-- 게시판 카테고리 활성화 + 1판 설정
UPDATE g5_board SET bo_use_category = 1, bo_category_list = '1'
  WHERE bo_table = 'vent' AND bo_use_category = 0;

-- 기존 글들을 1판에 배치 (ca_name이 비어있는 글)
UPDATE g5_write_vent SET ca_name = '1'
  WHERE (ca_name = '' OR ca_name IS NULL);
