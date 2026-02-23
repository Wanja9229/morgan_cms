<?php
/**
 * Morgan Edition - 의뢰 상세 → concierge.php 리다이렉트
 * 리디자인으로 인라인 카드 방식 전환됨
 */
include_once('./_common.php');

$cc_id = isset($_GET['cc_id']) ? (int)$_GET['cc_id'] : 0;
$url = G5_BBS_URL . '/concierge.php?tab=market' . ($cc_id ? '&cc_id=' . $cc_id : '');
header('Location: ' . $url);
exit;
