document.addEventListener("DOMContentLoaded", function () {
    const spinButton = document.getElementById("spin-button");
    const reels = [
        document.getElementById("reel1").querySelector(".reel-container"),
        document.getElementById("reel2").querySelector(".reel-container"),
        document.getElementById("reel3").querySelector(".reel-container"),
    ];
    const currentCoinsElement = document.getElementById("current-coins");
    const resultMessage = document.getElementById("result-message");

    spinButton.addEventListener("click", function () {
        // Désactiver le bouton et afficher l'icône de spinner
        spinButton.disabled = true;
        spinButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> En cours...';

        // Récupérer le jeton CSRF
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfTokenMeta) {
            console.error('CSRF token meta tag not found.');
            displayResult('Erreur de configuration. Veuillez réessayer plus tard.', false);
            resetSpinButton();
            return;
        }
        const csrfToken = csrfTokenMeta.getAttribute('content');

        // Vérifier si le jeton CSRF est présent
        if (!csrfToken) {
            console.error('CSRF token is empty.');
            displayResult('Erreur de configuration. Veuillez réessayer plus tard.', false);
            resetSpinButton();
            return;
        }

        // Faire la requête AJAX pour obtenir le résultat du spin
        fetch('/games/slots/spin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
            },
            body: JSON.stringify({
                csrf_token: csrfToken
            }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur de réponse du serveur: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Server response:", data); // Log pour débogage
            if (data.error) {
                throw new Error(data.error);
            }
            const finalResults = data.results;
            const winAmount = data.win;
            const totalCoins = data.total_coins;
            animateReels(reels, finalResults, () => {
                // Mettre à jour l'affichage des coins
                currentCoinsElement.textContent = totalCoins;
                // Afficher un message de gain si applicable
                if (winAmount > 0) {
                    displayResult(`Félicitations ! Vous avez gagné ${winAmount} EFREICOIN.`, true);
                } else {
                    displayResult('Perdu. Réessayez !', false);
                }
                // Réactiver le bouton et réinitialiser son contenu
                resetSpinButton();
            });
        })
        .catch(error => {
            console.error('Erreur:', error);
            displayResult('Une erreur est survenue lors du spin.', false);
            // Réactiver le bouton en cas d'erreur
            resetSpinButton();
        });
    });

    /**
     * Anime les rouleaux en fonction des résultats finaux.
     * @param {Array} reels - Tableau des conteneurs des rouleaux.
     * @param {Array} results - Tableau des résultats finaux.
     * @param {Function} callback - Fonction à appeler après l'animation.
     */
    function animateReels(reels, results, callback) {
        const spinDuration = 3000; // Durée totale de l'animation en ms

        reels.forEach((reel, index) => {
            const fruits = [
                "apple.png",
                "lemon.png",
                "orange.png",
                "prune.png",
                "melon.png",
                "raisin.png",
                "jackpot.png",
            ];
            const finalFruit = results[index];
            const finalIndex = fruits.indexOf(finalFruit);

            if (finalIndex === -1) {
                console.error(`Fruit "${finalFruit}" non trouvé.`);
                return;
            }

            const numSpins = 5;
            const numFruits = fruits.length;
            const totalImages = numSpins * numFruits + finalIndex + 1;

            reel.innerHTML = "";

            for (let i = 0; i < totalImages; i++) {
                const fruit = fruits[i % numFruits];
                const img = document.createElement("img");
                img.src = `/images/${fruit}`;
                img.alt = fruit.split(".")[0];
                reel.appendChild(img);
            }

            // Réinitialiser les transformations sans transition
            reel.style.transition = 'none';
            reel.style.transform = 'translateY(0px)';
            void reel.offsetWidth; // Forcer le reflow

            const finalTranslateY = (numSpins * numFruits + finalIndex) * 270; // 270px est la hauteur d'une image

            // Appliquer la transition pour animer le défilement
            reel.style.transition = `transform ${spinDuration / 1000}s cubic-bezier(0.25, 1, 0.5, 1)`;
            reel.style.transform = `translateY(-${finalTranslateY}px)`;
        });

        // Réinitialiser les rouleaux après l'animation
        setTimeout(() => {
            reels.forEach((reel, index) => {
                const finalFruit = results[index];
                reel.innerHTML = "";
                const finalImg = document.createElement("img");
                finalImg.src = `/images/${finalFruit}`;
                finalImg.alt = finalFruit.split(".")[0];
                reel.appendChild(finalImg);

                // Réinitialiser le transform sans transition
                reel.style.transition = 'none';
                reel.style.transform = 'translateY(0px)';
            });

            // Appeler le callback pour réactiver le bouton
            callback();
        }, spinDuration + 100); // spinDuration + un petit délai
    }

    /**
     * Affiche un message de résultat sur la page.
     * @param {string} message - Le message à afficher.
     * @param {boolean} isWin - Indique si c'est un gain ou une perte.
     */
    function displayResult(message, isWin) {
        if (isWin) {
            resultMessage.style.color = '#28a745'; // Vert pour les gains
        } else {
            resultMessage.style.color = '#dc3545'; // Rouge pour les pertes
        }
        resultMessage.textContent = message;
    }

    /**
     * Réinitialise l'état du bouton de spin.
     */
    function resetSpinButton() {
        spinButton.disabled = false;
        spinButton.innerHTML = '<i class="fas fa-play"></i> Lancer';
    }
});
