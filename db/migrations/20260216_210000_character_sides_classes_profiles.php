<?php
/**
 * 캐릭터 세력(mg_side), 종족(mg_class), 프로필 필드(mg_profile_field) 시드 데이터
 *
 * 입문자 가이드(beginner_guide) 기준:
 * - 세력 3종: 뱀파이어, 라이칸스로프, 헌터
 * - 종족: 뱀파이어 혈통 6종 + 라이칸스로프 형질 4종 + 헌터 회로 5종
 * - 프로필 필드: 기존 6개 + 추가 4개 (소속, 능력, 무기/장비, 기타)
 */

if (!defined('G5_PATH')) {
    die('Access denied');
}

// ──────────────────────────────────────────
// 1. 세력 (mg_side) — 3종
// ──────────────────────────────────────────
$sides = array(
    array(
        'name'  => '뱀파이어',
        'desc'  => '카인의 피를 이은 불사의 존재. 7가지 혈통으로 나뉘며, 각 혈통마다 고유한 권능을 지닌다. 진혈종(퓨어블러드)과 이빨종(턴드)으로 구분되며, 12개의 클랜(패밀리)이 에제이카 주 곳곳에서 세력을 형성하고 있다.',
        'order' => 1
    ),
    array(
        'name'  => '라이칸스로프',
        'desc'  => '야수의 피를 이은 변신 종족. 늑대, 곰, 쥐, 들소, 사자, 올빼미 등 다양한 야수종이 존재하며, 부족(트라이브) 단위로 결속한다. 블랙 후프 산맥을 중심으로 활동하며, 야수화와 하울링 등 고유 능력을 가진다.',
        'order' => 2
    ),
    array(
        'name'  => '헌터',
        'desc'  => '인간 기반의 초자연 사냥꾼. 영지(스티그마타)에 새겨진 연금 회로를 통해 초인적 힘을 발휘한다. WRO 산하 5개 주요 볼트(비스포크, 델타, 파운데이션, 잉크, 볼트 제로)에 소속되어 괴물을 사냥한다.',
        'order' => 3
    ),
);

foreach ($sides as $s) {
    $name = sql_real_escape_string($s['name']);
    $desc = sql_real_escape_string($s['desc']);
    $order = (int)$s['order'];

    // 중복 방지
    $exists = sql_fetch("SELECT side_id FROM {$g5['mg_side_table']} WHERE side_name = '{$name}'");
    if (empty($exists['side_id'])) {
        sql_query("INSERT INTO {$g5['mg_side_table']} (side_name, side_desc, side_order, side_use) VALUES ('{$name}', '{$desc}', {$order}, 1)");
    }
}

// side_id 조회 (class 연결용 설명에 사용)
$vampire_side = sql_fetch("SELECT side_id FROM {$g5['mg_side_table']} WHERE side_name = '뱀파이어'");
$lycan_side   = sql_fetch("SELECT side_id FROM {$g5['mg_side_table']} WHERE side_name = '라이칸스로프'");
$hunter_side  = sql_fetch("SELECT side_id FROM {$g5['mg_side_table']} WHERE side_name = '헌터'");

