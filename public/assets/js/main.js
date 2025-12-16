/**
 * Main
 */

"use strict";

let menu, animate;
document.addEventListener("DOMContentLoaded", function () {
  // class for ios specific styles
  if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
    document.body.classList.add("ios");
  }
});

(function () {
  // Initialize menu
  //-----------------

  let layoutMenuEl = document.querySelectorAll("#layout-menu");
  layoutMenuEl.forEach(function (element) {
    menu = new Menu(element, {
      orientation: "vertical",
      closeChildren: false,
    });
    // Change parameter to true if you want scroll animation
    window.Helpers.scrollToActive((animate = false));
    window.Helpers.mainMenu = menu;
  });

  // Initialize menu togglers and bind click on each
  let menuToggler = document.querySelectorAll(".layout-menu-toggle");
  menuToggler.forEach((item) => {
    item.addEventListener("click", (event) => {
      event.preventDefault();
      window.Helpers.toggleCollapsed();
    });
  });

  // Display menu toggle (layout-menu-toggle) on hover with delay
  let delay = function (elem, callback) {
    let timeout = null;
    elem.onmouseenter = function () {
      // Set timeout to be a timer which will invoke callback after 300ms (not for small screen)
      if (!Helpers.isSmallScreen()) {
        timeout = setTimeout(callback, 300);
      } else {
        timeout = setTimeout(callback, 0);
      }
    };

    elem.onmouseleave = function () {
      // Clear any timers set to timeout
      document.querySelector(".layout-menu-toggle").classList.remove("d-block");
      clearTimeout(timeout);
    };
  };
  if (document.getElementById("layout-menu")) {
    delay(document.getElementById("layout-menu"), function () {
      // not for small screen
      if (!Helpers.isSmallScreen()) {
        document.querySelector(".layout-menu-toggle").classList.add("d-block");
      }
    });
  }

  // Display in main menu when menu scrolls
  let menuInnerContainer = document.getElementsByClassName("menu-inner"),
    menuInnerShadow = document.getElementsByClassName("menu-inner-shadow")[0];
  if (menuInnerContainer.length > 0 && menuInnerShadow) {
    menuInnerContainer[0].addEventListener("ps-scroll-y", function () {
      if (this.querySelector(".ps__thumb-y").offsetTop) {
        menuInnerShadow.style.display = "block";
      } else {
        menuInnerShadow.style.display = "none";
      }
    });
  }

  // Init helpers & misc
  // --------------------

  // Init BS Tooltip
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Accordion active class
  const accordionActiveFunction = function (e) {
    if (e.type == "show.bs.collapse" || e.type == "show.bs.collapse") {
      e.target.closest(".accordion-item").classList.add("active");
    } else {
      e.target.closest(".accordion-item").classList.remove("active");
    }
  };

  const accordionTriggerList = [].slice.call(
    document.querySelectorAll(".accordion")
  );
  const accordionList = accordionTriggerList.map(function (accordionTriggerEl) {
    accordionTriggerEl.addEventListener(
      "show.bs.collapse",
      accordionActiveFunction
    );
    accordionTriggerEl.addEventListener(
      "hide.bs.collapse",
      accordionActiveFunction
    );
  });

  // Auto update layout based on screen size
  window.Helpers.setAutoUpdate(true);

  // Toggle Password Visibility
  window.Helpers.initPasswordToggle();

  // Speech To Text
  window.Helpers.initSpeechToText();

  // Manage menu expanded/collapsed with templateCustomizer & local storage
  //------------------------------------------------------------------

  // If current layout is horizontal OR current window screen is small (overlay menu) than return from here
  if (window.Helpers.isSmallScreen()) {
    return;
  }

  // If current layout is vertical and current window screen is > small

  // Auto update menu collapsed/expanded based on the themeConfig
  window.Helpers.setCollapsed(true, false);
})();
// Utils
function isMacOS() {
  return /Mac|iPod|iPhone|iPad/.test(navigator.userAgent);
}

// document.addEventListener("DOMContentLoaded", function () {
//   const themeButtons = document.querySelectorAll("[data-bs-theme-value]");
//   const htmlEl = document.documentElement;

//   // Appliquer le thème stocké dans le localStorage (si existant)
//   const storedTheme = localStorage.getItem("bs-theme");
//   if (storedTheme) {
//     htmlEl.setAttribute("data-bs-theme", storedTheme);
//     updateActiveButton(storedTheme);
//   } else {
//     applySystemTheme();
//   }

//   // Gérer le clic sur un bouton
//   themeButtons.forEach((button) => {
//     button.addEventListener("click", () => {
//       const theme = button.getAttribute("data-bs-theme-value");
//       if (theme === "system") {
//         localStorage.removeItem("bs-theme");
//         applySystemTheme();
//       } else {
//         localStorage.setItem("bs-theme", theme);
//         htmlEl.setAttribute("data-bs-theme", theme);
//       }
//       updateActiveButton(theme);
//     });
//   });

//   // Appliquer le thème système si choisi
//   function applySystemTheme() {
//     const isDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
//     htmlEl.setAttribute("data-bs-theme", isDark ? "dark" : "light");
//   }

//   // Mettre à jour les boutons actifs dans le dropdown
//   function updateActiveButton(theme) {
//     themeButtons.forEach((btn) => {
//       const isActive = btn.getAttribute("data-bs-theme-value") === theme;
//       btn.classList.toggle("active", isActive);
//       btn.setAttribute("aria-pressed", isActive.toString());
//     });

//     // Mettre à jour l'icône et le texte visible
//     const icon = document.querySelector(".theme-icon-active");
//     const text = document.getElementById("nav-theme-text");
//     if (theme === "dark") {
//       icon.className = "bx bx-moon icon-base icon-md theme-icon-active";
//       text.textContent = "Dark mode";
//     } else if (theme === "light") {
//       icon.className = "bx bx-sun icon-base icon-md theme-icon-active";
//       text.textContent = "Light mode";
//     } else {
//       icon.className = "bx bx-desktop icon-base icon-md theme-icon-active";
//       text.textContent = "System mode";
//     }
//   }

//   // Réagir aux changements du thème système (si mode "system")
//   window
//     .matchMedia("(prefers-color-scheme: dark)")
//     .addEventListener("change", (e) => {
//       if (!localStorage.getItem("bs-theme")) {
//         applySystemTheme();
//       }
//     });
// });
