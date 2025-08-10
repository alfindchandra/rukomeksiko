import "./bootstrap";

document.addEventListener("DOMContentLoaded", function () {
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");

    mobileMenuButton.addEventListener("click", function () {
        mobileMenu.classList.toggle("hidden");
    });

    // Mobile dropdown functionality
    const mobileDropdowns = document.querySelectorAll(".mobile-dropdown");

    mobileDropdowns.forEach((dropdown) => {
        const button = dropdown.querySelector(".mobile-dropdown-button");

        button.addEventListener("click", function () {
            // Close other dropdowns
            mobileDropdowns.forEach((otherDropdown) => {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove("active");
                }
            });

            // Toggle current dropdown
            dropdown.classList.toggle("active");
        });
    });

    // Close mobile menu when clicking outside
    document.addEventListener("click", function (event) {
        if (!event.target.closest("nav")) {
            mobileMenu.classList.add("hidden");
            mobileDropdowns.forEach((dropdown) => {
                dropdown.classList.remove("active");
            });
        }
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        });
    });
});
