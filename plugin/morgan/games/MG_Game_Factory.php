<?php
/**
 * Morgan Edition - 미니게임 팩토리
 *
 * 활성화된 게임 인스턴스를 반환
 */

if (!defined('_GNUBOARD_')) exit;

require_once __DIR__ . '/MG_Game_Interface.php';
require_once __DIR__ . '/MG_Game_Base.php';
require_once __DIR__ . '/MG_Game_Dice.php';
require_once __DIR__ . '/MG_Game_Fortune.php';
require_once __DIR__ . '/MG_Game_Lottery.php';

class MG_Game_Factory {
    private static $games = [
        'dice'    => MG_Game_Dice::class,
        'fortune' => MG_Game_Fortune::class,
        'lottery' => MG_Game_Lottery::class,
    ];

    /**
     * 현재 활성화된 게임 반환
     * @return MG_Game_Interface
     */
    public static function getActiveGame(): MG_Game_Interface {
        $activeCode = self::getActiveGameCode();
        return self::createGame($activeCode);
    }

    /**
     * 활성 게임 코드 조회
     * @return string
     */
    public static function getActiveGameCode(): string {
        $code = mg_config('attendance_game', 'dice');
        return array_key_exists($code, self::$games) ? $code : 'dice';
    }

    /**
     * 특정 게임 인스턴스 생성
     * @param string $code
     * @return MG_Game_Interface
     */
    public static function createGame(string $code): MG_Game_Interface {
        if (!array_key_exists($code, self::$games)) {
            $code = 'dice'; // 기본값
        }

        $className = self::$games[$code];
        return new $className();
    }

    /**
     * 사용 가능한 모든 게임 목록
     * @return array [code => name]
     */
    public static function getAvailableGames(): array {
        $result = [];
        foreach (self::$games as $code => $className) {
            $game = new $className();
            $result[$code] = [
                'code' => $code,
                'name' => $game->getName(),
                'description' => $game->getDescription()
            ];
        }
        return $result;
    }

    /**
     * 새 게임 등록 (플러그인 확장용)
     * @param string $code
     * @param string $className
     */
    public static function registerGame(string $code, string $className): void {
        if (class_exists($className) && in_array(MG_Game_Interface::class, class_implements($className))) {
            self::$games[$code] = $className;
        }
    }
}
