<?php
/**
 * MG_LocalStorage - 로컬 파일시스템 드라이버
 *
 * 기존 move_uploaded_file() + @unlink() 패턴을 그대로 래핑.
 * 기본 드라이버이며, R2를 설정하지 않으면 이 드라이버가 사용된다.
 */

if (!defined('_GNUBOARD_')) exit;

require_once __DIR__ . '/MG_Storage.php';

class MG_LocalStorage implements MG_StorageInterface
{
    private $basePath;
    private $baseUrl;

    public function __construct()
    {
        // [MT-1] 멀티테넌트: 테넌트별 데이터 경로 분기
        if (defined('MG_MULTITENANT_ENABLED') && MG_MULTITENANT_ENABLED
            && defined('MG_TENANT_ID') && MG_TENANT_ID > 0) {
            $this->basePath = G5_DATA_PATH . '/tenants/' . MG_TENANT_ID;
            $this->baseUrl  = G5_DATA_URL  . '/tenants/' . MG_TENANT_ID;
        } else {
            $this->basePath = G5_DATA_PATH;
            $this->baseUrl  = G5_DATA_URL;
        }

        if (!is_dir($this->basePath)) {
            @mkdir($this->basePath, 0755, true);
        }
    }

    /**
     * 파일 저장
     *
     * @param string $path    data/ 이하 상대경로
     * @param string $source  업로드 임시파일 또는 로컬 파일 경로
     * @param array  $options ['is_upload'=>true] 시 move_uploaded_file 사용
     * @return bool
     */
    public function put($path, $source, $options = [])
    {
        $fullPath = $this->basePath . '/' . $path;
        $dir = dirname($fullPath);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $isUpload = !empty($options['is_upload']);

        if ($isUpload) {
            $result = move_uploaded_file($source, $fullPath);
        } else {
            if (is_file($source)) {
                $result = copy($source, $fullPath);
            } else {
                // 바이너리 데이터 직접 쓰기
                $result = (file_put_contents($fullPath, $source) !== false);
            }
        }

        if ($result) {
            @chmod($fullPath, 0644);
        }

        return $result;
    }

    /**
     * 파일 삭제
     */
    public function delete($path)
    {
        $fullPath = $this->basePath . '/' . $path;
        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }
        return true; // 이미 없으면 성공
    }

    /**
     * 파일 존재 확인
     */
    public function exists($path)
    {
        return file_exists($this->basePath . '/' . $path);
    }

    /**
     * 공개 URL 반환
     */
    public function url($path)
    {
        return $this->baseUrl . '/' . $path;
    }

    /**
     * 디렉토리 확보
     */
    public function ensureDir($dir_path)
    {
        $fullDir = $this->basePath . '/' . $dir_path;
        if (!is_dir($fullDir)) {
            return @mkdir($fullDir, 0755, true);
        }
        return true;
    }

    /**
     * 로컬 절대경로 반환 (로컬 드라이버 전용, 리사이즈 등에 사용)
     */
    public function getFullPath($path)
    {
        return $this->basePath . '/' . $path;
    }
}
