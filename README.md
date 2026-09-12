<p>Design Cart Column Scroll Gallery is a WordPress plugin for WooCommerce. It shows products in three columns that travel in opposite directions as the page scrolls. Click a photo and a full-viewport preview opens: title, extra meta rows, price, short description, quantity, variations, add to cart, and a link to the product page.</p>

<p>The plugin was built to present a range of acrylic paintings in a more honest way than a shop grid. A painting needs space and rhythm. Opposite column motion comes from the Column Scroll demo on Codrops. That demo is a visual essay. This plugin rebuilds the idea for a real store: page-scroll pinning, a shortcode, AJAX variation matching, and a Design Cart admin with multiple gallery instances.</p>

<p>Scrolling was the hard part. An inner scroller trapped the wheel and cut the rest of the page off. Pinning the gallery to the viewport and driving the columns from page scroll fixed the footer, but it killed the earlier coast — the feeling that speed keeps going and then fades. Custom wheel physics brought the coast back, then made the whole site feel slow. Coast now runs only while the gallery is pinned. Above and below it, the browser owns the wheel.</p>

<p>You can create as many galleries as you need. Each instance has its own products, colors, typography, and shortcode:</p>

<p><code>[design_cart_cs_gallery id="1"]</code></p>

<p>General settings cover the product source (selected products, a category, or items on sale), a product limit, gallery background, the cursor-follow blur orb, close-button colors, grid width in px or %, scroll speed (how long the pin is, 10–100), and toggles for quantity and variations. The Products tab is a live WooCommerce search plus two custom rows per item (year and technique, for example) and drag-and-drop order. Appearance is per element: preview title, price, options, the view-product link, the cart button, and more. The Cart tab sets the button label; its look lives under Appearance.</p>

<p>The grid is photos only. The preview card holds the words. Quantity and add to cart stay on one line. The blur orb stays inside the gallery. Variation matching is AJAX, so the page never downloads a full variation table.</p>

<p>Requires WordPress 6.0+, PHP 7.4, and WooCommerce 8.0+. License: GPL-2.0-or-later.</p>

<p>Documentation (PL): <a href="https://www.designcart.pl/laboratorium/363-galeria-produktow-woocommerce-z-przeciwnym-scrollem-kolumn.html">Galeria produktów WooCommerce z przeciwnym scrollem kolumn</a></p>

<p>Author: <a href="https://www.designcart.pl/pawel-nosko.html">Paweł Nosko</a> / Design Cart.</p>
