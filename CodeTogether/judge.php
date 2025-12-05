<?php
header('Content-Type: application/json');

$apiKey = getenv('API_KEY');
if (!$apiKey) exit(json_encode(['error'=>'API key missing']));

$input = json_decode(file_get_contents("php://input"), true);
$code = $input['code'] ?? '';
$language = $input['language'] ?? 'javascript';
$problem = $input['problem'] ?? '';

if (!$code || !$problem) exit(json_encode(['error'=>'Missing code or problem']));

$prompt = <<<EOT
You are a code judge. Evaluate the following $language submission for this problem:

Problem:
$problem

Code Submission:
$code

Return JSON only:
{
    "correct": true|false,
    "score": 0-100,
    "feedback": "..."
}
EOT;

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);

$messages = [
    ["role"=>"system","content"=>"You are a helpful coding judge."],
    ["role"=>"user","content"=>$prompt]
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode([
        "model"=>"gpt-5-nano",
        "messages"=>$messages,
    ])
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
