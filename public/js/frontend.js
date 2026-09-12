/**
 * Design Cart Column Scroll Gallery
 * Author: Paweł Nosko — https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart — https://www.designcart.pl/
 */
(function (window, document) {
  'use strict';

  var cfg = window.dccsgFront || {};
  var pageCoast = {
    vel: 0,
    ticking: false,
    lastAt: 0,
    bound: false,
  };
  var galleries = [];

  function stopPageCoast() {
    pageCoast.vel = 0;
    pageCoast.ticking = false;
  }

  function isGalleryPinned() {
    var vh = window.innerHeight;
    var i;
    for (i = 0; i < galleries.length; i++) {
      var gallery = galleries[i];
      if (!gallery.pin || gallery.isOpen) {
        continue;
      }
      var rect = gallery.pin.getBoundingClientRect();
      if (rect.top <= 1 && rect.bottom > vh + 1) {
        return true;
      }
    }
    return false;
  }

  function nestedCanScroll(start, dy) {
    var el = start;
    while (el && el !== document.body && el !== document.documentElement) {
      var style = window.getComputedStyle(el);
      var oy = style.overflowY;
      if ((oy === 'auto' || oy === 'scroll') && el.scrollHeight > el.clientHeight + 1) {
        if ((dy < 0 && el.scrollTop > 0) || (dy > 0 && el.scrollTop + el.clientHeight < el.scrollHeight - 1)) {
          return true;
        }
      }
      el = el.parentElement;
    }
    return false;
  }

  function coastPage() {
    if (document.documentElement.classList.contains('dccsg-oh') || !isGalleryPinned()) {
      stopPageCoast();
      return;
    }
    var max = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
    var next = window.scrollY + pageCoast.vel;
    if (next <= 0) {
      window.scrollTo(0, 0);
      pageCoast.vel = 0;
    } else if (next >= max) {
      window.scrollTo(0, max);
      pageCoast.vel = 0;
    } else {
      window.scrollTo(0, next);
    }
    pageCoast.vel *= 0.91;
    if (Math.abs(pageCoast.vel) > 0.3 && isGalleryPinned()) {
      requestAnimationFrame(coastPage);
    } else {
      stopPageCoast();
    }
  }

  function onPageWheel(e) {
    if (document.documentElement.classList.contains('dccsg-oh') || e.ctrlKey || e.metaKey) {
      return;
    }
    var delta = e.deltaY;
    if (e.deltaMode === 1) {
      delta *= 16;
    } else if (e.deltaMode === 2) {
      delta *= window.innerHeight;
    }
    if (!delta || !isGalleryPinned() || nestedCanScroll(e.target, delta)) {
      return;
    }
    e.preventDefault();
    var now = performance.now();
    var dt = now - pageCoast.lastAt;
    pageCoast.lastAt = now;
    var impulse = delta * 0.22 * 0.7;
    if (dt < 40) {
      pageCoast.vel = pageCoast.vel * 0.5 + impulse * 0.55;
    } else {
      pageCoast.vel += impulse;
    }
    pageCoast.vel = Math.max(-70, Math.min(70, pageCoast.vel));
    if (!pageCoast.ticking) {
      pageCoast.ticking = true;
      requestAnimationFrame(coastPage);
    }
  }

  function bindPageCoast() {
    if (pageCoast.bound) {
      return;
    }
    pageCoast.bound = true;
    window.addEventListener('wheel', onPageWheel, { passive: false });
    window.addEventListener('pointerdown', stopPageCoast);
  }

  function preloadBackgrounds(root) {
    var els = [].slice.call(root.querySelectorAll('.dccsg-item__img'));
    var urls = els.map(function (el) {
      var bg = el.getAttribute('style') || '';
      var m = bg.match(/url\((['"]?)(.*?)\1\)/);
      return m ? m[2] : null;
    }).concat([].slice.call(root.querySelectorAll('[data-open-product]')).map(function (el) {
      return el.getAttribute('data-image');
    })).filter(Boolean);
    return Promise.all(urls.map(function (src) {
      return new Promise(function (resolve) {
        var img = new Image();
        img.onload = img.onerror = resolve;
        img.src = src;
      });
    }));
  }

  function Gallery(root) {
    this.root = root;
    this.pin = root.closest('[data-dccsg-pin]') || root.parentElement;
    this.stage = root.querySelector('[data-dccsg-stage]');
    this.columnsEl = root.querySelector('.dccsg-columns');
    this.oddColumns = [].slice.call(root.querySelectorAll('.dccsg-column--odd'));
    this.midColumns = [].slice.call(root.querySelectorAll('.dccsg-column-wrap--mid .dccsg-column'));
    this.overlay = root.querySelector('[data-overlay]');
    this.overlayImg = root.querySelector('[data-overlay-img]');
    this.overlayHome = this.overlay ? this.overlay.parentNode : null;
    this.overlayNext = this.overlay ? this.overlay.nextSibling : null;
    this.blob = root.querySelector('[data-blob]');
    this.items = [].slice.call(root.querySelectorAll('[data-open-product]'));
    this.panels = [].slice.call(root.querySelectorAll('.dccsg-content__item'));
    this.isOpen = false;
    this.currentPos = 0;
    this.scrollSpeed = Math.max(10, Math.min(100, parseFloat(root.getAttribute('data-scroll-speed')) || 22));
    this.pinExtra = 0;

    this.onScroll = this.onScroll.bind(this);
    this.onResize = this.onResize.bind(this);
    this.onKey = this.onKey.bind(this);

    galleries.push(this);
    window.addEventListener('scroll', this.onScroll, { passive: true });
    window.addEventListener('resize', this.onResize);
    bindPageCoast();
    this.measure();
    this.onScroll();
    var self = this;
    requestAnimationFrame(function () {
      self.measure();
      self.onScroll();
    });
    window.setTimeout(function () {
      self.measure();
      self.onScroll();
    }, 300);

    this.bindGrid();
    this.bindOverlay();
    this.bindBlob();
    this.bindCart();
  }

  Gallery.prototype.isMobile = function () {
    return window.matchMedia('(max-width: 700px)').matches;
  };

  Gallery.prototype.viewportH = function () {
    return window.innerHeight;
  };

  Gallery.prototype.measure = function () {
    var vh = this.viewportH();
    var travel = 0;
    if (this.isMobile()) {
      this.oddColumns.forEach(function (column) { column.style.transform = ''; });
      this.midColumns.forEach(function (column) { column.style.transform = ''; });
      if (this.columnsEl) {
        this.columnsEl.style.transform = '';
        travel = Math.max(0, this.columnsEl.scrollHeight - vh);
      }
    } else {
      if (this.columnsEl) {
        this.columnsEl.style.transform = '';
      }
      this.oddColumns.concat(this.midColumns).forEach(function (column) {
        var wrap = column.parentElement;
        travel = Math.max(travel, column.scrollHeight - (wrap ? wrap.clientHeight : vh));
      });
    }
    var mul = Math.min(6, 100 / this.scrollSpeed);
    this.pinExtra = Math.max(vh * 0.8, travel * mul * 0.5);
    if (this.pin) {
      this.pin.style.height = (vh + this.pinExtra) + 'px';
    }
  };

  Gallery.prototype.onResize = function () {
    var self = this;
    window.clearTimeout(this.resizeTimer);
    this.resizeTimer = window.setTimeout(function () {
      self.measure();
      self.onScroll();
    }, 80);
  };

  Gallery.prototype.applyProgress = function (p) {
    var vh = this.viewportH();
    if (this.isMobile()) {
      this.oddColumns.forEach(function (column) { column.style.transform = ''; });
      this.midColumns.forEach(function (column) { column.style.transform = ''; });
      if (this.columnsEl) {
        var shift = Math.max(0, this.columnsEl.scrollHeight - vh);
        this.columnsEl.style.transform = 'translateY(' + (-shift * p) + 'px)';
      }
      return;
    }
    if (this.columnsEl) {
      this.columnsEl.style.transform = '';
    }
    this.oddColumns.forEach(function (column) {
      var wrap = column.parentElement;
      var maxShift = Math.max(0, column.scrollHeight - wrap.clientHeight);
      column.style.transform = 'translateY(' + (-maxShift * (1 - p)) + 'px)';
    });
    this.midColumns.forEach(function (column) {
      var wrap = column.parentElement;
      var maxShift = Math.max(0, column.scrollHeight - wrap.clientHeight);
      column.style.transform = 'translateY(' + (-maxShift * p) + 'px)';
    });
  };

  Gallery.prototype.onScroll = function () {
    if (this.isOpen || !this.pin) {
      return;
    }
    var vh = this.viewportH();
    var rect = this.pin.getBoundingClientRect();
    var extra = this.pinExtra;
    var p = 0;
    var state = 'start';

    if (rect.top <= 0 && rect.bottom > vh) {
      state = 'pinned';
      p = extra > 0 ? Math.min(1, Math.max(0, -rect.top / extra)) : 1;
    } else if (rect.bottom <= vh) {
      state = 'end';
      p = 1;
    }

    this.root.classList.toggle('is-pinned', state === 'pinned');
    this.root.classList.toggle('is-end', state === 'end');
    this.applyProgress(p);
  };

  Gallery.prototype.bindGrid = function () {
    var self = this;
    this.items.forEach(function (btn) {
      btn.addEventListener('click', function () {
        self.open(btn);
      });
    });
  };

  Gallery.prototype.bindOverlay = function () {
    var self = this;
    [].slice.call(this.root.querySelectorAll('[data-back], [data-close]')).forEach(function (btn) {
      btn.addEventListener('click', function () { self.close(); });
    });
    document.addEventListener('keydown', this.onKey);
  };

  Gallery.prototype.bindBlob = function () {
    var blob = this.blob;
    if (!blob) {
      return;
    }
    var root = this.root;
    var x = root.clientWidth / 2;
    var y = root.clientHeight * 0.38;
    var tx = x;
    var ty = y;
    var ticking = false;

    blob.style.transform = 'translate3d(' + x + 'px,' + y + 'px,0) translate(-50%,-50%)';

    function tick() {
      x += (tx - x) * 0.12;
      y += (ty - y) * 0.12;
      blob.style.transform = 'translate3d(' + x + 'px,' + y + 'px,0) translate(-50%,-50%)';
      if (Math.abs(tx - x) > 0.4 || Math.abs(ty - y) > 0.4) {
        requestAnimationFrame(tick);
      } else {
        ticking = false;
      }
    }

    root.addEventListener('mousemove', function (e) {
      var rect = root.getBoundingClientRect();
      tx = e.clientX - rect.left;
      ty = e.clientY - rect.top;
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(tick);
      }
    }, { passive: true });
  };

  Gallery.prototype.mountOverlay = function (toBody) {
    if (!this.overlay || !this.overlayHome) {
      return;
    }
    if (toBody) {
      if (this.overlay.parentNode !== document.body) {
        document.body.appendChild(this.overlay);
      }
      return;
    }
    if (this.overlay.parentNode !== this.overlayHome) {
      this.overlayHome.insertBefore(this.overlay, this.overlayNext);
    }
  };

  Gallery.prototype.onKey = function (e) {
    if (e.key === 'Escape' && this.isOpen) {
      this.close();
    }
  };

  Gallery.prototype.open = function (btn) {
    if (this.isOpen) {
      return;
    }
    var pos = btn.getAttribute('data-pos');
    var src = btn.getAttribute('data-image') || '';
    var panel = this.root.querySelector('.dccsg-content__item[data-pos="' + pos + '"]');

    this.currentPos = pos;
    this.isOpen = true;
    stopPageCoast();
    document.documentElement.classList.add('dccsg-oh');
    this.root.classList.add('is-open');
    this.mountOverlay(true);
    this.overlay.classList.add('is-open');
    this.overlay.hidden = false;

    this.panels.forEach(function (el) {
      el.hidden = el !== panel;
    });
    if (this.overlayImg) {
      this.overlayImg.removeAttribute('style');
      this.overlayImg.alt = btn.getAttribute('aria-label') || '';
      this.overlayImg.src = src;
    }
  };

  Gallery.prototype.close = function () {
    if (!this.isOpen) {
      return;
    }
    var self = this;
    var done = function () {
      self.isOpen = false;
      self.root.classList.remove('is-open');
      self.overlay.classList.remove('is-open', 'is-closing');
      self.overlay.hidden = true;
      self.mountOverlay(false);
      document.documentElement.classList.remove('dccsg-oh');
      self.panels.forEach(function (el) { el.hidden = true; });
      if (self.overlayImg) {
        self.overlayImg.removeAttribute('src');
      }
      self.onScroll();
    };

    this.overlay.classList.add('is-closing');
    this.overlay.classList.remove('is-open');
    window.setTimeout(done, 300);
  };

  Gallery.prototype.bindCart = function () {
    var self = this;
    [].slice.call(this.root.querySelectorAll('[data-cart-form]')).forEach(function (form) {
      var plus = form.querySelector('[data-qty-plus]');
      var minus = form.querySelector('[data-qty-minus]');
      var input = form.querySelector('[name="quantity"]');
      if (plus && input) {
        plus.addEventListener('click', function () {
          var max = input.max ? parseInt(input.max, 10) : 0;
          var next = parseInt(input.value, 10) + 1;
          if (!max || next <= max) {
            input.value = next;
          }
        });
      }
      if (minus && input) {
        minus.addEventListener('click', function () {
          var min = input.min ? parseInt(input.min, 10) : 1;
          input.value = Math.max(min, parseInt(input.value, 10) - 1);
        });
      }
      [].slice.call(form.querySelectorAll('[data-attribute]')).forEach(function (select) {
        select.addEventListener('change', function () {
          self.fetchVariation(form);
        });
      });
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        self.addToCart(form);
      });
    });
  };

  Gallery.prototype.collectAttributes = function (form) {
    var attrs = {};
    [].slice.call(form.querySelectorAll('[data-attribute]')).forEach(function (select) {
      attrs[select.name] = select.value;
    });
    return attrs;
  };

  Gallery.prototype.fetchVariation = function (form) {
    var attrs = this.collectAttributes(form);
    var ready = Object.keys(attrs).every(function (k) { return attrs[k]; });
    var productId = form.querySelector('[name="product_id"]').value;
    var variationInput = form.querySelector('[data-variation-id]');
    var price = form.closest('.dccsg-content__item').querySelector('[data-price]');
    var btn = form.querySelector('[data-add-to-cart]');
    var self = this;

    if (!ready) {
      variationInput.value = '0';
      btn.disabled = true;
      return;
    }

    var body = new URLSearchParams();
    body.append('action', 'dccsg_get_variation');
    body.append('nonce', cfg.nonce);
    body.append('product_id', productId);
    Object.keys(attrs).forEach(function (key) {
      body.append('attributes[' + key + ']', attrs[key]);
    });

    fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success) {
          return;
        }
        variationInput.value = res.data.variation_id || 0;
        if (price && res.data.price_html) {
          price.innerHTML = res.data.price_html;
        }
        btn.disabled = !res.data.in_stock || !res.data.variation_id;
        if (res.data.image && self.overlayImg) {
          self.overlayImg.src = res.data.image;
        }
      });
  };

  Gallery.prototype.addToCart = function (form) {
    var msg = form.querySelector('[data-cart-msg]');
    var btn = form.querySelector('[data-add-to-cart]');
    var productId = form.querySelector('[name="product_id"]').value;
    var qty = form.querySelector('[name="quantity"]').value || 1;
    var variationId = form.querySelector('[data-variation-id]').value || 0;
    var attrs = this.collectAttributes(form);
    var hasAttrs = Object.keys(attrs).length > 0;

    if (hasAttrs && !parseInt(variationId, 10)) {
      this.setMsg(msg, cfg.i18n.selectOptions, false);
      return;
    }

    btn.disabled = true;
    var body = new URLSearchParams();
    body.append('action', 'dccsg_add_to_cart');
    body.append('nonce', cfg.nonce);
    body.append('product_id', productId);
    body.append('quantity', qty);
    body.append('variation_id', variationId);
    Object.keys(attrs).forEach(function (key) {
      body.append('variation[' + key + ']', attrs[key]);
    });

    fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        btn.disabled = false;
        if (!res.success) {
          this.setMsg(msg, (res.data && res.data.message) || cfg.i18n.error, false);
          return;
        }
        this.setMsg(msg, res.data.message || cfg.i18n.added, true);
        if (window.jQuery && res.data.fragments) {
          window.jQuery(document.body).trigger('added_to_cart', [res.data.fragments, res.data.cart_hash]);
        }
      }.bind(this))
      .catch(function () {
        btn.disabled = false;
        this.setMsg(msg, cfg.i18n.error, false);
      }.bind(this));
  };

  Gallery.prototype.setMsg = function (el, text, ok) {
    if (!el) {
      return;
    }
    el.hidden = false;
    el.textContent = text;
    el.style.color = ok ? '#178675' : '#e5484d';
  };

  function init() {
    [].slice.call(document.querySelectorAll('[data-dccsg]')).forEach(function (root) {
      if (root._dccsg) {
        return;
      }
      root.classList.add('dccsg--loading');
      preloadBackgrounds(root).then(function () {
        root.classList.remove('dccsg--loading');
        root._dccsg = new Gallery(root);
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window, document);
