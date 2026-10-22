/* ==========================================================================
   StudyMate — shared UI behaviour
   Theme switching, off-canvas navigation and small quality-of-life helpers.
   ========================================================================== */
(function () {
  "use strict";

  var STORAGE_KEY = "studymate_theme";
  var root = document.documentElement;

  /* ---------- Theme ---------- */
  function applyTheme(theme) {
    var isLight = theme === "light";
    root.setAttribute("data-theme", isLight ? "light" : "dark");
    document.body.classList.toggle("light-theme", isLight);
    try {
      localStorage.setItem(STORAGE_KEY, isLight ? "light" : "dark");
    } catch (e) {
      /* storage unavailable (private mode) — theme still applies for this page */
    }
    syncThemeButtons();
  }

  function currentTheme() {
    return root.getAttribute("data-theme") === "light" ? "light" : "dark";
  }

  function syncThemeButtons() {
    var isLight = currentTheme() === "light";
    var buttons = document.querySelectorAll("[data-theme-toggle]");
    for (var i = 0; i < buttons.length; i++) {
      buttons[i].textContent = isLight ? "☀️" : "🌙";
      buttons[i].setAttribute("aria-label", isLight ? "Switch to dark theme" : "Switch to light theme");
      buttons[i].setAttribute("title", isLight ? "Switch to dark theme" : "Switch to light theme");
    }
  }

  function toggleTheme() {
    applyTheme(currentTheme() === "light" ? "dark" : "light");
  }

  /* ---------- Off-canvas navigation ---------- */
  var sidebar = null;
  var backdrop = null;
  var toggleBtn = null;

  function isMobile() {
    return window.matchMedia("(max-width: 991.98px)").matches;
  }

  function setSidebar(open) {
    if (!sidebar) return;
    sidebar.classList.toggle("is-open", open);
    if (backdrop) backdrop.classList.toggle("show", open);
    if (toggleBtn) {
      toggleBtn.classList.toggle("is-open", open);
      toggleBtn.setAttribute("aria-expanded", open ? "true" : "false");
    }
    document.body.style.overflow = open && isMobile() ? "hidden" : "";
  }

  function closeSidebar() {
    setSidebar(false);
  }

  function initSidebar() {
    sidebar = document.getElementById("smSidebar");
    backdrop = document.getElementById("smBackdrop");
    toggleBtn = document.getElementById("smMenuToggle");

    if (toggleBtn) {
      toggleBtn.addEventListener("click", function () {
        setSidebar(!sidebar.classList.contains("is-open"));
      });
    }

    if (backdrop) {
      backdrop.addEventListener("click", closeSidebar);
    }

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeSidebar();
    });

    window.addEventListener("resize", function () {
      if (!isMobile()) closeSidebar();
    });

    /* Tapping a link inside the drawer navigates away — close it first. */
    if (sidebar) {
      sidebar.addEventListener("click", function (e) {
        if (e.target.closest("a") && isMobile()) closeSidebar();
      });
    }
  }

  /* ---------- Table helpers ---------- */
  function initTables() {
    var tables = document.querySelectorAll(".table-wrap");
    for (var i = 0; i < tables.length; i++) {
      tables[i].setAttribute("tabindex", "0");
      tables[i].setAttribute("role", "region");
      tables[i].setAttribute("aria-label", "Scrollable data table");
    }
  }

  /* ---------- Unread notification badge ---------- */
  function initNotifications() {
    var btn = document.querySelector("[data-notify-url]");
    if (!btn) return;
    var dot = document.getElementById("smNotifyDot");
    var url = btn.getAttribute("data-notify-url");
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!url || !dot) return;

    fetch(url, { headers: { "X-CSRF-Token": token ? token.content : "" }, credentials: "same-origin" })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (data) {
        if (!data || !data.ok) return;
        var count = parseInt(data.count, 10) || 0;
        dot.hidden = count === 0;
        btn.setAttribute("aria-label", btn.getAttribute("aria-label") + " (" + count + " unread)");
        btn.setAttribute("title", count + " unread notification" + (count === 1 ? "" : "s"));
      })
      .catch(function () { /* the badge is decorative — never break the page for it */ });
  }

  function init() {
    initSidebar();
    initTables();
    initNotifications();
    syncThemeButtons();

    document.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-theme-toggle]");
      if (btn) {
        e.preventDefault();
        toggleTheme();
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  window.StudyMate = {
    toggleTheme: toggleTheme,
    applyTheme: applyTheme,
    toggleSidebar: function () {
      setSidebar(!sidebar || !sidebar.classList.contains("is-open"));
    },
    closeSidebar: closeSidebar
  };
})();
