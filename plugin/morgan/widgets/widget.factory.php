<?php
/**
 * Morgan Edition - Widget Factory
 *
 * 위젯 인스턴스 생성 팩토리
 */

if (!defined('_GNUBOARD_')) exit;

require_once(__DIR__.'/widget.interface.php');

class MG_Widget_Factory {
    private static $widgets = array();
    private static $loaded = false;

    /**
     * 위젯 클래스 파일 로드
     */
    private static function loadWidgets() {
        if (self::$loaded) return;

        $widget_files = array(
            'text' => 'text.widget.php',
            'image' => 'image.widget.php',
            'link_button' => 'link_button.widget.php',
            'latest' => 'latest.widget.php',
            'notice' => 'notice.widget.php',
            'slider' => 'slider.widget.php',
            'editor' => 'editor.widget.php',  // legacy
            'calendar' => 'calendar.widget.php'
        );

        foreach ($widget_files as $type => $file) {
            $path = __DIR__.'/'.$file;
            if (file_exists($path)) {
                require_once($path);
            }
        }

        self::$loaded = true;
    }

    /**
     * 위젯 클래스 등록
     *
     * @param string $type 위젯 타입
     * @param string $class 클래스명
     */
    public static function register($type, $class) {
        self::$widgets[$type] = $class;
    }

    /**
     * 위젯 인스턴스 생성
     *
     * @param string $type 위젯 타입
     * @return MG_Widget_Interface|null
     */
    public static function create($type) {
        self::loadWidgets();

        if (!isset(self::$widgets[$type])) {
            return null;
        }

        $class = self::$widgets[$type];
        return new $class();
    }

    /**
     * 등록된 모든 위젯 타입 반환
     *
     * @return array
     */
    public static function getRegisteredTypes() {
        self::loadWidgets();
        return array_keys(self::$widgets);
    }

    /**
     * 위젯 정보 목록 반환
     *
     * @return array
     */
    public static function getWidgetList() {
        self::loadWidgets();

        $list = array();
        foreach (self::$widgets as $type => $class) {
            $instance = new $class();
            $list[$type] = array(
                'type' => $instance->getType(),
                'name' => $instance->getName(),
                'allowed_cols' => $instance->getAllowedCols()
            );
        }

        return $list;
    }
}
