// animations.js - For client-side interactive animations

document.addEventListener("DOMContentLoaded", function() {
    // Example: Add a class to trigger an animation on form elements after page load
    const formElements = document.querySelectorAll(".login-form input, .login-form button, .form-wrapper h1, .form-wrapper p");
    formElements.forEach((el, index) => {
        el.style.opacity = "0"; // Start as invisible
        el.style.transform = "translateY(20px)";
        el.style.transition = `opacity 0.5s ease-out ${index * 0.1}s, transform 0.5s ease-out ${index * 0.1}s`;
        setTimeout(() => {
            el.style.opacity = "1";
            el.style.transform = "translateY(0)";
        }, 100 + index * 100); // Stagger the animation start slightly after container fade-in
    });

    // Interactive button animation (example)
    const loginButton = document.querySelector(".btn-login");
    if (loginButton) {
        loginButton.addEventListener("mousemove", function(e) {
            const rect = loginButton.getBoundingClientRect();
            const x = e.clientX - rect.left; 
            const y = e.clientY - rect.top;
            loginButton.style.setProperty("--mouse-x", x + "px");
            loginButton.style.setProperty("--mouse-y", y + "px");
        });
    }

    // Further animations for other pages can be added here, potentially based on page-specific classes or IDs.
});

// General function to add subtle hover effects to interactive elements
function addHoverEffects(selector) {
    const elements = document.querySelectorAll(selector);
    elements.forEach(el => {
        el.addEventListener("mouseenter", () => {
            el.style.transform = "scale(1.03)";
            el.style.transition = "transform 0.2s ease-in-out";
        });
        el.addEventListener("mouseleave", () => {
            el.style.transform = "scale(1)";
        });
    });
}

// Call for common interactive elements like buttons or cards if a global class is used
// addHoverEffects(".interactive-card, .interactive-button");

