<?php
declare(strict_types=1);

class FlashCardsController extends Controller
{

   public function performAction(): void
    {
        $subAction = $_GET['do'] ?? 'menu'; 

        switch ($subAction) {
            case 'menu':
                $this->showMenu();
                break;

            case 'play':
                $this->play();
                break;

            case 'create':
                $this->create();
                break;

            case 'import':
                $this->import();
                break;

            case 'save':
                $this->save();
                break;

            case 'delete':
                $this->deleteSet();
                break;

            default:
                $this->showMenu();
                break;
                
        }
    }

   public function showMenu(): void
    {
    $user = $_SESSION['usercreds']['username'] ?? null;
    $customSets = [];

    if ($user) {
        $userDir = __DIR__ . "/../public/js/data/users/{$user}";

        if (is_dir($userDir)) {
            $files = glob($userDir . "/*.json");

            foreach ($files as $file) {
                $customSets[] = basename($file, ".json");
            }
        }
    }

    $this->renderView('FlashCardsMenu', [
        'customSets' => $customSets
    ]);
    }


    public function play():void
    {
        $set = $_GET['set'] ?? 'default';

         if ($this->isJsonRequest()) {
            $cards = $this->loadSet($set);
            header('Content-Type: application/json');
            echo json_encode($cards);
            exit;
        }
        // Otherwise render the view
        $this->renderView('cards', [
            'set' => $set
        ]);
    }
        
   private function isJsonRequest(): bool
    {
        return isset($_SERVER['HTTP_ACCEPT']) &&
            str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json');
    }

    private function loadSet(string $set): array
    {
        $user = $_SESSION['usercreds']['username'] ?? null;

        // Prevent directory traversal
        $safeSet = basename($set);
        $file    = '';

        // User-specific sets
        if (str_starts_with($set, 'user/') && $user) {
            $file = __DIR__ . "/../public/js/data/users/{$user}/{$safeSet}.json";
        } else {
            // Predefined/global sets
            $file = __DIR__ . "/../public/js/data/predefined/{$safeSet}.json";
        }

        if (!file_exists($file)) {
            error_log("[FlashCards] File not found: $file");
            return [];
        }

        $json = file_get_contents($file);
        if ($json === false) {
            error_log("[FlashCards] Could not read file: $file");
            return [];
        }

        $data = json_decode($json, true);
        if ($data === null) {
            error_log("[FlashCards] Invalid JSON in $file");
            return [];
        }

        // --- 1. Unwrap "cards" wrapper if present (your import format) ---
        // Handles: { "name": "...", "owner": "...", "cards": [ {front,back}, ... ] }
        $cards = $data;
        if (isset($data['cards']) && is_array($data['cards'])) {
            $cards = $data['cards'];
        }

        if (!is_array($cards)) {
            return [];
        }

        // --- 2. Normalize from {front,back} to {id,term,definition} if needed ---
        // Manual save() already writes {id, term, definition}, so we only
        // transform when we see 'front'/'back' but no 'term'.
        if (!empty($cards)
            && isset($cards[0]['front'], $cards[0]['back'])
            && !isset($cards[0]['term'])
        ) {
            $normalized = [];
            $id = 1;

            foreach ($cards as $c) {
                $front = trim((string)($c['front'] ?? ''));
                $back  = trim((string)($c['back'] ?? ''));

                if ($front === '' && $back === '') {
                    continue;
                }

                $normalized[] = [
                    'id'         => $id++,
                    'term'       => $front,
                    'definition' => $back,
                ];
            }

            $cards = $normalized;
        }

        // At this point $cards is the same shape as your manually-created sets
        return $cards;
    }


