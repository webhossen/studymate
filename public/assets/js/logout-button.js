/* ==========================================================================
   StudyMate — animated logout button behaviour
   Adapted from the "Animated logout button" component.

   The button plays the walk-through-the-door animation and only then submits
   the sign-out form, so the confirmation dialog and the animation stay in
   sync: confirm -> animate -> sign out.
   ========================================================================== */
(function () {
  "use strict";

  var STATES = {
    default: {
      "--figure-duration": "100",
      "--transform-figure": "none",
      "--walking-duration": "100",
      "--transform-arm1": "none",
      "--transform-wrist1": "none",
      "--transform-arm2": "none",
      "--transform-wrist2": "none",
      "--transform-leg1": "none",
      "--transform-calf1": "none",
      "--transform-leg2": "none",
      "--transform-calf2": "none"
    },
    hover: {
      "--figure-duration": "100",
      "--transform-figure": "translateX(1.5px)",
      "--walking-duration": "100",
      "--transform-arm1": "rotate(-5deg)",
      "--transform-wrist1": "rotate(-15deg)",
      "--transform-arm2": "rotate(5deg)",
      "--transform-wrist2": "rotate(6deg)",
      "--transform-leg1": "rotate(-10deg)",
      "--transform-calf1": "rotate(5deg)",
      "--transform-leg2": "rotate(20deg)",
      "--transform-calf2": "rotate(-20deg)"
    },
    walking1: {
      "--figure-duration": "300",
      "--transform-figure": "translateX(11px)",
      "--walking-duration": "300",
      "--transform-arm1": "translateX(-4px) translateY(-2px) rotate(120deg)",
      "--transform-wrist1": "rotate(-5deg)",
      "--transform-arm2": "translateX(4px) rotate(-110deg)",
      "--transform-wrist2": "rotate(-5deg)",
      "--transform-leg1": "translateX(-3px) rotate(80deg)",
      "--transform-calf1": "rotate(-30deg)",
      "--transform-leg2": "translateX(4px) rotate(-60deg)",
      "--transform-calf2": "rotate(20deg)"
    },
    walking2: {
      "--figure-duration": "400",
      "--transform-figure": "translateX(17px)",
      "--walking-duration": "300",
      "--transform-arm1": "rotate(60deg)",
      "--transform-wrist1": "rotate(-15deg)",
      "--transform-arm2": "rotate(-45deg)",
      "--transform-wrist2": "rotate(6deg)",
      "--transform-leg1": "rotate(-5deg)",
      "--transform-calf1": "rotate(10deg)",
      "--transform-leg2": "rotate(10deg)",
      "--transform-calf2": "rotate(-20deg)"
    },
    falling1: {
      "--figure-duration": "1600",
      "--walking-duration": "400",
      "--transform-arm1": "rotate(-60deg)",
      "--transform-wrist1": "none",
      "--transform-arm2": "rotate(30deg)",
      "--transform-wrist2": "rotate(120deg)",
      "--transform-leg1": "rotate(-30deg)",
      "--transform-calf1": "rotate(-20deg)",
      "--transform-leg2": "rotate(20deg)"
    },
    falling2: {
      "--walking-duration": "300",
      "--transform-arm1": "rotate(-100deg)",
      "--transform-arm2": "rotate(-60deg)",
      "--transform-wrist2": "rotate(60deg)",
      "--transform-leg1": "rotate(80deg)",
      "--transform-calf1": "rotate(20deg)",
      "--transform-leg2": "rotate(-60deg)"
    },
    falling3: {
      "--walking-duration": "500",
      "--transform-arm1": "rotate(-30deg)",
      "--transform-wrist1": "rotate(40deg)",
      "--transform-arm2": "rotate(50deg)",
      "--transform-wrist2": "none",
      "--transform-leg1": "rotate(-30deg)",
      "--transform-leg2": "rotate(20deg)",
      "--transform-calf2": "none"
    }
  };

  function updateButtonState(button, state) {
    if (!STATES[state]) return;
    button.state = state;
    for (var key in STATES[state]) {
      button.style.setProperty(key, STATES[state][key]);
    }
  }

  /**
   * Play the sequence: walk, slam the door, fall, then hand back.
   */
  function playAnimation(button, onDone) {
    if (button.dataset.animating === "1") return;
    button.dataset.animating = "1";

    button.classList.add("clicked");
    updateButtonState(button, "walking1");

    window.setTimeout(function () {
      button.classList.add("door-slammed");
      updateButtonState(button, "walking2");

      window.setTimeout(function () {
        button.classList.add("falling");
        updateButtonState(button, "falling1");

        window.setTimeout(function () {
          updateButtonState(button, "falling2");

          window.setTimeout(function () {
            updateButtonState(button, "falling3");

            window.setTimeout(function () {
              button.classList.remove("clicked");
              button.classList.remove("door-slammed");
              button.classList.remove("falling");
              updateButtonState(button, "default");
              button.dataset.animating = "0";
              if (typeof onDone === "function") onDone();
            }, 1000);
          }, STATES.falling2["--walking-duration"]);
        }, STATES.falling1["--walking-duration"]);
      }, STATES.walking2["--figure-duration"]);
    }, STATES.walking1["--figure-duration"]);
  }

  /** Submit the sign-out form, bypassing the generic submit confirmation. */
  function submitForm(form) {
    if (!form) return;
    form.setAttribute("data-confirm-bypass", "1");
    if (typeof form.requestSubmit === "function") form.requestSubmit();
    else form.submit();
  }

  /** The hidden template button that gets cloned into the confirmation popup. */
  function templateButton() {
    var tpl = document.getElementById("smLogoutTemplate");
    return tpl ? tpl.querySelector(".logoutButton") : null;
  }

  /**
   * Wire up a single animated logout button.
   * A button inside the confirmation popup plays the animation and then
   * resolves the dialog; a button elsewhere opens the dialog first.
   */
  function initButton(button) {
    if (!button || button.dataset.smWired === "1") return;
    /* The hidden template is only a source for clones — never interactive. */
    if (button.closest("#smLogoutTemplate")) return;
    button.dataset.smWired = "1";
    button.state = "default";

    button.addEventListener("mouseenter", function () {
      if (button.state === "default") updateButtonState(button, "hover");
    });

    button.addEventListener("mouseleave", function () {
      if (button.state === "hover") updateButtonState(button, "default");
    });

    button.addEventListener("click", function (event) {
      event.preventDefault();
      if (button.dataset.animating === "1") return;

      /* Inside the popup: animate, then confirm and sign out. */
      var inDialog = !!button.closest("#smConfirmModal");
      if (inDialog) {
        playAnimation(button, function () {
          if (window.StudyMate && window.StudyMate.confirmResolve) {
            /* "animated" tells the trigger that the animation already ran. */
            window.StudyMate.confirmResolve("animated");
          } else {
            var form2 = button.closest("form");
            if (form2) submitForm(form2);
          }
        });
        return;
      }

      var form = button.closest("form");
      var message = form ? form.getAttribute("data-confirm") : null;

      var start = function () {
        playAnimation(button, function () {
          submitForm(form);
        });
      };

      /* Confirm first when the form asks for it, then animate, then sign out. */
      if (message && window.StudyMate && window.StudyMate.confirm) {
        window.StudyMate.confirm({
          title: form.getAttribute("data-confirm-title") || "Sign out",
          message: message,
          confirmLabel: form.getAttribute("data-confirm-label") || "Sign out",
          tone: form.getAttribute("data-confirm-tone") || "warning",
          actionNode: templateButton()
        }).then(function (ok) {
          if (!ok) return;
          /* The popup button already played the animation — just sign out. */
          if (ok === "animated") { submitForm(form); return; }
          start();
        });
        return;
      }

      start();
    });
  }

  function init() {
    document.querySelectorAll(".logoutButton").forEach(initButton);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  window.StudyMateLogout = {
    initButton: initButton,
    playAnimation: playAnimation,
    templateButton: templateButton
  };
})();
