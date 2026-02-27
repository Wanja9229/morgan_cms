"""관리자 가이드용 스크린샷 캡처 (하이라이트 포함)"""
from playwright.sync_api import sync_playwright
import os, time

BASE = "http://localhost:8080"
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pdf_images")
os.makedirs(OUT, exist_ok=True)

# 하이라이트 CSS 주입
HIGHLIGHT_CSS = """
.mg-hl { outline: 3px solid #f59f0a !important; outline-offset: 3px !important; position: relative !important; z-index: 10 !important; }
.mg-hl-box { border: 3px solid #f59f0a !important; border-radius: 8px !important; position: relative !important; }
.mg-badge { position: absolute; top: -14px; left: -14px; width: 28px; height: 28px; background: #f59f0a; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; z-index: 9999; pointer-events: none; }
.mg-callout { position: absolute; top: -36px; left: 50%; transform: translateX(-50%); background: #f59f0a; color: white; padding: 3px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; white-space: nowrap; z-index: 9999; pointer-events: none; }
.mg-callout::after { content: ''; position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 6px solid #f59f0a; }
"""

def add_highlight(page, selector, number=None, callout=None):
    """특정 요소에 하이라이트 + 번호 뱃지 + 콜아웃 추가"""
    page.evaluate(f"""(function(){{
        var els = document.querySelectorAll('{selector}');
        if (!els.length) return;
        var el = els[0];
        el.classList.add('mg-hl');
        el.style.position = 'relative';
        {f'''
        var badge = document.createElement('div');
        badge.className = 'mg-badge';
        badge.textContent = '{number}';
        el.appendChild(badge);
        ''' if number else ''}
        {f'''
        var co = document.createElement('div');
        co.className = 'mg-callout';
        co.textContent = '{callout}';
        el.appendChild(co);
        ''' if callout else ''}
    }})()""")

