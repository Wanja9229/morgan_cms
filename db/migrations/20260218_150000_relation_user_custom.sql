-- 관계 시스템 리팩토링: 아이콘 프리셋 → 유저 자유 설정
-- ri_id를 선택적으로 변경 (새 관계는 0)
ALTER TABLE mg_relation MODIFY ri_id int DEFAULT 0;

-- 기존 관계의 ri_icon/ri_color를 cr_icon_a/cr_color로 이관 (빈 값만 채움)
UPDATE mg_relation r
  JOIN mg_relation_icon ri ON r.ri_id = ri.ri_id
  SET r.cr_icon_a = COALESCE(NULLIF(r.cr_icon_a, ''), ri.ri_icon),
      r.cr_icon_b = COALESCE(NULLIF(r.cr_icon_b, ''), ri.ri_icon),
      r.cr_color  = COALESCE(NULLIF(r.cr_color, ''), ri.ri_color)
  WHERE r.ri_id > 0;

-- 테스트 캐릭터 시드 (ch_id 21, 22)
INSERT IGNORE INTO mg_character (ch_id, mb_id, ch_name, ch_state, ch_type, ch_main, ch_thumb, ch_datetime)
VALUES (21, 'admin', '페트로넬라', 'approved', 'main', 1, 'admin/head_6991e8743bee6.png', NOW());

INSERT IGNORE INTO mg_character (ch_id, mb_id, ch_name, ch_state, ch_type, ch_main, ch_thumb, ch_datetime)
VALUES (22, 'test', '아니니', 'approved', 'main', 1, 'test/head_6991ebfdab849.png', NOW());

-- 테스트 데이터: 관계 샘플 2건
INSERT IGNORE INTO mg_relation (ch_id_a, ch_id_b, ch_id_from, ri_id, cr_label_a, cr_label_b, cr_icon_a, cr_icon_b, cr_color, cr_status, cr_datetime, cr_accept_datetime)
VALUES (21, 22, 21, 0, '동료', '동료', '🤝', '🤝', '#3498db', 'active', NOW(), NOW());

INSERT IGNORE INTO mg_relation (ch_id_a, ch_id_b, ch_id_from, ri_id, cr_label_a, cr_label_b, cr_icon_a, cr_icon_b, cr_color, cr_status, cr_datetime)
VALUES (22, 21, 22, 0, '라이벌', '', '🗡️', '', '#e74c3c', 'pending', NOW());
