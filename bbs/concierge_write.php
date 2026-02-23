<?php
/**
 * Morgan Edition - 의뢰 등록 → concierge.php 모달로 리다이렉트
 * 리디자인으로 모달 방식 전환됨
 */
include_once('./_common.php');

header('Location: ' . G5_BBS_URL . '/concierge.php?tab=market&write=1');
exit;
