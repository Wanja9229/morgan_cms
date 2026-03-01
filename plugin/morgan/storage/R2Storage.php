<?php
/**
 * MG_R2Storage - Cloudflare R2 드라이버
 *
 * S3 호환 API를 사용하여 Cloudflare R2에 파일을 저장/삭제/조회.
 * curl + MG_S3Signature 기반, 외부 라이브러리 없음.
 */

if (!defined('_GNUBOARD_')) exit;

require_once __DIR__ . '/MG_Storage.php';
require_once __DIR__ . '/S3Signature.php';

class MG_R2Storage implements MG_StorageInterface
{
    private $endpoint;
    private $bucket;
    private $publicUrl;
    private $signer;
    private $keyPrefix;

    public function __construct()
    {
        $accountId  = mg_config('mg_r2_account_id', '');
        $accessKey  = mg_config('mg_r2_access_key_id', '');
        $secretKey  = mg_config('mg_r2_secret_access_key', '');
        $this->bucket    = mg_config('mg_r2_bucket_name', '');
        $this->publicUrl = rtrim(mg_config('mg_r2_public_url', ''), '/');

        // 엔드포인트: 설정값 우선, 없으면 account_id로 자동 생성
        $endpoint = mg_config('mg_r2_endpoint', '');
        if ($endpoint) {
            $this->endpoint = rtrim($endpoint, '/');
        } else {
            $this->endpoint = "https://{$accountId}.r2.cloudflarestorage.com";
        }

        $this->signer = new MG_S3Signature($accessKey, $secretKey, 'auto', 's3');

        // [MT-1] 멀티테넌트: R2 키에 테넌트 프리픽스 추가
        if (defined('MG_MULTITENANT_ENABLED') && MG_MULTITENANT_ENABLED
            && defined('MG_TENANT_ID') && MG_TENANT_ID > 0) {
            $this->keyPrefix = MG_TENANT_ID . '/';
        } else {
            $this->keyPrefix = '';
        }
    }

    /**
     * 파일 저장 (PutObject)
     *
     * @param string $path    data/ 이하 상대경로
     * @param string $source  업로드 임시파일 경로 또는 바이너리 데이터
     * @param array  $options ['is_upload'=>bool, 'content_type'=>string]
     * @return bool
     */
    public function put($path, $source, $options = [])
    {
        // 파일 내용 읽기
        if (is_file($source)) {
            $body = file_get_contents($source);
            if ($body === false) return false;
        } else {
            // 바이너리 데이터 직접 전달
            $body = $source;
        }

        // Content-Type 결정
        $contentType = '';
        if (!empty($options['content_type'])) {
            $contentType = $options['content_type'];
        } else {
            $contentType = $this->guessContentType($path);
        }

        $url = $this->objectUrl($path);
        $headers = [];
        if ($contentType) {
            $headers['Content-Type'] = $contentType;
        }

        $signedHeaders = $this->signer->signRequest('PUT', $url, $headers, $body);

        $result = $this->curlRequest('PUT', $url, $signedHeaders, $body);

        return ($result['http_code'] >= 200 && $result['http_code'] < 300);
    }

    /**
     * 파일 삭제 (DeleteObject)
     */
    public function delete($path)
    {
        $url = $this->objectUrl($path);
        $signedHeaders = $this->signer->signRequest('DELETE', $url);

        $result = $this->curlRequest('DELETE', $url, $signedHeaders);

        // 204 No Content = 성공, 404 = 이미 없음
        return ($result['http_code'] === 204 || $result['http_code'] === 404);
    }

    /**
     * 파일 존재 확인 (HeadObject)
     */
    public function exists($path)
    {
        $url = $this->objectUrl($path);
        $signedHeaders = $this->signer->signRequest('HEAD', $url);

        $result = $this->curlRequest('HEAD', $url, $signedHeaders);

        return ($result['http_code'] === 200);
    }

    /**
     * 공개 URL 반환
     */
    public function url($path)
    {
        if ($this->publicUrl) {
            return $this->publicUrl . '/' . $this->keyPrefix . $path;
        }
        // public URL 미설정 시 R2 endpoint URL (비공개)
        return $this->objectUrl($path);
    }

    /**
     * 디렉토리 확보 (R2에서는 no-op, 오브젝트 스토리지는 디렉토리 개념 없음)
     */
    public function ensureDir($dir_path)
    {
        return true;
    }

    /**
     * 버킷 연결 테스트 (HeadBucket)
     *
     * @return array ['success'=>bool, 'message'=>string, 'http_code'=>int]
     */
    public function testConnection()
    {
        $url = $this->endpoint . '/' . $this->bucket;
        $signedHeaders = $this->signer->signRequest('HEAD', $url);

        $result = $this->curlRequest('HEAD', $url, $signedHeaders);

        if ($result['http_code'] === 200) {
            return ['success' => true, 'message' => '연결 성공', 'http_code' => 200];
        }

        if ($result['http_code'] === 0) {
            return [
                'success' => false,
                'message' => '서버에 연결할 수 없습니다: ' . ($result['error'] ?? ''),
                'http_code' => 0
            ];
        }

        return [
            'success' => false,
            'message' => "HTTP {$result['http_code']} 오류",
            'http_code' => $result['http_code']
        ];
    }

    /**
     * 오브젝트 전체 URL
     */
    private function objectUrl($path)
    {
        return $this->endpoint . '/' . $this->bucket . '/' . $this->keyPrefix . $path;
    }

    /**
     * curl 요청 실행
     *
     * @param string $method  HTTP 메서드
     * @param string $url     전체 URL
     * @param array  $headers 서명된 헤더 배열
     * @param string $body    요청 바디
     * @return array ['http_code'=>int, 'body'=>string, 'error'=>string]
     */
    private function curlRequest($method, $url, $headers, $body = '')
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_NOBODY         => ($method === 'HEAD'),
        ]);

        // 헤더 설정
        $curlHeaders = [];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = "{$k}: {$v}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

        // 바디 설정 (PUT)
        if ($method === 'PUT' && $body !== '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);

        curl_close($ch);

        return [
            'http_code' => (int)$httpCode,
            'body'      => $response ?: '',
            'error'     => $error,
        ];
    }

    /**
     * 파일 확장자로 Content-Type 추정
     */
    private function guessContentType($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $map = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/x-icon',
        ];

        return isset($map[$ext]) ? $map[$ext] : 'application/octet-stream';
    }
}
