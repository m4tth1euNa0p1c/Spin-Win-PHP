<?php

namespace App\Controllers\Games\Slot;

use App\Core\Controller;
use App\Models\User;
use App\Models\Transaction;
use PDO;

class SlotGamesController extends Controller
{
    private $userModel;
    private $transactionModel;

    private $symbolWinMapping = [
        'apple.png'    => 20,  // Gain standard
        'lemon.png'    => 30,  // Gain standard
        'orange.png'   => 50,  // Gain standard
        'prune.png'    => 100, // Gain élevé
        'melon.png'    => 100, // Gain élevé
        'raisin.png'   => 100, // Gain élevé
        'jackpot.png'  => 500, // Jackpot
    ];

    public function __construct($twig, PDO $pdo)
    {
        parent::__construct($twig, $pdo);
        $this->userModel = new User();
        $this->transactionModel = new Transaction();
    }

    public function slots()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login', ['error' => 'not_logged_for_play']);
        }

        $csrfToken = $this->generateCsrfToken();

        $user = User::findById($_SESSION['user_id'], $this->pdo);
        if (!$user) {
            return $this->redirect('/login', ['error' => 'user_not_found']);
        }

        error_log("Displaying slot machine page. CSRF Token: " . $csrfToken);

        echo $this->twig->render('Games/slots.html.twig', [
            'title' => 'Machine à Sous',
            'includeNavbarAndFooter' => true,
            'csrf_token' => $csrfToken,
            'coins' => $user->coins,
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

            error_log("Spin request data: " . $input);

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

            $spinCost = 1;

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

            $fruits = array_keys($this->symbolWinMapping); // Utiliser les symboles définis dans le mapping
            $results = [];

            for ($i = 0; $i < 3; $i++) {
                $selected = $fruits[array_rand($fruits)];
                $results[] = $selected;
                error_log("Spin result " . ($i + 1) . ": " . $selected);
            }

            $winAmount = $this->calculateWin($results);

            if ($winAmount > 0) {
                $added = $user->addCoins($this->pdo, $winAmount);
                if ($added) {
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
            error_log("Error during spin: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Une erreur est survenue lors du spin.']);
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
        $lines = [
            [0, 1, 2], // Ligne unique
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
                    if ($symbol === 'jackpot.png') {
                        if (!$jackpotWon) {
                            $winAmount += $this->symbolWinMapping[$symbol]; // Ajouter le gain du jackpot
                            $jackpotWon = true; // Marquer le jackpot comme gagné
                        }
                    } else {
                        $winAmount += $this->symbolWinMapping[$symbol]; // Ajouter le gain correspondant
                    }
                }
            }
        }

        $symbolCounts = array_count_values($results);
        foreach ($symbolCounts as $symbol => $count) {
            if ($count === 2 && isset($this->symbolWinMapping[$symbol]) && $symbol !== 'jackpot.png') {
                $winAmount += 3;
            }
        }

        if (count(array_unique($results)) === 1) {
            $winAmount += 200;
        }

        return $winAmount;
    }
}
