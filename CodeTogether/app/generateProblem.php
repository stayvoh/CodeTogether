<?php
header('Content-Type: application/json');

$apiKey = getenv('API_KEY');
if (!$apiKey) {
    echo json_encode(['error' => 'API key missing']);
    exit;
}

// File to store today's problem
$filename = __DIR__ . '/dailyProblem.json';
$today = date('Y-m-d');

// Check if file exists and is from today
if (file_exists($filename)) {
    $data = json_decode(file_get_contents($filename), true);
    if ($data && ($data['date'] ?? '') === $today) {
        // Return today's problem
        echo json_encode($data['problem']);
        exit;
    }
}

// If we reach here, we need to generate a new problem
$language = $_GET['language'] ?? 'javascript';
$difficulty = $_GET['difficulty'] ?? 'beginner';

$prompt = <<<EOT
Generate a daily coding challenge in $language for $difficulty users. 
Provide ONLY JSON like:
{
    "title": "...",
    "description": "...",
    "exampleInput": "...",
    "exampleOutput": "..."
}
Do NOT include any explanations or extra text. No backticks, just raw JSON.
EOT;

// Call OpenAI API
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);

$messages = [
    ["role" => "system", "content" => "You are a coding challenge generator."],
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'AI API error', 'response' => $response]);
    exit;
}

// Parse AI response safely
$aiData = json_decode($response, true);
$problemJson = $aiData['choices'][0]['message']['content'] ?? '';
$problemJson = trim($problemJson, "\"' \n");

// Decode JSON
$problem = json_decode($problemJson, true);
if (!$problem) {
    echo json_encode(['error' => 'Failed to parse problem JSON', 'raw' => $problemJson]);
    exit;
}

// Save to file with today's date
file_put_contents($filename, json_encode([
    'date' => $today,
    'problem' => $problem
]));

// Return the problem
echo json_encode($problem);
