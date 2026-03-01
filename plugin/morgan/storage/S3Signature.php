<?php
/**
 * S3Signature - AWS Signature V4 순수 PHP 구현
 *
 * Cloudflare R2 (S3 호환 API) 요청 서명에 사용.
 * 외부 라이브러리 없이 PHP 내장 hash_hmac/hash 함수만 사용.
 */

if (!defined('_GNUBOARD_')) exit;

class MG_S3Signature
{
    private $accessKeyId;
    private $secretAccessKey;
    private $region;
    private $service;

    /**
     * @param string $accessKeyId
     * @param string $secretAccessKey
     * @param string $region   R2는 'auto'
     * @param string $service  's3'
     */
    public function __construct($accessKeyId, $secretAccessKey, $region = 'auto', $service = 's3')
    {
        $this->accessKeyId     = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->region          = $region;
        $this->service         = $service;
    }

    /**
     * 서명된 헤더 생성
     *
     * @param string $method   HTTP 메서드 (GET, PUT, DELETE, HEAD)
     * @param string $url      전체 URL
     * @param array  $headers  추가 헤더 ['Content-Type'=>'image/jpeg']
     * @param string $payload  요청 바디 (PUT 시 파일 데이터)
     * @return array 서명이 포함된 전체 헤더 배열
     */
    public function signRequest($method, $url, $headers = [], $payload = '')
    {
        $parsed = parse_url($url);
        $host   = $parsed['host'];
        $path   = isset($parsed['path']) ? $parsed['path'] : '/';
        $query  = isset($parsed['query']) ? $parsed['query'] : '';

        $timestamp = gmdate('Ymd\THis\Z');
        $datestamp = gmdate('Ymd');

        // 페이로드 해시
        $payloadHash = hash('sha256', $payload);

        // 기본 헤더
        $headers['Host']                 = $host;
        $headers['x-amz-date']           = $timestamp;
        $headers['x-amz-content-sha256'] = $payloadHash;

        // 서명할 헤더 정렬
        $signedHeaderNames = array_map('strtolower', array_keys($headers));
        sort($signedHeaderNames);
        $signedHeaders = implode(';', $signedHeaderNames);

        // Canonical Headers
        $canonicalHeaders = '';
        $lowerHeaders = [];
        foreach ($headers as $k => $v) {
            $lowerHeaders[strtolower($k)] = trim($v);
        }
        ksort($lowerHeaders);
        foreach ($lowerHeaders as $k => $v) {
            $canonicalHeaders .= $k . ':' . $v . "\n";
        }

        // Canonical Query String
        $canonicalQueryString = '';
        if ($query) {
            parse_str($query, $queryParams);
            ksort($queryParams);
            $canonicalQueryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        }

        // Canonical Request
        $canonicalRequest = implode("\n", [
            strtoupper($method),
            $this->uriEncodePath($path),
            $canonicalQueryString,
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash,
        ]);

        // Credential Scope
        $credentialScope = $datestamp . '/' . $this->region . '/' . $this->service . '/aws4_request';

        // String to Sign
        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $timestamp,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        // Signing Key
        $signingKey = $this->getSigningKey($datestamp);

        // Signature
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        // Authorization Header
        $headers['Authorization'] = sprintf(
            'AWS4-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            $this->accessKeyId,
            $credentialScope,
            $signedHeaders,
            $signature
        );

        return $headers;
    }

    /**
     * Signing Key 생성
     */
    private function getSigningKey($datestamp)
    {
        $kDate    = hash_hmac('sha256', $datestamp, 'AWS4' . $this->secretAccessKey, true);
        $kRegion  = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', $this->service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        return $kSigning;
    }

    /**
     * URI 경로 인코딩 (S3 사양)
     */
    private function uriEncodePath($path)
    {
        $segments = explode('/', $path);
        $encoded = [];
        foreach ($segments as $seg) {
            $encoded[] = rawurlencode($seg);
        }
        return implode('/', $encoded);
    }
}
