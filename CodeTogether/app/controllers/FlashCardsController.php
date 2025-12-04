<?php
declare(strict_types=1);

class FlashCardsController extends Controller
{

   public function performAction(): void
    {
        $subAction = $_GET['do'] ?? 'menu'; 
        // e.g., ?action=cards&do=play

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

            case 'upload':
                $this->upload();
                break;
            case 'save':
                $this->save();
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
    $safeSet = basename($set); // Prevent directory traversal
    $file = '';

    // Check if it's a user-specific set
    if (str_starts_with($set, 'user/') && $user) {
        // Path to user-specific sets
        $file = __DIR__ . "/../public/js/data/users/{$user}/{$safeSet}.json";
    } else {
        // Path to predefined sets (adjusted to your folder)
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

    return $data;
}

     public function create(): void
    {
        $this->renderView('createFlashCards');
    }
     public function upload(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle CSV upload later
            $this->renderView('flashcards/upload_success');
        } else {
            $this->renderView('flashcards/upload');
        }
    }
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
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

        // Sanitize set name to safe filename
        $safeSetName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $setName);

        // Prepare data
        $cards = [];
        for ($i = 0; $i < count($terms); $i++) {
            $cards[] = [
                'id' => $i + 1,
                'term' => trim($terms[$i]),
                'definition' => trim($definitions[$i]),
            ];
        }

        // Make sure user directory exists
        $userDir = __DIR__ . "/../public/js/data/users/{$user}";
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }

        // Save JSON file
        $filePath = "{$userDir}/{$safeSetName}.json";
        $json = json_encode($cards, JSON_PRETTY_PRINT);

        if (file_put_contents($filePath, $json) === false) {
            http_response_code(500);
            echo "Failed to save file";
            exit;
        }

        // Redirect or show success
        header("Location: index.php?action=cards&do=play&set=user/{$safeSetName}");
        exit;
    }


    public function renderView(string $view, array $data = []): void
    {
        parent::renderView($view, $data);
        ;
    }
}
?>