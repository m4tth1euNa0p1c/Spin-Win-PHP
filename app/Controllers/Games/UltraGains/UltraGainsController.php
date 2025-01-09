<?php

namespace App\Controllers\Games\UltraGains;

use App\Core\Controller;
use App\Models\User;
use App\Models\Transaction;
use PDO;

class UltraGainsController extends Controller
{
    private $userModel;
    private $transactionModel;

    private $symbolWinMapping = [
        'diamond.png'  => 1000,
        'gold.png'     => 500, 
        'silver.png'   => 50,
        'ruby.png'     => 200,
        'emerald.png'  => 250,
        'sapphire.png' => 300,
        'pearl.png'    => 70,
        'topaz.png'    => 90,
        'amethyst.png' => 100,
    ];

    public function __construct($twig, PDO $pdo)
    {
        parent::__construct($twig, $pdo);
        $this->userModel = new User();
        $this->transactionModel = new Transaction();
    }

    public function ultraGains()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login', ['error' => 'not_logged_for_play']);
        }

        $csrfToken = $this->generateCsrfToken();

        $user = User::findById($_SESSION['user_id'], $this->pdo);
        if (!$user) {
            return $this->redirect('/login', ['error' => 'user_not_found']);
        }

        error_log("Displaying Ultra Gains slot machine page. CSRF Token: " . $csrfToken);

        echo $this->twig->render('Games/ultra_gains.html.twig', [
            'title' => 'Ultra Gains',
            'includeNavbarAndFooter' => true,
            'csrf_token' => $csrfToken,
            'coins' => $user->coins, // Passer les coins à la vue
        ]);
    }

    public function spin()
    {
        header('Content-Type: application/json');

        try {
            if (!isset($_SESSION['user_id'])) {
                error_log("Spin attempt by unauthorized user.");
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            $userId = $_SESSION['user_id'];

            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Invalid JSON input: " . json_last_error_msg());
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                exit;
            }

            error_log("Ultra Gains Spin request data: " . $input);

            $csrfToken = $data['csrf_token'] ?? '';

            if (!$this->verifyCsrfToken($csrfToken)) {
                error_log("Invalid CSRF token: " . $csrfToken);
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }

            $user = User::findById($userId, $this->pdo);
            if (!$user) {
                error_log("User not found for ID: " . $userId);
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit;
            }

            $spinCost = 2;

            if ($user->coins < $spinCost) {
                http_response_code(400);
                echo json_encode(['error' => 'Insufficient coins']);
                exit;
            }
            

            $deducted = $user->deductCoins($this->pdo, $spinCost);
            if (!$deducted) {
                error_log("Failed to deduct coins for user ID: " . $userId);
                http_response_code(500);
                echo json_encode(['error' => 'Failed to deduct coins']);
                exit;
            }

            $this->transactionModel->save($this->pdo, $userId, 'withdrawal', $spinCost);

            $symbols = array_keys($this->symbolWinMapping);
            $results = [];

            for ($i = 0; $i < 9; $i++) {
                $selected = $symbols[array_rand($symbols)];
                $results[] = $selected;
                error_log("Ultra Gains Spin result " . ($i + 1) . ": " . $selected);
            }

            $winAmount = $this->calculateWin($results);

            if ($winAmount > 0) {
                $added = $user->addCoins($this->pdo, $winAmount);
                if ($added) {
                    // Enregistrer la transaction de gain
                    $this->transactionModel->save($this->pdo, $userId, 'win', $winAmount);
                } else {
                    error_log("Failed to add coins for user ID: " . $userId);
                }
            }

            $updatedUser = User::findById($userId, $this->pdo);
            if (!$updatedUser) {
                error_log("Failed to retrieve updated user data for ID: " . $userId);
                http_response_code(500);
                echo json_encode(['error' => 'Failed to retrieve updated user data']);
                exit;
            }

            echo json_encode([
                'results' => $results,
                'win' => $winAmount,
                'total_coins' => $updatedUser->coins
            ]);
            exit;

        } catch (\Exception $e) {
            error_log("Error during Ultra Gains spin: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Une erreur est survenue lors du spin Ultra Gains.']);
            exit;
        }
    }

    /**
     *
     * @param array $results
     * @return int
     */
    private function calculateWin(array $results): int
    {
        // Logique de gain pour Ultra Gains
        // 1. Trois symboles identiques dans une ligne
        // 2. Trois symboles identiques dans une colonne
        // 3. Toute combinaison spéciale (par exemple, diagonale)
        // 4. Bonus pour deux symboles identiques
        // 5. Bonus pour toutes les cases identiques

        // Définir les lignes, colonnes et diagonales
        $lines = [
            // Lignes
            [0, 1, 2],
            [3, 4, 5],
            [6, 7, 8],
            // Colonnes
            [0, 3, 6],
            [1, 4, 7],
            [2, 5, 8],
            // Diagonales
            [0, 4, 8],
            [2, 4, 6],
        ];

        $winAmount = 0;
        $jackpotWon = false; // Pour éviter les doublons de jackpot

        foreach ($lines as $line) {
            // Extraire les symboles pour la ligne actuelle
            $lineSymbols = [
                $results[$line[0]],
                $results[$line[1]],
                $results[$line[2]],
            ];

            if (count(array_unique($lineSymbols)) === 1) {
                $symbol = $lineSymbols[0];

                if (isset($this->symbolWinMapping[$symbol])) {
                    if ($symbol === 'diamond.png') {
                        if (!$jackpotWon) {
                            $winAmount += $this->symbolWinMapping[$symbol];
                            $jackpotWon = true;
                        }
                    } else {
                        $winAmount += $this->symbolWinMapping[$symbol];
                    }
                }
            }
        }

        $symbolCounts = array_count_values($results);
        foreach ($symbolCounts as $symbol => $count) {
            if ($count === 2 && isset($this->symbolWinMapping[$symbol]) && $symbol !== 'diamond.png') {
                $winAmount += 2;
            }
        }

        $uniqueSymbols = array_unique($results);
        foreach ($uniqueSymbols as $symbol) {
            $symbolCount = array_count_values($results)[$symbol];
            if ($symbolCount >= 3 && isset($this->symbolWinMapping[$symbol])) {
                $winAmount += $this->symbolWinMapping[$symbol] * $symbolCount;
            }
        }

        if (count(array_unique($results)) === 1) {
            $winAmount += 200;
        }

        return $winAmount;
    }
}
