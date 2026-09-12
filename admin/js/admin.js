/**
 * Design Cart Column Scroll Gallery
 * Author: Paweł Nosko — https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart — https://www.designcart.pl/
 */
(function ($) {
  'use strict';

  var cfg = window.dccsgAdmin || {};
  var timer = null;

  function reindex() {
    $('[data-product-list] [data-product-row]').each(function (i) {
      $(this).find('[name]').each(function () {
        this.name = this.name.replace(/products\[\d+\]/, 'products[' + i + ']');
      });
    });
    $('[data-empty-hint]').prop('hidden', $('[data-product-list] [data-product-row]').length > 0);
  }

  function productRow(item, idx) {
    var thumb = item.thumb
      ? '<img class="dccsg-product__thumb" src="' + item.thumb + '" alt="" />'
      : '<span class="dccsg-product__thumb dccsg-product__thumb--empty"></span>';
    return (
      '<li class="dccsg-product" data-product-row>' +
      '<span class="dashicons dashicons-move dccsg-product__handle" aria-hidden="true"></span>' +
      thumb +
      '<div class="dccsg-product__body">' +
      '<input type="hidden" name="products[' + idx + '][id]" value="' + item.id + '" />' +
      '<strong class="dccsg-product__title"></strong>' +
      '<div class="dc-row dc-row--2">' +
      '<div class="dc-field"><label class="dc-label">' + cfg.i18n.line1 + '</label>' +
      '<input class="dc-input" type="text" name="products[' + idx + '][line1]" value="' + (item.line1 || '') + '" placeholder="2015" /></div>' +
      '<div class="dc-field"><label class="dc-label">' + cfg.i18n.line2 + '</label>' +
      '<input class="dc-input" type="text" name="products[' + idx + '][line2]" value="' + (item.line2 || '') + '" /></div>' +
      '</div></div>' +
      '<button type="button" class="button-link-delete dccsg-product__remove" data-remove-product aria-label="' + cfg.i18n.remove + '">' +
      '<span class="dashicons dashicons-trash"></span></button></li>'
    );
  }

  function addProduct(item) {
    var list = $('[data-product-list]');
    if (list.find('input[name$="[id]"][value="' + item.id + '"]').length) {
      return;
    }
    var html = productRow(item, list.children().length);
    var $row = $(html);
    $row.find('.dccsg-product__title').text(item.name);
    list.append($row);
    reindex();
  }

  function existingProducts() {
    var rows = [];
    $('[data-product-list] [data-product-row]').each(function () {
      rows.push({
        id: $(this).find('input[name$="[id]"]').val(),
        line1: $(this).find('input[name$="[line1]"]').val(),
        line2: $(this).find('input[name$="[line2]"]').val(),
      });
    });
    return rows;
  }

  function toggleSource() {
    var source = $('input[name="source"]:checked').val();
    $('[data-source-category]').toggle(source === 'category');
  }

  $(function () {
    toggleSource();
    $(document).on('change', 'input[name="source"]', toggleSource);

    var list = $('[data-product-list]');
    if (list.length && $.fn.sortable) {
      list.sortable({
        handle: '.dccsg-product__handle',
        update: reindex,
      });
    }

    $(document).on('click', '[data-remove-product]', function () {
      $(this).closest('[data-product-row]').remove();
      reindex();
    });

    var $input = $('[data-search-input]');
    var $results = $('[data-search-results]');

    $input.on('input', function () {
      var q = $.trim(this.value);
      clearTimeout(timer);
      if (q.length < 2) {
        $results.prop('hidden', true).empty();
        return;
      }
      timer = setTimeout(function () {
        $.getJSON(cfg.ajax, { action: 'dccsg_search_products', nonce: cfg.nonce, q: q })
          .done(function (res) {
            $results.empty();
            if (!res.success || !res.data.length) {
              $results.prop('hidden', true);
              return;
            }
            res.data.forEach(function (item) {
              var thumb = item.thumb
                ? '<img class="dccsg-search__thumb" src="' + item.thumb + '" alt="" />'
                : '<span class="dccsg-search__thumb"></span>';
              var $btn = $('<button type="button" class="dccsg-search__item"></button>');
              $btn.append(thumb).append($('<span></span>').text(item.name));
              $btn.on('click', function () {
                addProduct(item);
                $input.val('');
                $results.prop('hidden', true).empty();
              });
              $results.append($('<li></li>').append($btn));
            });
            $results.prop('hidden', false);
          });
      }, 220);
    });

    $(document).on('click', function (e) {
      if (!$(e.target).closest('[data-product-search]').length) {
        $results.prop('hidden', true);
      }
    });

    $('[data-load-source]').on('click', function () {
      var $btn = $(this);
      $btn.prop('disabled', true);
      $.post(cfg.ajax, {
        action: 'dccsg_load_source',
        nonce: cfg.nonce,
        source: $('input[name="source"]:checked').val(),
        category_id: $('select[name="category_id"]').val(),
        limit: $('input[name="limit"]').val(),
        products: JSON.stringify(existingProducts()),
      }).done(function (res) {
        if (res.success) {
          list.html(res.data.html);
          reindex();
        }
      }).always(function () {
        $btn.prop('disabled', false);
      });
    });

    $(document).on('change', 'input[name="container_width_unit"]', function () {
      $('#container_width').attr('max', this.value === 'px' ? 4000 : 100);
    });

    $(document).on('click', '[data-copy-shortcode]', function () {
      var text = this.getAttribute('data-copy-shortcode') || '';
      if (!text || !navigator.clipboard) {
        return;
      }
      var btn = this;
      navigator.clipboard.writeText(text).then(function () {
        btn.classList.add('is-copied');
        window.setTimeout(function () { btn.classList.remove('is-copied'); }, 1200);
      });
    });

    $(document).on('submit', '[data-confirm-delete]', function (e) {
      if (!window.confirm(cfg.i18n.confirmDel || '')) {
        e.preventDefault();
      }
    });
  });
})(jQuery);
