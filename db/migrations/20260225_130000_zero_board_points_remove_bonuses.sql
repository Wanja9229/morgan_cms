-- 게시판 포인트 필드 일괄 0 설정 (Morgan 보상 시스템만 사용)
UPDATE g5_board SET bo_read_point = 0, bo_write_point = 0, bo_comment_point = 0, bo_download_point = 0;

-- 보너스 필드 일괄 0 설정 (500자/1000자/이미지 보너스 제거)
UPDATE mg_board_reward SET br_bonus_500 = 0, br_bonus_1000 = 0, br_bonus_image = 0;
