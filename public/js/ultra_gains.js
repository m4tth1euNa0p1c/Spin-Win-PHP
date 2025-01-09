document.addEventListener("DOMContentLoaded", function () {
    const spinButton = document.getElementById("spin-button");
    const reels = [
        document.getElementById("reel1").querySelector("img"),
        document.getElementById("reel2").querySelector("img"),
        document.getElementById("reel3").querySelector("img"),
        document.getElementById("reel4").querySelector("img"),
        document.getElementById("reel5").querySelector("img"),
        document.getElementById("reel6").querySelector("img"),
        document.getElementById("reel7").querySelector("img"),
        document.getElementById("reel8").querySelector("img"),
        document.getElementById("reel9").querySelector("img"),
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
        fetch('/games/ultra-gains/spin', {
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
            displayResult('Une erreur est survenue lors du spin Ultra Gains.', false);
            // Réactiver le bouton en cas d'erreur
            resetSpinButton();
        });
    });

    /**
     * Anime les reels en fonction des résultats finaux.
     * @param {Array} reels - Tableau des images des reels.
     * @param {Array} results - Tableau des résultats finaux.
     * @param {Function} callback - Fonction à appeler après l'animation.
     */
    function animateReels(reels, results, callback) {
        const spinDuration = 2000; // Durée totale de l'animation en ms

        reels.forEach((reel, index) => {
            const symbols = [
                "diamond.png",
                "gold.png",
                "silver.png",
                "ruby.png",
                "emerald.png",
                "sapphire.png",
                "pearl.png",
                "topaz.png",
                "amethyst.png",
            ];
            const finalSymbol = results[index];
            const finalIndex = symbols.indexOf(finalSymbol);

            if (finalIndex === -1) {
                console.error(`Symbol "${finalSymbol}" non trouvé.`);
                return;
            }

            const numSpins = 5;
            const numSymbols = symbols.length;
            const totalImages = numSpins * numSymbols + finalIndex + 1;

            // Créer une animation d'affichage rapide des symboles
            let currentSpin = 0;
            const spinInterval = setInterval(() => {
                const symbol = symbols[currentSpin % numSymbols];
                reel.src = `/images/${symbol}`;
                currentSpin++;
                if (currentSpin >= totalImages) {
                    clearInterval(spinInterval);
                    reel.src = `/images/${finalSymbol}`;
                }
            }, spinDuration / (numSpins * numSymbols));
        });

        // Appeler le callback après l'animation
        setTimeout(() => {
            callback();
        }, spinDuration);
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
