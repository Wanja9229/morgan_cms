-- 인장 배경 아이템 설명 업데이트 (Vanta.js → CSS 애니메이션)
-- bg_id 필드는 유지하되, 렌더링은 mg_seal_bg_effect_css()에서 CSS로 처리

UPDATE mg_shop_item SET si_desc = '인장에 안개처럼 흐르는 그라디언트 효과'
WHERE si_type = 'seal_bg' AND si_effect LIKE '%"fog"%';

UPDATE mg_shop_item SET si_desc = '인장에 겹치는 물결 패턴 효과'
WHERE si_type = 'seal_bg' AND si_effect LIKE '%"waves"%';

UPDATE mg_shop_item SET si_desc = '인장에 맥동하는 유기적 셀 효과'
WHERE si_type = 'seal_bg' AND si_effect LIKE '%"cells"%';

UPDATE mg_shop_item SET si_desc = '인장에 빛나는 도트 그리드 효과'
WHERE si_type = 'seal_bg' AND si_effect LIKE '%"net"%';

UPDATE mg_shop_item SET si_desc = '인장에 중심에서 퍼지는 파문 효과'
WHERE si_type = 'seal_bg' AND si_effect LIKE '%"ripple"%';
