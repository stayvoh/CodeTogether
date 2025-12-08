<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['code'], $input['language'], $input['problem'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$user = $_SESSION['username'] ?? 'guest';
$code = $input['code'];
$language = $input['language'];
$problem = $input['problem'];

if (!$problem) {
    echo json_encode(['error' => 'Problem data invalid']);
    exit;
}

// AI judge
$apiKey = getenv('API_KEY');
if (!$apiKey) {
    echo json_encode(['error' => 'API key missing']);
    exit;
}

$prompt = <<<EOT
You are a coding challenge judge.  
Problem:
Title: {$problem['title']}
Description: {$problem['description']}
Example Input: {$problem['exampleInput']}
Example Output: {$problem['exampleOutput']}

User submission in {$language}:

{$code}

Return JSON ONLY:
{
  "score": <0-100>,
  "correct": <true|false>,
  "feedback": "string"
}
EOT;

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);

$messages = [
    ["role" => "system", "content" => "You are a strict but fair coding judge."],
    ["role" => "user", "content" => $prompt]
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode([
        "model" => "gpt-5-nano",
        "messages" => $messages
    ])
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$aiReply = $result['choices'][0]['message']['content'] ?? '{}';
$judgement = json_decode($aiReply, true);

if (!$judgement) {
    echo json_encode(['error' => 'AI did not return valid JSON']);
    exit;
}

// Update leaderboard
$leaderboardFile = __DIR__ . '/leaderboard.json';
$leaderboard = file_exists($leaderboardFile) ? json_decode(file_get_contents($leaderboardFile), true) : [];
$leaderboard[$user] = max($leaderboard[$user] ?? 0, $judgement['score'] ?? 0);
arsort($leaderboard);
file_put_contents($leaderboardFile, json_encode($leaderboard, JSON_PRETTY_PRINT));

// Return result + top 3
echo json_encode([
    'score' => $judgement['score'] ?? 0,
    'correct' => $judgement['correct'] ?? false,
    'feedback' => $judgement['feedback'] ?? 'No feedback provided',
    'leaderboard' => array_slice($leaderboard, 0, 3, true)
]);
?>
