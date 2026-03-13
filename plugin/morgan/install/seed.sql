-- Morgan Edition — 테넌트 시드 데이터
-- 새 테넌트 프로비저닝 시 gnuboard5.sql + install.sql 이후 실행
-- 관리자 계정은 TenantManager.php에서 별도 생성
-- 플레이스홀더: {ADMIN_ID}, {ADMIN_EMAIL} → TenantManager에서 치환

-- ============================================================
-- 1. g5_config 기본 설정
-- ============================================================
INSERT IGNORE INTO g5_config SET
    cf_title = 'Morgan CMS',
    cf_theme = 'morgan',
    cf_admin = '{ADMIN_ID}',
    cf_admin_email = '{ADMIN_EMAIL}',
    cf_admin_email_name = 'Morgan CMS',
    cf_use_point = '1',
    cf_use_copy_log = '1',
    cf_login_point = '100',
    cf_memo_send_point = '500',
    cf_cut_name = '15',
    cf_nick_modify = '60',
    cf_new_skin = 'basic',
    cf_new_rows = '15',
    cf_search_skin = 'basic',
    cf_connect_skin = 'basic',
    cf_read_point = '0',
    cf_write_point = '0',
    cf_comment_point = '0',
    cf_download_point = '0',
    cf_write_pages = '10',
    cf_mobile_pages = '5',
    cf_link_target = '_blank',
    cf_delay_sec = '30',
    cf_filter = '',
    cf_possible_ip = '',
    cf_intercept_ip = '',
    cf_member_skin = 'basic',
    cf_mobile_new_skin = 'basic',
    cf_mobile_search_skin = 'basic',
    cf_mobile_connect_skin = 'basic',
    cf_mobile_member_skin = 'basic',
    cf_faq_skin = 'basic',
    cf_mobile_faq_skin = 'basic',
    cf_editor = 'toastui',
    cf_captcha_mp3 = 'basic',
    cf_register_level = '2',
    cf_register_point = '1000',
    cf_icon_level = '2',
    cf_leave_day = '30',
    cf_search_part = '10000',
    cf_email_use = '1',
    cf_prohibit_id = 'admin,administrator,root,guest',
    cf_prohibit_email = '',
    cf_new_del = '30',
    cf_memo_del = '180',
    cf_visit_del = '180',
    cf_popular_del = '180',
    cf_use_member_icon = '2',
    cf_member_icon_size = '5000',
    cf_member_icon_width = '22',
    cf_member_icon_height = '22',
    cf_member_img_size = '50000',
    cf_member_img_width = '60',
    cf_member_img_height = '60',
    cf_login_minutes = '10',
    cf_image_extension = 'gif|jpg|jpeg|png|webp',
    cf_flash_extension = 'swf',
    cf_movie_extension = 'asx|asf|wmv|wma|mpg|mpeg|mov|avi|mp3',
    cf_formmail_is_member = '1',
    cf_page_rows = '15',
    cf_mobile_page_rows = '15',
    cf_cert_limit = '2',
    cf_stipulation = '서비스 이용약관을 입력해 주세요.',
    cf_privacy = '개인정보 처리방침을 입력해 주세요.';

-- ============================================================
-- 2. 1:1 문의 설정
-- ============================================================
INSERT IGNORE INTO g5_qa_config
    (qa_title, qa_category, qa_skin, qa_mobile_skin, qa_use_email, qa_req_email,
     qa_use_hp, qa_req_hp, qa_use_editor, qa_subject_len, qa_mobile_subject_len,
     qa_page_rows, qa_mobile_page_rows, qa_image_width, qa_upload_size, qa_insert_content)
VALUES
    ('1:1문의', '일반|기타', 'basic', 'basic', '1', '0',
     '1', '0', '1', '60', '30',
     '15', '15', '600', '1048576', '');

-- ============================================================
-- 3. 내용 관리
-- ============================================================
INSERT IGNORE INTO g5_content (co_id, co_html, co_subject, co_content, co_skin, co_mobile_skin) VALUES
    ('company', '1', '소개', '<p>소개 내용을 입력해 주세요.</p>', 'basic', 'basic'),
    ('privacy', '1', '개인정보 처리방침', '<p>개인정보 처리방침을 입력해 주세요.</p>', 'basic', 'basic'),
    ('provision', '1', '서비스 이용약관', '<p>서비스 이용약관을 입력해 주세요.</p>', 'basic', 'basic');