// ──────────────────────────────────────────
// 2. 종족 (mg_class)
//    뱀파이어 혈통 6종 + 라이칸스로프 형질 4종 + 헌터 회로 5종
// ──────────────────────────────────────────
$classes = array(
    // === 뱀파이어 혈통 (6종, 탐욕 제외 — 신청 불가) ===
    array(
        'name'  => '오만의 혈통',
        'desc'  => '[뱀파이어] 권능: 찬탈(Usurpation). 고유 능력이 없는 대신, 다른 혈통 뱀파이어의 피를 마셔 그 권능을 일시적으로 빼앗을 수 있다.',
        'order' => 1
    ),
    array(
        'name'  => '질투의 혈통',
        'desc'  => '[뱀파이어] 권능: 기만(Deception). 피를 매개로 카인의 마법을 재현한다. 피를 무기로 경화하거나, 피가 묻은 대상을 추적하거나, 피를 매개로 환영을 만든다.',
        'order' => 2
    ),
    array(
        'name'  => '분노의 혈통',
        'desc'  => '[뱀파이어] 권능: 변이(Metamorphosis). 분노를 물질로 변환하여 신체를 변형한다. 손톱을 칼날로, 피부를 갑옷으로, 근육을 무기로 바꿀 수 있다.',
        'order' => 3
    ),
    array(
        'name'  => '색욕의 혈통',
        'desc'  => '[뱀파이어] 권능: 사역(Familiar). 자신의 살점이나 신체 부위를 떼어내 일시적인 사역체를 만든다. 신체를 분리시켜 가장 불사에 가까운 형태가 될 잠재력을 가진다.',
        'order' => 4
    ),
    array(
        'name'  => '식탐의 혈통',
        'desc'  => '[뱀파이어] 권능: 포식(Devouring). 끝없는 갈증에서 비롯된 초예민 감각을 가진다. 자신의 그림자를 물질화하여 물리력을 행사할 수 있다. 흡혈하지 않으면 이성을 빠르게 잃는다.',
        'order' => 5
    ),
    array(
        'name'  => '나태의 혈통',
        'desc'  => '[뱀파이어] 권능: 군체(Swarm). 몸을 박쥐, 쥐, 벌레 등의 떼로 흩어뜨렸다가 재조립할 수 있다. 낮에는 가사 상태에 빠지며, 안전한 은신처 없이는 극도로 취약하다.',
        'order' => 6
    ),

    // === 라이칸스로프 특이 형질 (4종) ===
    array(
        'name'  => '감각의 초월',
        'desc'  => '[라이칸스로프] 형질: Hyper Senses. 총알을 피하는 불릿타임 시야, 벽 너머를 꿰뚫는 반향정위. 뱀파이어의 은신과 헌터의 매복을 감지하는 살아있는 레이더.',
        'order' => 7
    ),
    array(
        'name'  => '육체의 경화',
        'desc'  => '[라이칸스로프] 형질: Bio-Armor. 뼈를 압축한 골갑이 강철보다 단단해지고, 충격을 되튕기는 반응형 표피. 인그레이브 칼날을 맨손으로 받아내며 폭발 속을 걸어다닌다.',
        'order' => 8
    ),
    array(
        'name'  => '근력의 폭발',
        'desc'  => '[라이칸스로프] 형질: Explosive Power. 콘크리트 건물을 두부처럼 부수는 공성 망치급 완력, 3층 높이를 수직 도약하는 포식자 점프. 상식을 초월한 파괴력.',
        'order' => 9
    ),
    array(
        'name'  => '생체 조작',
        'desc'  => '[라이칸스로프] 형질: Bio-Control. 아드레날린 과부하로 고통을 차단하고 죽을 때까지 싸우는 전투 본능, 치명적 독을 합성하여 발톱에 바르는 생체 공장.',
        'order' => 10
    ),

    // === 헌터 연금 회로 (5종) ===
    array(
        'name'  => '적색 회로 (루베도)',
        'desc'  => '[헌터] Rubedo — 파괴의 회로. 열, 폭발, 증폭, 가속 등 직접적 물리 강화. 과사용 시 급성 발열, 화상, 장기 열손상.',
        'order' => 11
    ),
    array(
        'name'  => '녹색 회로 (비리디타스)',
        'desc'  => '[헌터] Viriditas — 재생의 회로. 생명력의 폭발적 증폭. 찢어진 근육과 피부를 재생하고, 몸을 경화하며, 타인의 상처를 치유. 과사용 시 피로, 괴사, 급속 노화.',
        'order' => 12
    ),
    array(
        'name'  => '백색 회로 (알베도)',
        'desc'  => '[헌터] Albedo — 간섭의 회로. 물리법칙에 저항하는 대항장을 생성. 접촉 없이 물리력을 행사. 과사용 시 뇌 부하, 코피, 의식불명.',
        'order' => 13
    ),
    array(
        'name'  => '황색 회로 (시트리니타스)',
        'desc'  => '[헌터] Citrinitas — 감각의 회로. 오감을 변조하여 보이지 않는 정보를 시각화. 야간 투시, 반향 추적, 소나 감지. 과사용 시 정보 과부하, 환각, 환청.',
        'order' => 14
    ),
    array(
        'name'  => '흑색 회로 (니그레도)',
        'desc'  => '[헌터] Nigredo — 각인의 회로. 연금술을 인식하고 영지의 교정/조율/해석을 보조. 전투 능력은 없으나 영지 시술과 수리에 필수적. 손상된 회로 위에 덧입혀 헌터에게 제2의 삶을 부여.',
        'order' => 15
    ),
);

foreach ($classes as $c) {
    $name  = sql_real_escape_string($c['name']);
    $desc  = sql_real_escape_string($c['desc']);
    $order = (int)$c['order'];

    // 중복 방지
    $exists = sql_fetch("SELECT class_id FROM {$g5['mg_class_table']} WHERE class_name = '{$name}'");
    if (empty($exists['class_id'])) {
        sql_query("INSERT INTO {$g5['mg_class_table']} (class_name, class_desc, class_order, class_use) VALUES ('{$name}', '{$desc}', {$order}, 1)");
    }
}

