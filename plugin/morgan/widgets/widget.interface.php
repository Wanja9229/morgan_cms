<?php
/**
 * Morgan Edition - Widget Interface
 *
 * 위젯 클래스가 구현해야 할 인터페이스
 */

if (!defined('_GNUBOARD_')) exit;

interface MG_Widget_Interface {
    /**
     * 위젯 렌더링
     *
     * @param array $config 위젯 설정
     * @return string HTML
     */
    public function render($config);

    /**
     * 설정 폼 필드 렌더링
     *
     * @param array $config 현재 설정값
     * @return string HTML
     */
    public function renderConfigForm($config);

    /**
     * 위젯 타입 반환
     *
     * @return string
     */
    public function getType();

    /**
     * 위젯 이름 반환
     *
     * @return string
     */
    public function getName();

    /**
     * 허용된 컬럼 너비 목록 반환
     *
     * @return array
     */
    public function getAllowedCols();

    /**
     * 기본 설정값 반환
     *
     * @return array
     */
    public function getDefaultConfig();
}

/**
 * 위젯 기본 클래스
 */
abstract class MG_Widget_Base implements MG_Widget_Interface {
    protected $type = '';
    protected $name = '';
    protected $allowed_cols = array(12);
    protected $default_config = array();

    public function getType() {
        return $this->type;
    }

    public function getName() {
        return $this->name;
    }

    public function getAllowedCols() {
        return $this->allowed_cols;
    }

    public function getDefaultConfig() {
        return $this->default_config;
    }

    /**
     * 스킨 파일 경로 반환
     *
     * @return string
     */
    protected function getSkinPath() {
        return G5_THEME_PATH.'/skin/widget/'.$this->type.'.skin.php';
    }

    /**
     * 스킨 존재 여부 확인
     *
     * @return bool
     */
    protected function skinExists() {
        return file_exists($this->getSkinPath());
    }

    /**
     * 스킨으로 렌더링
     *
     * @param array $data 스킨에 전달할 데이터
     * @return string HTML
     */
    protected function renderSkin($data) {
        if (!$this->skinExists()) {
            return '<div class="card"><p class="text-mg-text-muted">스킨 파일이 없습니다: '.$this->type.'.skin.php</p></div>';
        }

        extract($data);
        ob_start();
        include($this->getSkinPath());
        return ob_get_clean();
    }
}
