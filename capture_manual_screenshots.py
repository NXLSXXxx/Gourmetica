import time
from pathlib import Path
from playwright.sync_api import sync_playwright

OUT_DIR = Path(r"C:\xampp\htdocs\Gourmetica\storage\app")
OUT_DIR.mkdir(parents=True, exist_ok=True)

def capture_all():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="msedge", headless=True)
        # 1440x900 viewport gives perfect desktop view with expanded sidebar and full tables
        context = browser.new_context(
            viewport={"width": 1440, "height": 900},
            device_scale_factor=1.25
        )
        page = context.new_page()

        print("--- CAPTURING PUBLIC PAGES ---")

        # 1. Home
        print("Capturing manual-home.png...")
        page.goto("https://gourmetica.com.pe/", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual-home.png"))

        # 2. Shop / Catalog
        print("Capturing manual-catalogo.png...")
        page.goto("https://gourmetica.com.pe/shop", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual-catalogo.png"))

        # Add item to cart for realistic cart view
        print("Adding product to cart...")
        try:
            # Click on first product or add to cart button
            add_btn = page.locator('button:has-text("Agregar"), form[action*="cart"] button, .product-card button').first
            if add_btn.count() > 0:
                add_btn.click()
                time.sleep(1.5)
        except Exception as e:
            print("Notice on add to cart:", e)

        # 3. Cart
        print("Capturing manual-carrito.png...")
        page.goto("https://gourmetica.com.pe/cart", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual-carrito.png"))

        # 4. Locations
        print("Capturing manual-sedes.png...")
        page.goto("https://gourmetica.com.pe/locations", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual-sedes.png"))

        # 5. Catering public
        print("Capturing manual-catering.png...")
        page.goto("https://gourmetica.com.pe/catering", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual-catering.png"))

        # 6. Contact
        print("Capturing manual-contacto.png...")
        page.goto("https://gourmetica.com.pe/contact", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual-contacto.png"))

        # 7. Client Login
        print("Capturing manual-login.png...")
        page.goto("https://gourmetica.com.pe/login", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual-login.png"))

        print("--- CAPTURING ADMIN LOGIN ---")

        # 8. Admin Login
        print("Capturing manual-admin-login.png...")
        page.goto("https://gourmetica.com.pe/admin/login", wait_until="networkidle", timeout=30000)
        page.fill('input[type="email"], input[name="email"]', "admin@gourmetica.pe")
        page.fill('input[type="password"], input[name="password"]', "••••••••")
        time.sleep(0.5)
        page.screenshot(path=str(OUT_DIR / "manual-admin-login.png"))

        # Now perform actual login with valid server credentials
        page.fill('input[type="email"], input[name="email"]', "admin@gourmetica.com")
        page.fill('input[type="password"], input[name="password"]', "password")
        page.click('button[type="submit"]')
        page.wait_for_load_state("networkidle")
        time.sleep(1)

        print("--- CAPTURING INTRANET PAGES ---")

        # 9. Dashboard
        print("Capturing manual-dashboard-wide.png...")
        page.goto("https://gourmetica.com.pe/intranet/dashboard", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual-dashboard-wide.png"))
        page.screenshot(path=str(OUT_DIR / "manual4-dashboard.png"))

        # 10. Live orders / Caja
        print("Capturing manual4-live-orders.png...")
        page.goto("https://gourmetica.com.pe/intranet/live-orders", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-live-orders.png"))

        # 11. Orders list
        print("Capturing manual4-pedidos.png...")
        page.goto("https://gourmetica.com.pe/intranet/orders", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-pedidos.png"))

        # 12. Order Detail
        print("Capturing manual4-pedido-detalle.png...")
        try:
            page.goto("https://gourmetica.com.pe/intranet/orders/14", wait_until="networkidle", timeout=30000)
            time.sleep(1)
            page.screenshot(path=str(OUT_DIR / "manual4-pedido-detalle.png"))
        except Exception as e:
            print("Notice on order detail:", e)

        # 13. Catering Admin
        print("Capturing manual4-catering-admin.png...")
        page.goto("https://gourmetica.com.pe/intranet/catering", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-catering-admin.png"))

        # 14. Catering Admin Detail
        print("Capturing manual4-catering-detalle.png...")
        try:
            page.goto("https://gourmetica.com.pe/intranet/catering/1", wait_until="networkidle", timeout=30000)
            time.sleep(1)
            page.screenshot(path=str(OUT_DIR / "manual4-catering-detalle.png"))
        except Exception as e:
            print("Notice on catering detail:", e)

        # 15. Sales & SUNAT
        print("Capturing manual4-ventas.png...")
        page.goto("https://gourmetica.com.pe/intranet/sales", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-ventas.png"))

        # 16. Clients list
        print("Capturing manual4-clientes.png...")
        page.goto("https://gourmetica.com.pe/intranet/clients", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-clientes.png"))

        # 17. Client Detail
        print("Capturing manual4-cliente-detalle.png...")
        try:
            page.goto("https://gourmetica.com.pe/intranet/clients/7", wait_until="networkidle", timeout=30000)
            time.sleep(1)
            page.screenshot(path=str(OUT_DIR / "manual4-cliente-detalle.png"))
        except Exception as e:
            print("Notice on client detail:", e)

        # 18. Supplies (Insumos)
        print("Capturing manual4-insumos.png...")
        page.goto("https://gourmetica.com.pe/intranet/supplies", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-insumos.png"))

        # 19. Production & Kitchen
        print("Capturing manual4-produccion.png...")
        page.goto("https://gourmetica.com.pe/intranet/productions", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-produccion.png"))

        # 20. Purchases (Compras)
        print("Capturing manual4-compras.png...")
        page.goto("https://gourmetica.com.pe/intranet/purchases", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-compras.png"))

        # 21. Products
        print("Capturing manual4-productos.png...")
        page.goto("https://gourmetica.com.pe/intranet/products", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-productos.png"))

        # 22. Categories
        print("Capturing manual4-categorias.png...")
        page.goto("https://gourmetica.com.pe/intranet/categories", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-categorias.png"))

        # 23. Headquarters (Sedes Admin)
        print("Capturing manual4-sedes-admin.png...")
        page.goto("https://gourmetica.com.pe/intranet/headquarters", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-sedes-admin.png"))

        # 24. Delivery Zones
        print("Capturing manual4-zonas-delivery.png...")
        page.goto("https://gourmetica.com.pe/intranet/delivery-zones", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-zonas-delivery.png"))

        # 25. Banners
        print("Capturing manual4-banners.png...")
        page.goto("https://gourmetica.com.pe/intranet/banners", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-banners.png"))

        # 26. Users & Admins
        print("Capturing manual4-usuarios.png...")
        page.goto("https://gourmetica.com.pe/intranet/users", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-usuarios.png"))

        # 27. Settings
        print("Capturing manual4-configuracion.png...")
        page.goto("https://gourmetica.com.pe/intranet/settings", wait_until="networkidle", timeout=30000)
        time.sleep(1)
        page.screenshot(path=str(OUT_DIR / "manual4-configuracion.png"))

        browser.close()
        print("ALL SCREENSHOTS CAPTURED SUCCESSFULLY!")

if __name__ == "__main__":
    capture_all()