def capture(page, name, full=True):
    path = os.path.join(OUT, f"{name}.png")
    page.screenshot(path=path, full_page=full)
    print(f"  -> {name}.png")

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    ctx = browser.new_context(viewport={"width": 1440, "height": 900}, locale="ko-KR")
    page = ctx.new_page()

    # 로그인
    page.goto(f"{BASE}/bbs/login.php")
    page.wait_for_load_state("networkidle")
    page.fill('input[name="mb_id"]', "admin")
    page.fill('input[name="mb_password"]', "admin")
    page.click('button[type="submit"], input[type="submit"]')
    page.wait_for_load_state("networkidle")
    time.sleep(1)
    print("Logged in as admin")

    # ═══ 프론트엔드 (큰 사이즈 재캡처) ═══
    print("\n=== Frontend Screenshots ===")
    front_pages = [
        ("f_home", "/"),
        ("f_character", "/bbs/character_list.php"),
        ("f_lore", "/bbs/lore.php"),
        ("f_lore_timeline", "/bbs/lore_timeline.php"),
        ("f_shop", "/bbs/shop.php"),
        ("f_inventory", "/bbs/inventory.php"),
        ("f_attendance", "/bbs/attendance.php"),
        ("f_pioneer", "/bbs/pioneer.php"),
        ("f_expedition", "/bbs/pioneer.php?view=expedition"),
        ("f_achievement", "/bbs/achievement.php"),
        ("f_notification", "/bbs/notification.php"),
        ("f_seal_edit", "/bbs/seal_edit.php"),
        ("f_mypage", "/bbs/mypage.php"),
        ("f_rp", "/bbs/rp_list.php"),
    ]
    for name, url in front_pages:
        try:
            page.goto(BASE + url, wait_until="networkidle", timeout=15000)
            time.sleep(0.5)
            capture(page, name, full=False)
        except Exception as e:
            print(f"  ERROR {name}: {e}")

    # ═══ 관리자 페이지 ═══
    print("\n=== Admin Screenshots ===")

    # --- 튜토리얼 1: 캐릭터 승인 ---
    print("\n[Tutorial 1] 캐릭터 승인")

    # Step 1: 캐릭터 관리 목록
    page.goto(f"{BASE}/adm/morgan/character_list.php", wait_until="networkidle", timeout=15000)
    time.sleep(0.5)
    page.add_style_tag(content=HIGHLIGHT_CSS)
    # 상태 필터나 승인 대기 표시 하이라이트
    add_highlight(page, 'select[name="state"], .form-select', number='1', callout='상태 필터')
    # 테이블의 첫번째 행 하이라이트
    add_highlight(page, 'table tbody tr:first-child', number='2', callout='캐릭터 선택')
    capture(page, "tut_char_01_list")

    # Step 2: 캐릭터 상세 (승인/반려 버튼)
    # 첫 번째 캐릭터 ID 찾기
    ch_link = page.query_selector('table tbody tr:first-child a[href*="character_form"]')
    if ch_link:
        ch_href = ch_link.get_attribute('href')
        if ch_href and not ch_href.startswith('http'):
            ch_href = BASE + '/adm/morgan/' + ch_href
        page.goto(ch_href, wait_until="networkidle", timeout=15000)
    else:
        page.goto(f"{BASE}/adm/morgan/character_form.php?ch_id=1", wait_until="networkidle", timeout=15000)
    time.sleep(0.5)
    page.add_style_tag(content=HIGHLIGHT_CSS)
    # 승인/반려 버튼 하이라이트
    add_highlight(page, 'select[name="ch_state"]', number='1', callout='상태 변경')
    add_highlight(page, 'button[type="submit"], input[type="submit"]', number='2', callout='저장')
    capture(page, "tut_char_02_form")

    # --- 튜토리얼 2: 보상 설정 ---
    print("\n[Tutorial 2] 보상 설정")

    # Step 1: 보상 관리 - 게시판 탭
    page.goto(f"{BASE}/adm/morgan/reward.php", wait_until="networkidle", timeout=15000)
    time.sleep(0.5)
    page.add_style_tag(content=HIGHLIGHT_CSS)
    # 탭 메뉴 하이라이트
    add_highlight(page, '.nav-tabs, .tab-nav, [role="tablist"]', number='1', callout='탭 메뉴')
    capture(page, "tut_reward_01_board")

    # Step 2: 보상 관리 - 활동 탭 (있으면)
    tabs = page.query_selector_all('a[href*="tab="], button[data-tab]')
    for tab in tabs:
        txt = tab.inner_text()
        if '활동' in txt or 'activity' in txt.lower():
            tab.click()
            time.sleep(0.5)
            break
    page.add_style_tag(content=HIGHLIGHT_CSS)
    capture(page, "tut_reward_02_activity")

    # Step 3: 보상 추가/수정 모달이나 폼
    page.goto(f"{BASE}/adm/morgan/reward.php", wait_until="networkidle", timeout=15000)
    time.sleep(0.5)
    page.add_style_tag(content=HIGHLIGHT_CSS)
    # 추가 버튼 하이라이트
    add_highlight(page, 'a[href*="reward_form"], button.btn-add, .btn-primary', number='1', callout='보상 추가/수정')
    capture(page, "tut_reward_03_form", full=True)

    # --- 튜토리얼 3: 파견지 세팅 ---
    print("\n[Tutorial 3] 파견지 세팅")

    # Step 1: 파견지 관리 목록
    page.goto(f"{BASE}/adm/morgan/expedition_area.php", wait_until="networkidle", timeout=15000)
    time.sleep(0.5)
    page.add_style_tag(content=HIGHLIGHT_CSS)
    add_highlight(page, 'a[href*="expedition_area_form"], button.btn-add, .btn-primary:first-of-type', number='1', callout='파견지 추가')
    add_highlight(page, 'table tbody tr:first-child', number='2', callout='파견지 선택')
    capture(page, "tut_exped_01_list")

    # Step 2: 파견지 수정 폼
    ea_link = page.query_selector('table tbody tr:first-child a[href*="expedition_area"]')
    if ea_link:
        ea_href = ea_link.get_attribute('href')
        if ea_href and not ea_href.startswith('http'):
            ea_href = BASE + '/adm/morgan/' + ea_href
        page.goto(ea_href, wait_until="networkidle", timeout=15000)
    else:
        page.goto(f"{BASE}/adm/morgan/expedition_area.php", wait_until="networkidle", timeout=15000)
    time.sleep(0.5)
    page.add_style_tag(content=HIGHLIGHT_CSS)
    capture(page, "tut_exped_02_form", full=True)

    # --- 이벤트 관리 ---
    page.goto(f"{BASE}/adm/morgan/expedition_event.php", wait_until="networkidle", timeout=15000)
    time.sleep(0.5)
    page.add_style_tag(content=HIGHLIGHT_CSS)
    capture(page, "tut_exped_03_event")

    # --- 추가 관리자 페이지 ---
    print("\n=== Additional Admin Pages ===")
    admin_extras = [
        ("adm_config", "/adm/morgan/config.php"),
        ("adm_board_list", "/adm/morgan/board_list.php"),
        ("adm_shop_list", "/adm/morgan/shop_item_list.php"),
        ("adm_emoticon", "/adm/morgan/emoticon_list.php"),
        ("adm_achievement", "/adm/morgan/achievement_list.php"),
    ]
    for name, url in admin_extras:
        try:
            page.goto(BASE + url, wait_until="networkidle", timeout=15000)
            time.sleep(0.5)
            capture(page, name, full=False)
        except Exception as e:
            print(f"  ERROR {name}: {e}")

    browser.close()
    print(f"\nDone! Images saved to: {OUT}")
