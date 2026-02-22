-- 게시판 재구성: 삭제, 이름 변경, 스킨 변경
-- 2026-02-22

-- 1. 자유게시판 삭제
DELETE FROM g5_board WHERE bo_table = 'free';

-- 2. 게시판 이름 변경
UPDATE g5_board SET bo_subject = '문의' WHERE bo_table = 'qa';
UPDATE g5_board SET bo_subject = '질문답변' WHERE bo_table = 'qna';
UPDATE g5_board SET bo_subject = '로그(일반)' WHERE bo_table = 'log';
UPDATE g5_board SET bo_subject = '로그(이미지)' WHERE bo_table = 'gallery';
UPDATE g5_board SET bo_subject = '오너게시판' WHERE bo_table = 'owner';
UPDATE g5_board SET bo_subject = '앓이란' WHERE bo_table = 'vent';

-- 3. 스킨 변경
UPDATE g5_board SET bo_skin = 'theme/memo', bo_mobile_skin = 'theme/memo' WHERE bo_table = 'qa';
UPDATE g5_board SET bo_skin = 'theme/basic', bo_mobile_skin = 'theme/basic' WHERE bo_table = 'log';