    public function create(): void
    {
        $this->renderView('createFlashCards');
    }

    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->importFromFile();
                } else {
                    $this->renderView('importFlashCards');
                }
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Invalid request method";
            exit;
        }
       
        $user = $_SESSION['usercreds']['username'] ?? null;
        if (!$user) {
            http_response_code(403);
            echo "You must be logged in to save sets";
            exit;
        }

        $setName = trim($_POST['set_name'] ?? '');
        $terms = $_POST['terms'] ?? [];
        $definitions = $_POST['definitions'] ?? [];

        if (empty($setName) || empty($terms) || empty($definitions) || count($terms) !== count($definitions)) {
            http_response_code(400);
            echo "Invalid data submitted";
            exit;
        }

        $safeSetName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $setName);

        $cards = [];
        for ($i = 0; $i < count($terms); $i++) {
            $cards[] = [
                'id' => $i + 1,
                'term' => trim($terms[$i]),
                'definition' => trim($definitions[$i]),
            ];
        }

        $userDir = __DIR__ . "/../public/js/data/users/{$user}";
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }

        $filePath = "{$userDir}/{$safeSetName}.json";
        $json = json_encode($cards, JSON_PRETTY_PRINT);

        if (file_put_contents($filePath, $json) === false) {
            http_response_code(500);
            echo "Failed to save file";
            exit;
        }

        header("Location: index.php?action=cards&do=play&set=user/{$safeSetName}");
        exit;
    }


    public function renderView(string $view, array $data = []): void
    {
        parent::renderView($view, $data);
        ;
    }

    private function deleteSet(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Invalid request method';
        exit;
    }

    $user = $_SESSION['usercreds']['username'] ?? null;
    if (!$user) {
        http_response_code(403);
        echo 'You must be logged in to delete sets';
        exit;
    }

    // Name of the set (filename without .json)
    $set = trim($_POST['set'] ?? '');
    if ($set === '') {
        http_response_code(400);
        echo 'Missing set name';
        exit;
    }

    // Prevent directory traversal
    $safeSet = basename($set);

    $userDir = __DIR__ . "/../public/js/data/users/{$user}";
    $file    = "{$userDir}/{$safeSet}.json";

    if (file_exists($file)) {
        unlink($file);
    }

    // Back to the flashcards menu
    header('Location: index.php?action=cards&do=menu');
    exit;
}


    private function importFromFile(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Invalid request method';
        exit;
    }

    // Use the same user logic as save()
    $user = $_SESSION['usercreds']['username'] ?? null;
    if (!$user) {
        http_response_code(403);
        echo 'You must be logged in to import sets';
        exit;
    }

    if (empty($_FILES['flashcard_file']['tmp_name']) || empty($_POST['set_name'])) {
        http_response_code(400);
        echo 'Missing file or set name';
        exit;
    }

    $file        = $_FILES['flashcard_file'];
    $setName     = trim($_POST['set_name']);
    $safeSetName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $setName);

    // basic file checks
    if ($file['size'] > 2 * 1024 * 1024) {
        http_response_code(400);
        echo 'File too large.';
        exit;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo 'Upload error.';
        exit;
    }

    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $tmpPath = $file['tmp_name'];

    // parse file into ['front' => ..., 'back' => ...]
    $cardsRaw = [];
    if ($ext === 'csv') {
        $cardsRaw = $this->parseCsvFile($tmpPath);
    } elseif ($ext === 'txt') {
        $cardsRaw = $this->parseTxtFile($tmpPath);
    } else {
        http_response_code(400);
        echo 'Unsupported file type. Use .csv or .txt';
        exit;
    }

    if (empty($cardsRaw)) {
        http_response_code(400);
        echo 'No valid flashcards found in file.';
        exit;
    }

    // Normalize to the same structure as save():
    // [ {id, term, definition}, ... ]
    $cards = [];
    $id    = 1;
    foreach ($cardsRaw as $c) {
        $front = trim((string)($c['front'] ?? ''));
        $back  = trim((string)($c['back'] ?? ''));

        if ($front === '' && $back === '') {
            continue;
        }

        $cards[] = [
            'id'         => $id++,
            'term'       => $front,
            'definition' => $back,
        ];
    }

    if (empty($cards)) {
        http_response_code(400);
        echo 'No usable cards after parsing.';
        exit;
    }

    // Save in the SAME place as save():
    $userDir = __DIR__ . "/../public/js/data/users/{$user}";
    if (!is_dir($userDir)) {
        mkdir($userDir, 0755, true);
    }

    $filePath = "{$userDir}/{$safeSetName}.json";
    $json     = json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if (file_put_contents($filePath, $json) === false) {
        http_response_code(500);
        echo 'Failed to save imported set';
        exit;
    }

    // redirect to play that new set
    header("Location: index.php?action=cards&do=play&set=user/{$safeSetName}");
    exit;
}


    private function parseCsvFile(string $path): array
    {
        $cards = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $first = true;
            while (($row = fgetcsv($handle)) !== false) {
                if ($first && isset($row[0]) && stripos($row[0], 'front') !== false) {
                    $first = false;
                    continue;
                }
                $first = false;
                if (count($row) < 2) {
                    continue;
                }
                $front = trim($row[0]);
                $back  = trim($row[1]);
                if ($front === '' && $back === '') {
                    continue;
                }
                $cards[] = [
                    'front' => $front,
                    'back'  => $back,
                ];
            }
            fclose($handle);
        }
        return $cards;
    }

    private function parseTxtFile(string $path): array
    {
        $cards = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Support "front | back" or tab-separated
            if (strpos($line, '|') !== false) {
                [$front, $back] = array_map('trim', explode('|', $line, 2));
            } elseif (strpos($line, "\t") !== false) {
                [$front, $back] = array_map('trim', explode("\t", $line, 2));
            } else {
                $front = $line;
                $back  = '';
            }

            if ($front === '' && $back === '') {
                continue;
            }

            $cards[] = [
                'front' => $front,
                'back'  => $back,
            ];
        }

        return $cards;
    }



}
?>