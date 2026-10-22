/* ==========================================================================
   StudyMate — toasts and confirmation dialogs
   Exposed as window.StudyMate.toast() and window.StudyMate.confirm()
   ========================================================================== */
(function () {
  "use strict";

  var ICONS = { success: "✓", error: "✕", warning: "!", info: "i" };
  var TITLES = {
    success: "Success",
    error: "Error",
    warning: "Warning",
    info: "Heads up"
  };
  var DEFAULT_TIMEOUT = 5000;

  function stack() {
    var el = document.getElementById("smToastStack");
    if (!el) {
      el = document.createElement("div");
      el.id = "smToastStack";
      el.className = "sm-toast-stack";
      el.setAttribute("role", "region");
      el.setAttribute("aria-live", "polite");
      el.setAttribute("aria-atomic", "false");
      document.body.appendChild(el);
    }
    return el;
  }

  /**
   * Show a toast notification.
   * toast("Saved", { type: "success", title: "Done", timeout: 4000 })
   */
  function toast(message, options) {
    options = options || {};
    var type = ICONS[options.type] ? options.type : "info";
    var timeout = typeof options.timeout === "number" ? options.timeout : DEFAULT_TIMEOUT;

    var el = document.createElement("div");
    el.className = "sm-toast sm-toast-" + type;
    el.setAttribute("role", type === "error" ? "alert" : "status");

    var icon = document.createElement("span");
    icon.className = "sm-toast-icon";
    icon.setAttribute("aria-hidden", "true");
    icon.textContent = ICONS[type];

    var content = document.createElement("div");
    content.className = "sm-toast-content";

    var title = document.createElement("p");
    title.className = "sm-toast-title";
    title.textContent = options.title || TITLES[type];
    content.appendChild(title);

    var body = document.createElement("p");
    body.className = "sm-toast-body";
    body.textContent = message == null ? "" : String(message);
    content.appendChild(body);

    var close = document.createElement("button");
    close.type = "button";
    close.className = "sm-toast-close";
    close.setAttribute("aria-label", "Dismiss notification");
    close.textContent = "×";

    el.appendChild(icon);
    el.appendChild(content);
    el.appendChild(close);

    if (timeout > 0) {
      var bar = document.createElement("span");
      bar.className = "sm-toast-progress";
      bar.style.animationDuration = timeout + "ms";
      el.appendChild(bar);
    }

    var removed = false;
    var timer = null;
    var remaining = timeout;
    var startedAt = 0;

    function dismiss() {
      if (removed) return;
      removed = true;
      if (timer) clearTimeout(timer);
      el.classList.add("is-leaving");
      window.setTimeout(function () {
        if (el.parentNode) el.parentNode.removeChild(el);
      }, 220);
    }

    function startTimer() {
      if (timeout <= 0) return;
      startedAt = Date.now();
      timer = window.setTimeout(dismiss, remaining);
    }

    function pauseTimer() {
      if (timer) { clearTimeout(timer); timer = null; }
      remaining = remaining - (Date.now() - startedAt);
    }

    close.addEventListener("click", dismiss);
    el.addEventListener("mouseenter", pauseTimer);
    el.addEventListener("mouseleave", function () {
      if (!removed && remaining > 0) { remaining = Math.max(remaining, 1200); startTimer(); }
    });

    stack().appendChild(el);
    startTimer();

    /* Keep the stack from growing without bound. */
    var all = stack().querySelectorAll(".sm-toast");
    if (all.length > 5) all[0].click();

    return { dismiss: dismiss };
  }

  /* ---------------- Confirmation dialog ---------------- */

  var pendingResolve = null;
  var pendingRestore = null;

  function confirmModal() {
    var el = document.getElementById("smConfirmModal");
    if (!el) return null;
    return window.bootstrap && window.bootstrap.Modal
      ? window.bootstrap.Modal.getOrCreateInstance(el)
      : null;
  }

  /**
   * Ask the user to confirm an action.
   * confirm({ title, message, confirmLabel, tone }) -> Promise<boolean>
   */
  function confirmAction(options) {
    options = options || {};

    return new Promise(function (resolve) {
      var el = document.getElementById("smConfirmModal");
      var modal = confirmModal();
      if (!el || !modal) {
        /* No dialog available — fall back to the native prompt. */
        resolve(window.confirm(options.message || "Are you sure?"));
        return;
      }

      var iconEl = document.getElementById("smConfirmIcon");
      var titleEl = document.getElementById("smConfirmTitle");
      var bodyEl = document.getElementById("smConfirmBody");
      var okEl = document.getElementById("smConfirmOk");
      var cancelEl = document.getElementById("smConfirmCancel");
      var actionSlot = document.getElementById("smConfirmAction");

      var tone = options.tone || "primary";
      iconEl.className = "sm-confirm-icon" + (tone === "primary" ? "" : " is-" + tone);
      iconEl.textContent = options.icon || (tone === "danger" ? "!" : tone === "warning" ? "!" : tone === "success" ? "✓" : "?");
      titleEl.textContent = options.title || "Please confirm";
      /* The message may contain markup from the page template. */
      bodyEl.innerHTML = options.message || "Are you sure you want to continue?";
      cancelEl.textContent = options.cancelLabel || "Cancel";

      /*
       * A custom action replaces the default Confirm button. The supplied node
       * is cloned into the dialog and is responsible for resolving the
       * promise (see StudyMate.confirmResolve).
       */
      if (actionSlot) {
        actionSlot.innerHTML = "";
        actionSlot.classList.add("d-none");
        okEl.classList.remove("d-none");
        okEl.textContent = options.confirmLabel || "Confirm";
        okEl.className = "btn " + (tone === "danger" ? "btn-danger-soft" : "btn-sm-navy");

        if (options.actionNode) {
          var clone = options.actionNode.cloneNode(true);
          clone.removeAttribute("id");
          /* Allow the clone to be wired up independently of the template. */
          if (clone.dataset) delete clone.dataset.smWired;
          clone.classList.add("logoutButton--dialog");
          actionSlot.appendChild(clone);
          actionSlot.classList.remove("d-none");
          okEl.classList.add("d-none");
          if (window.StudyMateLogout && window.StudyMateLogout.initButton) {
            window.StudyMateLogout.initButton(clone);
          }
        }
      }

      pendingResolve = resolve;
      pendingRestore = null;

      /*
       * Bootstrap cannot stack two modals cleanly, so any modal that is
       * already open is hidden first and restored once the answer is in.
       */
      var open = document.querySelector(".modal.show");
      if (open && open.id !== "smConfirmModal") {
        pendingRestore = open;
        var onHidden = function () {
          open.removeEventListener("hidden.bs.modal", onHidden);
          modal.show();
        };
        open.addEventListener("hidden.bs.modal", onHidden);
        var instance = window.bootstrap.Modal.getInstance(open);
        if (instance) instance.hide();
        else modal.show();
        return;
      }

      modal.show();
    });
  }

  function settleConfirm(result) {
    if (typeof pendingResolve === "function") {
      var fn = pendingResolve;
      pendingResolve = null;
      fn(result);
    }

    /* Close the dialog as soon as the answer is in. */
    var modalEl = document.getElementById("smConfirmModal");
    if (modalEl && modalEl.classList.contains("show") && window.bootstrap && window.bootstrap.Modal) {
      window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
    }

    /* Restore the default Confirm button for the next dialog. */
    var ok = document.getElementById("smConfirmOk");
    var slot = document.getElementById("smConfirmAction");
    if (ok) ok.classList.remove("d-none");
    if (slot) { slot.innerHTML = ""; slot.classList.add("d-none"); }
    if (pendingRestore) {
      var el = pendingRestore;
      pendingRestore = null;
      if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
      }
    }
  }

  function initConfirmDialog() {
    var el = document.getElementById("smConfirmModal");
    if (!el) return;

    var ok = document.getElementById("smConfirmOk");
    var cancel = document.getElementById("smConfirmCancel");

    ok.addEventListener("click", function () {
      var modal = confirmModal();
      if (modal) modal.hide();
      settleConfirm(true);
    });

    cancel.addEventListener("click", function () {
      var modal = confirmModal();
      if (modal) modal.hide();
      settleConfirm(false);
    });

    /* Closing with the X or the backdrop counts as a cancellation. */
    el.addEventListener("hidden.bs.modal", function () { settleConfirm(false); });
  }

  function escapeHtml(value) {
    return String(value == null ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  /**
   * Resolve the message for a confirm action, substituting the currently
   * selected option when the template contains a data-confirm-value marker.
   * The value comes from the control itself, or from the control named by
   * data-confirm-source when the confirmation is attached to a form.
   */
  function resolveMessage(field, template) {
    if (!field || !template) return template || "";

    var source = null;
    if (field.options) {
      source = field;
    } else {
      var name = field.getAttribute ? field.getAttribute("data-confirm-source") : null;
      if (name && field.querySelector) {
        source = field.querySelector('[name="' + name + '"]') || document.getElementById(name);
      }
    }
    if (!source) return template;

    var label = source.options && source.options[source.selectedIndex]
      ? source.options[source.selectedIndex].textContent.trim()
      : source.value;

    return template.replace(/<span data-confirm-value>[\s\S]*?<\/span>/, escapeHtml(label));
  }

  /* ---------------- Declarative confirmations ---------------- */

  function initDeclarativeConfirms() {
    /* Forms: ask before submitting. */
    document.addEventListener("submit", function (e) {
      var form = e.target;
      if (!(form instanceof HTMLFormElement)) return;
      var message = form.getAttribute("data-confirm");
      if (!message) return;
      if (form.getAttribute("data-confirm-bypass") === "1") {
        form.removeAttribute("data-confirm-bypass");
        return;
      }
      e.preventDefault();
      confirmAction({
        title: form.getAttribute("data-confirm-title") || "Are you sure?",
        message: resolveMessage(form, message),
        confirmLabel: form.getAttribute("data-confirm-label") || "Confirm",
        tone: form.getAttribute("data-confirm-tone") || "warning"
      }).then(function (ok) {
        if (!ok) return;
        form.setAttribute("data-confirm-bypass", "1");
        if (typeof form.requestSubmit === "function") form.requestSubmit();
        else form.submit();
      });
    });

    /* Links: ask before navigating. */
    document.addEventListener("click", function (e) {
      var link = e.target.closest("a[data-confirm]");
      if (!link) return;
      e.preventDefault();
      confirmAction({
        title: link.getAttribute("data-confirm-title") || "Are you sure?",
        message: link.getAttribute("data-confirm"),
        confirmLabel: link.getAttribute("data-confirm-label") || "Continue",
        tone: link.getAttribute("data-confirm-tone") || "warning"
      }).then(function (ok) {
        if (ok) window.location.href = link.href;
      });
    });

    /* Selects that submit their form on change need a confirmation too. */
    document.addEventListener("change", function (e) {
      var field = e.target;
      if (!(field instanceof HTMLElement)) return;
      var message = field.getAttribute("data-confirm");
      var autosubmit = field.getAttribute("data-sm-autosubmit") !== null;
      if (!message && !autosubmit) return;

      var form = field.form;
      if (!form) return;

      /* The value to restore when the user cancels — captured before the change. */
      var previous = field.getAttribute("data-confirm-previous");
      if (previous === null) previous = field.value;

      /* Nothing to confirm — submit straight away. */
      if (!message) {
        form.setAttribute("data-confirm-bypass", "1");
        if (typeof form.requestSubmit === "function") form.requestSubmit();
        else form.submit();
        return;
      }

      e.preventDefault();
      e.stopPropagation();

      confirmAction({
        title: field.getAttribute("data-confirm-title") || "Are you sure?",
        message: resolveMessage(field, message),
        confirmLabel: field.getAttribute("data-confirm-label") || "Confirm",
        tone: field.getAttribute("data-confirm-tone") || "warning"
      }).then(function (ok) {
        if (ok) {
          form.setAttribute("data-confirm-bypass", "1");
          if (typeof form.requestSubmit === "function") form.requestSubmit();
          else form.submit();
        } else {
          field.value = previous;
        }
      });
    });
  }

  /* ---------------- Flash messages ---------------- */

  function renderFlashes() {
    var flashes = window.StudyMateFlash || [];
    if (!Array.isArray(flashes)) return;
    flashes.forEach(function (f) {
      if (!f || !f.message) return;
      toast(f.message, { type: f.type || "info", title: f.title || undefined });
    });
    window.StudyMateFlash = [];
  }

  function init() {
    initConfirmDialog();
    initDeclarativeConfirms();
    /* Let the layout paint before the toasts animate in. */
    window.setTimeout(renderFlashes, 120);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  window.StudyMate = window.StudyMate || {};
  window.StudyMate.toast = toast;
  window.StudyMate.confirm = confirmAction;
  /* Lets a custom dialog action resolve the pending confirmation. */
  window.StudyMate.confirmResolve = settleConfirm;
})();
