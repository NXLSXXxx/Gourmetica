/**
 * GOURMETICA - Main JavaScript
 */

// Mega Menu Global State
window.currentMegaCategory = null;

/**
 * Fetches products for the mega menu via AJAX
 * @param {string} slug - Category slug
 * @param {HTMLElement} element - The category menu item
 */
async function fetchProducts(slug, element) {
    if (window.currentMegaCategory === slug) return;
    window.currentMegaCategory = slug;

    document.querySelectorAll('.mega-menu-item').forEach(el => el.classList.remove('active'));
    element.classList.add('active');

    const grid = document.getElementById('mega-products-grid');
    if (!grid) return;

    grid.innerHTML = '<div class="col-span-3 flex items-center justify-center h-64"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-primary"></div></div>';

    try {
        const response = await fetch(`/shop/category/${slug}`);
        const products = await response.json();

        if (products.length === 0) {
            grid.innerHTML = '<div class="col-span-3 text-center py-20 text-gray-400">No hay productos en esta categoría</div>';
            return;
        }

        grid.innerHTML = products.map(p => `
            <a href="/shop/product/${p.slug}" class="mega-product-card group/item flex flex-col items-center text-center">
                <div class="aspect-square w-full rounded-2xl overflow-hidden bg-gray-50 mb-3 shadow-sm border border-gray-100 flex items-center justify-center">
                    ${p.image ?
                        `<img src="/storage/${p.image}" class="w-full h-full object-cover group-hover/item:scale-110 transition-transform duration-500">` :
                        `<svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>`
                    }
                </div>
                <h4 class="text-[11px] font-bold text-brand-secondary group-hover/item:text-brand-primary transition-colors line-clamp-1 uppercase tracking-tighter">${p.name}</h4>
                <span class="text-[10px] font-extrabold text-brand-primary mt-1">S/ ${parseFloat(p.base_price).toFixed(2)}</span>
            </a>
        `).join('');
    } catch (error) {
        console.error('Error fetching products:', error);
        grid.innerHTML = '<div class="col-span-3 text-center py-20 text-red-400 text-xs">Error al cargar productos</div>';
    }
}
