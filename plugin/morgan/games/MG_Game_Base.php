<?php
/**
 * Morgan Edition - 미니게임 베이스 클래스
 *
 * 공통 기능 구현
 */

if (!defined('_GNUBOARD_')) exit;

require_once __DIR__ . '/MG_Game_Interface.php';

abstract class MG_Game_Base implements MG_Game_Interface {
    protected $config = [];

    /**
     * 설정 로드
     */
    protected function loadConfig(): void {
        global $g5;

        $code = $this->getCode();
        $sql = "SELECT * FROM {$g5['mg_config_table']} WHERE cf_key LIKE 'game_{$code}_%'";
        $result = sql_query($sql);

        while ($row = sql_fetch_array($result)) {
            $key = str_replace("game_{$code}_", '', $row['cf_key']);
            $this->config[$key] = $row['cf_value'];
        }
    }

    /**
     * 설정값 가져오기
     */
    protected function getConfig(string $key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    /**
     * 오늘 이미 플레이했는지 확인
     */
    protected function hasPlayedToday(string $mb_id): bool {
        global $g5;

        $mb_id = sql_real_escape_string($mb_id);
        $today = date('Y-m-d');

        $sql = "SELECT at_id FROM {$g5['mg_attendance_table']}
                WHERE mb_id = '{$mb_id}' AND at_date = '{$today}'";
        $row = sql_fetch($sql);

        return !empty($row);
    }

    /**
     * 출석 기록 저장
     */
    protected function saveAttendance(string $mb_id, int $point, array $gameData = []): void {
        global $g5;

        $mb_id = sql_real_escape_string($mb_id);
        $today = date('Y-m-d');
        $gameType = $this->getCode();
        $gameResult = sql_real_escape_string(json_encode($gameData, JSON_UNESCAPED_UNICODE));
        $ip = sql_real_escape_string($_SERVER['REMOTE_ADDR'] ?? '');

        $sql = "INSERT INTO {$g5['mg_attendance_table']}
                (mb_id, at_date, at_point, at_game_type, at_game_result, at_ip)
                VALUES ('{$mb_id}', '{$today}', {$point}, '{$gameType}', '{$gameResult}', '{$ip}')";
        sql_query($sql);

        // 포인트 지급
        $content = '출석체크 (' . $this->getName() . ')';
        insert_point($mb_id, $point, $content);
    }

    /**
     * 연속 출석 일수 계산
     */
    protected function getStreakDays(string $mb_id): int {
        global $g5;

        $mb_id = sql_real_escape_string($mb_id);
        $today = date('Y-m-d');

        // 최근 7일간 출석 기록 조회
        $sql = "SELECT at_date FROM {$g5['mg_attendance_table']}
                WHERE mb_id = '{$mb_id}'
                AND at_date >= DATE_SUB('{$today}', INTERVAL 7 DAY)
                AND at_date < '{$today}'
                ORDER BY at_date DESC";
        $result = sql_query($sql);

        $streak = 0;
        $checkDate = date('Y-m-d', strtotime('-1 day'));

        while ($row = sql_fetch_array($result)) {
            if ($row['at_date'] == $checkDate) {
                $streak++;
                $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
            } else {
                break;
            }
        }

        return $streak;
    }
}
