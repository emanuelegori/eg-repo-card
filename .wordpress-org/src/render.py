from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b = p.chromium.launch()
    for name, (w, h), scales in (("banner", (772, 250), {1: "banner-772x250.png", 2: "banner-1544x500.png"}),
                                 ("icon", (128, 128), {1: "icon-128x128.png", 2: "icon-256x256.png"})):
        for dsf, out in scales.items():
            pg = b.new_page(viewport={"width": w, "height": h}, device_scale_factor=dsf)
            pg.goto(f"file:///work/{name}.html", wait_until="networkidle")
            pg.evaluate("document.fonts.ready")
            pg.screenshot(path=f"/work/{out}", omit_background=(name == "icon"))
            pg.close()
    b.close()
