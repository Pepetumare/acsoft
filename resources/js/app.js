import "./bootstrap";
import * as bootstrap from "bootstrap";
import Chart from "chart.js/auto";

window.bootstrap = bootstrap;
window.Chart = Chart;

document.addEventListener("DOMContentLoaded", () => {
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("/service-worker.js", { scope: "/" });
    }

    const navigation = document.querySelector(".navbar");

    const updateNavbar = () => {
        if (!navigation) {
            return;
        }

        navigation.classList.toggle("navbar-scrolled", window.scrollY > 10);
    };

    updateNavbar();

    window.addEventListener("scroll", updateNavbar, {
        passive: true,
    });
    const navbar = document.getElementById("navbarACSoft");

    if (!navbar) {
        return;
    }

    const links = navbar.querySelectorAll(".nav-link");

    links.forEach((link) => {
        link.addEventListener("click", () => {
            if (window.innerWidth >= 992) {
                return;
            }

            const collapse = bootstrap.Collapse.getInstance(navbar);

            if (collapse) {
                collapse.hide();
            }
        });
    });
});