// ──────────────────────────────────────────
// 3. 프로필 필드 (mg_profile_field) — 기존 6개 + 추가 4개
//    기존: age, gender, height, personality, appearance, background
//    추가: affiliation(소속), ability(능력), weapon(무기/장비), extra(기타)
// ──────────────────────────────────────────
$new_fields = array(
    array(
        'code'        => 'affiliation',
        'name'        => '소속',
        'type'        => 'text',
        'options'     => null,
        'placeholder' => '클랜, 부족, 볼트 등 소속 조직명',
        'help'        => '뱀파이어: 패밀리명 / 라이칸스로프: 부족명 / 헌터: 소속 볼트명',
        'required'    => 0,
        'order'       => 7,
        'category'    => '소속/능력'
    ),
    array(
        'code'        => 'ability',
        'name'        => '능력',
        'type'        => 'textarea',
        'options'     => null,
        'placeholder' => '권능, 형질, 연금 회로 등 캐릭터의 능력을 서술해주세요',
        'help'        => '뱀파이어: 혈통 권능의 구체적 활용 / 라이칸스로프: 특이 형질과 야수화 / 헌터: 연금 회로와 인그레이브',
        'required'    => 0,
        'order'       => 8,
        'category'    => '소속/능력'
    ),
    array(
        'code'        => 'weapon',
        'name'        => '무기/장비',
        'type'        => 'textarea',
        'options'     => null,
        'placeholder' => '사용하는 무기나 장비를 서술해주세요',
        'help'        => '헌터의 인그레이브, 뱀파이어의 고유 무기, 라이칸스로프의 변신 형태 등',
        'required'    => 0,
        'order'       => 9,
        'category'    => '소속/능력'
    ),
    array(
        'code'        => 'extra',
        'name'        => '기타',
        'type'        => 'textarea',
        'options'     => null,
        'placeholder' => '취미, 버릇, 말투, 목소리 톤, 테마곡 등',
        'help'        => null,
        'required'    => 0,
        'order'       => 10,
        'category'    => '기타'
    ),
);

foreach ($new_fields as $f) {
    $code = sql_real_escape_string($f['code']);

    // 중복 방지 (pf_code UNIQUE)
    $exists = sql_fetch("SELECT pf_id FROM {$g5['mg_profile_field_table']} WHERE pf_code = '{$code}'");
    if (!empty($exists['pf_id'])) {
        continue;
    }

    $name        = sql_real_escape_string($f['name']);
    $type        = sql_real_escape_string($f['type']);
    $options     = $f['options'] ? "'" . sql_real_escape_string($f['options']) . "'" : 'NULL';
    $placeholder = $f['placeholder'] ? "'" . sql_real_escape_string($f['placeholder']) . "'" : 'NULL';
    $help        = $f['help'] ? "'" . sql_real_escape_string($f['help']) . "'" : 'NULL';
    $required    = (int)$f['required'];
    $order       = (int)$f['order'];
    $category    = $f['category'] ? "'" . sql_real_escape_string($f['category']) . "'" : 'NULL';

    sql_query("INSERT INTO {$g5['mg_profile_field_table']}
        (pf_code, pf_name, pf_type, pf_options, pf_placeholder, pf_help, pf_required, pf_order, pf_category, pf_use)
        VALUES ('{$code}', '{$name}', '{$type}', {$options}, {$placeholder}, {$help}, {$required}, {$order}, {$category}, 1)");
}

// ──────────────────────────────────────────
// 4. 기존 프로필 필드 도움말 보강
//    입문자 가이드 기준으로 help 텍스트 추가
// ──────────────────────────────────────────
$field_updates = array(
    'age' => array(
        'help'        => '뱀파이어·라이칸스로프는 겉보기 나이와 실제 나이를 모두 기재. 뱀파이어는 불사, 라이칸스로프는 약 200년 수명.',
        'placeholder' => '예: 겉보기 25세 / 실제 142세',
    ),
    'appearance' => array(
        'help'        => '키워드 나열보다 서술형을 권장합니다. 최소 3줄 이상의 묘사, 또는 가로 640px 이상의 전신 일러스트.',
        'placeholder' => null,  // keep existing
    ),
    'background' => array(
        'help'        => '캐릭터의 과거, 현재 목표, 달그늘 세계에서의 동기와 행적.',
        'placeholder' => null,  // keep existing
    ),
);

foreach ($field_updates as $code => $updates) {
    $code_esc = sql_real_escape_string($code);
    $set_parts = array();

    if (isset($updates['help']) && $updates['help']) {
        $set_parts[] = "pf_help = '" . sql_real_escape_string($updates['help']) . "'";
    }
    if (isset($updates['placeholder']) && $updates['placeholder']) {
        $set_parts[] = "pf_placeholder = '" . sql_real_escape_string($updates['placeholder']) . "'";
    }

    if (!empty($set_parts)) {
        sql_query("UPDATE {$g5['mg_profile_field_table']} SET " . implode(', ', $set_parts) . " WHERE pf_code = '{$code_esc}'");
    }
}
