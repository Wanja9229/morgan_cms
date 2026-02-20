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
     * 특정 주사위만 리롤 (유지 주사위는 제자리, 마지막 0.3초에 목표 면으로 lerp)
     * @param {number[]} indices  리롤할 주사위 인덱스 배열 [0,2,4]
     * @param {number[]} values   강제 결과값 배열 [3,6,1]
     * @returns {Promise}
     */
    rerollForced(indices, values) {
        if (!this._ready || !this._box || !indices.length) return Promise.resolve([]);
        this._box.last_time = 0;

        const box = this._box;
        const LERP_MS = 500;
        const VEL_THRESH = 350;
        const ANG_THRESH = 8;
        const SETTLE_FRAMES = 3; // 연속 저속 프레임 수 (바운스 정점 필터링)
        const MIN_WAIT = 500;
        const t0 = performance.now();
        const lerpMap = new Map();
        const lowVelCount = {};

        // 목표 face의 quaternion 계산
        const computeTargetQuat = (die, targetVal) => {
            const Vec3 = die.position.constructor;
            const Quat = die.quaternion.constructor;
            const factory = box.DiceFactory.get(die.notation.type);
            const vi = factory.values.indexOf(targetVal);
            if (vi < 0) return null;

            const targetMatIdx = vi + 2;
            const groups = die.geometry.groups;
            const norms = die.geometry.getAttribute('normal').array;
            let localN = null;

            for (let g = 0; g < groups.length; g++) {
                if (groups[g].materialIndex === targetMatIdx) {
                    const off = g * 9;
                    localN = new Vec3(norms[off], norms[off+1], norms[off+2]).normalize();
                    break;
                }
            }
            if (!localN) return null;

            // body quaternion → Three.js Quaternion으로 변환
            const bq = new Quat(
                die.body.quaternion.x, die.body.quaternion.y,
                die.body.quaternion.z, die.body.quaternion.w
            );
            const worldN = localN.clone().applyQuaternion(bq);
            const up = new Vec3(0, 0, 1);
            const angle = worldN.angleTo(up);

            if (angle < 0.02) return bq; // 이미 목표 면이 위

            const axis = new Vec3().crossVectors(worldN, up);
            if (axis.lengthSq() < 1e-6) axis.set(1, 0, 0);
            axis.normalize();

            const corr = new Quat().setFromAxisAngle(axis, angle);
            return corr.multiply(bq);
        };

        // 매 프레임 모니터: 속도가 충분히 낮아지면 lerp 시작
        const monitor = () => {
            const elapsed = performance.now() - t0;
            let active = false;

            indices.forEach((dieIdx, i) => {
                const die = box.diceList[dieIdx];
                if (!die || !die.body) return;

                if (!lerpMap.has(dieIdx)) {
                    if (elapsed < MIN_WAIT) { active = true; return; }
                    const v = die.body.velocity;
                    const av = die.body.angularVelocity;
                    const spd = Math.sqrt(v.x*v.x + v.y*v.y + v.z*v.z);
                    const aspd = Math.sqrt(av.x*av.x + av.y*av.y + av.z*av.z);

                    if (spd < VEL_THRESH && aspd < ANG_THRESH) {
                        lowVelCount[dieIdx] = (lowVelCount[dieIdx] || 0) + 1;
                        if (lowVelCount[dieIdx] >= SETTLE_FRAMES) {
                            const tq = computeTargetQuat(die, values[i]);
                            if (tq) {
                                const Quat = die.quaternion.constructor;
                                const startQ = new Quat(
                                    die.body.quaternion.x, die.body.quaternion.y,
                                    die.body.quaternion.z, die.body.quaternion.w
                                );
                                die.body.velocity.set(0, 0, 0);
                                die.body.angularVelocity.set(0, 0, 0);
                                lerpMap.set(dieIdx, { startQ, targetQ: tq, t0: performance.now(), done: false });
                            }
                        }
                    } else {
                        lowVelCount[dieIdx] = 0;
                    }
                    active = true;
                } else {
                    const s = lerpMap.get(dieIdx);
                    if (!s.done) {
                        const t = Math.min((performance.now() - s.t0) / LERP_MS, 1);
                        const e = t * t * (3 - 2 * t); // smoothstep

                        const lq = s.startQ.clone().slerp(s.targetQ, e);
                        die.quaternion.copy(lq);
                        die.body.quaternion.x = lq.x;
                        die.body.quaternion.y = lq.y;
                        die.body.quaternion.z = lq.z;
                        die.body.quaternion.w = lq.w;

                        if (t >= 1) s.done = true;
                        else active = true;
                    }
                }
            });

            box.renderer.render(box.scene, box.camera);
            if (active) requestAnimationFrame(monitor);
        };

        requestAnimationFrame(monitor);
        return box.reroll(indices);
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
