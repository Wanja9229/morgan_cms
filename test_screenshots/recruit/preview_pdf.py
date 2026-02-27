"""PDF/HTML 미리보기 스크린샷 생성"""
from playwright.sync_api import sync_playwright
import os, time

html_path = os.path.abspath('guide_source.html').replace('\\', '/')
out_dir = 'pdf_preview'
os.makedirs(out_dir, exist_ok=True)

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    ctx = browser.new_context(viewport={'width': 900, 'height': 1200})
    page = ctx.new_page()
    page.goto('file:///' + html_path)
    page.wait_for_load_state('networkidle')
    time.sleep(2)

    # Cover
    page.screenshot(path=os.path.join(out_dir, 'preview_cover.png'), full_page=False)
    # Full
    page.screenshot(path=os.path.join(out_dir, 'preview_full.png'), full_page=True)

    # Check broken images
    js = """() => {
        const imgs = document.querySelectorAll('img');
        const broken = [];
        imgs.forEach(img => {
            if (!img.complete || img.naturalWidth === 0) broken.push(img.src);
        });
        return {total: imgs.length, broken: broken};
    }"""
    result = page.evaluate(js)
    print(f"Total images: {result['total']}, Broken: {len(result['broken'])}")
    if result['broken']:
        for b in result['broken']:
            print(f"  BROKEN: {b}")

    browser.close()
    print("Done!")
