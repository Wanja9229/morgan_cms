<?php
/**
 * Morgan Edition - 미니게임 인터페이스
 *
 * 모든 출석 미니게임은 이 인터페이스를 구현해야 함
 */

if (!defined('_GNUBOARD_')) exit;

interface MG_Game_Interface {
    /**
     * 게임 코드 반환
     * @return string 'dice', 'fortune', 'lottery' 등
     */
    public function getCode(): string;

    /**
     * 게임 이름 반환
     * @return string '주사위', '운세뽑기' 등
     */
    public function getName(): string;

    /**
     * 게임 설명 반환
     * @return string
     */
    public function getDescription(): string;

    /**
     * 게임 실행 (결과 반환)
     * @param string $mb_id 회원 ID
     * @return array ['success' => bool, 'point' => int, 'message' => string, 'data' => array]
     */
    public function play(string $mb_id): array;

    /**
     * 게임 결과 HTML 렌더링 (애니메이션 포함)
     * @param array $result play() 결과
     * @return string HTML
     */
    public function renderResult(array $result): string;

    /**
     * 게임 UI HTML 렌더링 (버튼 등)
     * @return string HTML
     */
    public function renderUI(): string;

    /**
     * 게임에 필요한 JS 반환
     * @return string JavaScript 코드
     */
    public function getJavaScript(): string;

    /**
     * 게임에 필요한 CSS 반환
     * @return string CSS 코드
     */
    public function getCSS(): string;
}
