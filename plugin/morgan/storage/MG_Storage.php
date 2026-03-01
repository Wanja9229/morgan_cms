<?php
/**
 * MG_Storage - 스토리지 추상화 인터페이스 + 팩토리
 *
 * 로컬 파일시스템과 Cloudflare R2를 통합하는 추상화 레이어.
 * mg_config('mg_storage_driver') 값에 따라 드라이버를 선택한다.
 */

if (!defined('_GNUBOARD_')) exit;

/**
 * 스토리지 인터페이스
 *
 * 모든 $path 인자는 data/ 이하의 상대경로를 사용한다.
 * 예: 'character/admin/head_xxx.jpg', 'emoticon/1/icon.png'
 */
interface MG_StorageInterface
{
    /**
     * 파일 저장
     *
     * @param string $path    저장 경로 (data/ 이하 상대경로)
     * @param string $source  업로드 임시파일 경로 또는 로컬 파일 경로
     * @param array  $options ['is_upload'=>bool, 'content_type'=>string]
     * @return bool
     */
    public function put($path, $source, $options = []);

    /**
     * 파일 삭제
     *
     * @param string $path
     * @return bool
     */
    public function delete($path);

    /**
     * 파일 존재 확인
     *
     * @param string $path
     * @return bool
     */
    public function exists($path);

    /**
     * 공개 URL 반환
     *
     * @param string $path
     * @return string
     */
    public function url($path);

    /**
     * 디렉토리 확보 (로컬 전용, R2에서는 no-op)
     *
     * @param string $dir_path  디렉토리 경로 (data/ 이하)
     * @return bool
     */
    public function ensureDir($dir_path);
}

/**
 * 스토리지 팩토리 (싱글턴)
 */
class MG_Storage
{
    private static $instance = null;

    /**
     * 드라이버 인스턴스 반환
     *
     * @return MG_StorageInterface
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            $driver = function_exists('mg_config')
                ? mg_config('mg_storage_driver', 'local')
                : 'local';

            switch ($driver) {
                case 'r2':
                    require_once __DIR__ . '/R2Storage.php';
                    self::$instance = new MG_R2Storage();
                    break;
                default:
                    require_once __DIR__ . '/LocalStorage.php';
                    self::$instance = new MG_LocalStorage();
                    break;
            }
        }
        return self::$instance;
    }

    /**
     * 인스턴스 리셋 (테스트/멀티테넌트 전환 시)
     */
    public static function reset()
    {
        self::$instance = null;
    }
}
