<?php
/**
 * Dice-Box 3D 공용 로더
 * @3d-dice/dice-box-threejs v0.0.12 자체 호스팅
 *
 * 사용법:
 *   include_once(G5_PATH.'/plugin/dice-box/dice-box-loader.php');
 *   mg_dice_box_scripts();
 *
 * JS API:
 *   MorganDice.init('#container')
 *   MorganDice.roll('5d6@3,2,6,1,4')  → 강제 값 지정 가능
 *   MorganDice.clear()
 *   MorganDice.hide() / .show()
 */

if (!defined('_GNUBOARD_')) exit;

function mg_dice_box_scripts($options = array()) {
    $base_url = G5_URL . '/plugin/dice-box/dist';
    $asset_path = parse_url(G5_URL, PHP_URL_PATH) . '/plugin/dice-box/dist/';
    $container = isset($options['container']) ? $options['container'] : '#dice-box-container';
?>
<style>
.dice-box-overlay {
    position: relative;
    width: 100%;
    height: 280px;
    border-radius: 0.75rem;
    overflow: hidden;
    background: transparent;
}
.dice-box-overlay canvas {
    width: 100% !important;
    height: 100% !important;
}
@media (max-width: 640px) {
    .dice-box-overlay { height: 220px; }
}
</style>
<script type="module">
import DiceBox from '<?php echo $base_url; ?>/dice-box-threejs.es.js';

window.MorganDice = {
    _box: null,
    _ready: false,
    _initPromise: null,
    _container: null,

    /**
     * 초기화
     * @param {string} selector  컨테이너 CSS selector
     * @returns {Promise}
     */
    init(selector) {
        if (this._initPromise) return this._initPromise;

        this._container = selector || '<?php echo $container; ?>';
        const el = document.querySelector(this._container);
        if (!el) {
            console.error('[MorganDice] Container not found:', this._container);
            return Promise.reject('Container not found');
        }

        this._box = new DiceBox(this._container, {
            assetPath: '<?php echo $asset_path; ?>',
            sounds: false,
            theme_customColorset: {
                name: 'morgan_white',
                category: 'Custom',
                foreground: '#222222',
                background: '#f0f0f0',
                outline: '#cccccc',
                texture: 'none',
                material: 'plastic',
            },
            theme_surface: 'green-felt',
            gravity_multiplier: 400,
            light_intensity: 1.0,
            shadows: true,
            baseScale: 100,
            strength: 1,
        });

        this._initPromise = this._box.initialize().then(() => {
            this._ready = true;
            console.log('[MorganDice] Ready (threejs)');
            window.dispatchEvent(new CustomEvent('MorganDiceReady'));
        }).catch((err) => {
            console.error('[MorganDice] Init failed:', err);
        });

        return this._initPromise;
    },

    /**
     * 주사위 굴리기 (값 지정 가능)
     * @param {string} notation  "5d6" 또는 "5d6@3,2,6,1,4" (강제 값)
     * @returns {Promise<Array>}
     */
    roll(notation) {
        if (!this._ready || !this._box) return Promise.resolve([]);
        return this._box.roll(notation);
    },

    /**
     * 특정 주사위만 리롤 (유지 주사위는 제자리)
     * @param {number[]} indices  리롤할 주사위 인덱스 배열 [0,2,4]
     * @param {number[]} values   강제 결과값 배열 [3,6,1]
     * @returns {Promise}
     */
    rerollForced(indices, values) {
        if (!this._ready || !this._box || !indices.length) return Promise.resolve([]);
        // last_time 리셋: 안 하면 경과시간만큼 물리를 한 프레임에 fast-forward → 모션 안 보임
        this._box.last_time = 0;
        return this._box.reroll(indices).then((results) => {
            indices.forEach((dieIdx, i) => {
                const die = this._box.diceList[dieIdx];
                if (die && values[i] !== undefined) {
                    this._box.swapDiceFace(die, values[i]);
                }
            });
            this._box.renderer.render(this._box.scene, this._box.camera);
            return results;
        });
    },

    /**
     * 주사위 화면 정리
     */
    clear() {
        if (this._ready && this._box) {
            this._box.clearDice();
        }
    },

    /**
     * 오버레이 숨기기/표시
     */
    hide() {
        const el = document.querySelector(this._container);
        if (el) el.style.display = 'none';
    },
    show() {
        const el = document.querySelector(this._container);
        if (el) el.style.display = '';
    },

    isReady() { return this._ready; }
};

// 자동 초기화
const autoContainer = document.querySelector('<?php echo $container; ?>');
if (autoContainer) {
    window.MorganDice.init('<?php echo $container; ?>');
}

window.MorganDiceLoaded = true;
window.dispatchEvent(new CustomEvent('MorganDiceLoaded'));
</script>
<?php
}
