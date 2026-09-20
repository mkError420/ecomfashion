// Rongdhonu Fashion — storefront interactions
document.addEventListener('DOMContentLoaded', function () {
  var navToggle = document.getElementById('navToggle');
  var mainNav = document.getElementById('mainNav');
  var searchToggle = document.getElementById('searchToggle');
  var searchWrap = document.getElementById('searchWrap');

  if (navToggle && mainNav) {
    navToggle.addEventListener('click', function () {
      mainNav.classList.toggle('open');
      if (searchWrap) searchWrap.classList.toggle('open');
    });
  }

  // Category dropdowns: click/touch toggles; desktop also opens on hover via CSS.
  document.querySelectorAll('.menu-trigger').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var item = btn.closest('.menu-item');
      var wasOpen = item.classList.contains('open');
      document.querySelectorAll('.menu-item.open').forEach(function (o) { o.classList.remove('open'); });
      if (!wasOpen) { item.classList.add('open'); }
      btn.setAttribute('aria-expanded', String(!wasOpen));
    });
  });
  document.addEventListener('click', function () {
    document.querySelectorAll('.menu-item.open').forEach(function (o) { o.classList.remove('open'); });
  });

  // Show a compact search toggle on small screens
  function syncSearchToggle() {
    if (!searchToggle || !searchWrap) return;
    if (window.innerWidth <= 720) {
      searchToggle.style.display = 'grid';
    } else {
      searchToggle.style.display = 'none';
      searchWrap.classList.remove('open');
    }
  }
  if (searchToggle && searchWrap) {
    syncSearchToggle();
    searchToggle.addEventListener('click', function () {
      searchWrap.classList.toggle('open');
      var input = searchWrap.querySelector('input');
      if (searchWrap.classList.contains('open') && input) input.focus();
    });
    window.addEventListener('resize', syncSearchToggle);
  }

  // Quantity steppers (any .qty-input)
  document.querySelectorAll('.qty-input').forEach(function (box) {
    var input = box.querySelector('input');
    var minus = box.querySelector('[data-step="-1"]');
    var plus = box.querySelector('[data-step="1"]');
    if (minus) minus.addEventListener('click', function () {
      input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
    });
    if (plus) plus.addEventListener('click', function () {
      var max = parseInt(input.getAttribute('max'), 10) || 9999;
      input.value = Math.min(max, (parseInt(input.value, 10) || 1) + 1);
    });
  });

  // Mobile filter drawer
  var filterToggle = document.querySelector('.filter-toggle');
  var filters = document.querySelector('.filters');
  if (filterToggle && filters) {
    filterToggle.addEventListener('click', function () {
      filters.classList.toggle('open');
    });
  }

  // Auto-dismiss flash alerts
  document.querySelectorAll('.alert').forEach(function (a) {
    setTimeout(function () {
      a.style.transition = 'opacity .4s'; a.style.opacity = '0';
      setTimeout(function () { a.remove(); }, 400);
    }, 5000);
  });
});
