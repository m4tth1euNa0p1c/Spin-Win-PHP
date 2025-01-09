// promotions.js

document.addEventListener("DOMContentLoaded", () => {
    // Exemple : Animer le titre en bougeant légèrement de gauche à droite façon “marquee”
    const titleElement = document.querySelector(".animated-text");
    if (titleElement) {
        // On applique une petite animation “casino” sur le texte
        let direction = 1;
        let position = 0;

        const animateTitle = () => {
            position += 0.5 * direction;
            if (position > 10 || position < -10) {
                direction *= -1;
            }
            // Appliquer un léger décalage
            titleElement.style.transform = `translateX(${position}px)`;
            requestAnimationFrame(animateTitle);
        };
        requestAnimationFrame(animateTitle);
    }
    
    // Exemple : Faire scintiller les icônes de la liste
    const icons = document.querySelectorAll(".promotions-list li i");
    icons.forEach(icon => {
        icon.addEventListener("mouseover", () => {
            icon.classList.add("fa-beat"); // Classe FontAwesome v6 pour animation
        });
        icon.addEventListener("mouseout", () => {
            icon.classList.remove("fa-beat");
        });
    });
});