-- ============================================================
-- 4. 게시판 그룹
-- ============================================================
INSERT IGNORE INTO g5_group (gr_id, gr_subject) VALUES ('community', '커뮤니티');

-- ============================================================
-- 5. 기본 게시판 7개
-- ============================================================
INSERT IGNORE INTO g5_board (bo_table, gr_id, bo_subject, bo_device, bo_list_level, bo_read_level, bo_write_level, bo_reply_level, bo_comment_level, bo_upload_level, bo_download_level, bo_html_level, bo_link_level, bo_read_point, bo_write_point, bo_comment_point, bo_download_point, bo_use_dhtml_editor, bo_page_rows, bo_mobile_page_rows, bo_subject_len, bo_mobile_subject_len, bo_new, bo_hot, bo_image_width, bo_skin, bo_mobile_skin, bo_include_head, bo_include_tail, bo_gallery_cols, bo_gallery_width, bo_gallery_height, bo_mobile_gallery_width, bo_mobile_gallery_height) VALUES
    ('notice',  'community', '공지사항',    'both', 1, 1, 5, 5, 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/morgan/skin/board/basic', 'theme/morgan/skin/board/basic', '_head.php', '_tail.php', 4, 202, 150, 172, 150),
    ('qa',      'community', '문의',       'both', 1, 1, 2, 2, 2, 2, 2, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/morgan/skin/board/basic', 'theme/morgan/skin/board/basic', '_head.php', '_tail.php', 4, 202, 150, 172, 150),
    ('qna',     'community', '질문답변',    'both', 1, 1, 2, 2, 2, 2, 2, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/morgan/skin/board/basic', 'theme/morgan/skin/board/basic', '_head.php', '_tail.php', 4, 202, 150, 172, 150),
    ('log',     'community', '로그',       'both', 1, 1, 2, 2, 2, 2, 2, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/morgan/skin/board/basic', 'theme/morgan/skin/board/basic', '_head.php', '_tail.php', 4, 202, 150, 172, 150),
    ('gallery', 'community', '로그(이미지)', 'both', 1, 1, 2, 2, 2, 2, 2, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/morgan/skin/board/gallery', 'theme/morgan/skin/board/gallery', '_head.php', '_tail.php', 4, 202, 150, 172, 150),
    ('owner',   'community', '오너게시판',   'both', 1, 1, 5, 5, 2, 2, 2, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/morgan/skin/board/basic', 'theme/morgan/skin/board/basic', '_head.php', '_tail.php', 4, 202, 150, 172, 150),
    ('vent',    'community', '앓이란',      'both', 1, 1, 2, 2, 2, 2, 2, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/morgan/skin/board/basic', 'theme/morgan/skin/board/basic', '_head.php', '_tail.php', 4, 202, 150, 172, 150),
    ('roulette','community', '룰렛 로그','both', 1, 1, 2, 2, 2, 2, 2, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/morgan/skin/board/basic', 'theme/morgan/skin/board/basic', '_head.php', '_tail.php', 4, 202, 150, 172, 150);

-- ============================================================
-- 6. mg_config 기본값 (Morgan 설정)
-- ============================================================
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES
    ('color_accent', '#f59f0a'),
    ('color_accent_hover', '#d97706'),
    ('color_bg_primary', '#1e1f22'),
    ('color_bg_secondary', '#2b2d31'),
    ('color_border', '#313338'),
    ('color_text', '#f2f3f5'),
    ('color_button_text', '#ffffff'),
    ('site_logo', ''),
    ('site_background', ''),
    ('character_max', '5'),
    ('character_approval', '1'),
    ('attendance_point', '100'),
    ('attendance_bonus_7', '200'),
    ('attendance_bonus_30', '500'),
    ('roulette_use', '0'),
    ('roulette_cost', '100'),
    ('roulette_daily_limit', '3'),
    ('roulette_cooldown', '0'),
    ('roulette_board', 'roulette'),
    ('roulette_jackpot_pool', '0'),
    ('roulette_transfer_reveal', '0'),
    ('roulette_pending_hours', '24');

-- ============================================================
-- 7. 기본 상점 카테고리
-- ============================================================
INSERT IGNORE INTO mg_shop_category (sc_name, sc_order) VALUES
    ('프로필', 0),
    ('기타', 1);

-- ============================================================
-- 8. FAQ 마스터
-- ============================================================
INSERT IGNORE INTO g5_faq_master (fm_id, fm_subject) VALUES (1, '자주 묻는 질문');
