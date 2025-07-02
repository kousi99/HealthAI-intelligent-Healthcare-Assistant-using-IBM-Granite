<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $question = trim($input['question'] ?? '');

    if (!$question) {
        echo json_encode(['answer' => 'Please enter a question.']);
        exit;
    }

    // --- Gemini API Call Function ---
    function callGoogleGemini($prompt) {
        $apiKey = 'AIzaSyCfuQ3iVQisc014NnR2hoG2t8u40C0SALo';  // <--- Your real key

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";

        $payload = json_encode([
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "X-goog-api-key: $apiKey"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return "Error connecting: " . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($response, true);

        // Check for API error response
        if (isset($result['error'])) {
            return "Gemini API Error (" . $result['error']['code'] . "): " . $result['error']['message'];
        }

        // Normal success response
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }

        return "Unknown response format:\n" . json_encode($result, JSON_PRETTY_PRINT);
    }

    // Generate and return answer
    $answer = callGoogleGemini($question);
    echo json_encode(['answer' => $answer]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HealthAI - Intelligent Healthcare Assistant</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 50px auto;
      background: #ffffff;
      padding: 20px 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-radius: 10px;
    }
    h1 {
      text-align: center;
      color: #2c3e50;
    }
    p {
      text-align: center;
      color: #34495e;
    }
    textarea {
      width: 100%;
      height: 150px;
      padding: 12px;
      margin-top: 12px;
      font-size: 1em;
      border: 1px solid #ccc;
      border-radius: 6px;
      resize: vertical;
    }
    button {
      display: block;
      width: 100%;
      padding: 14px;
      margin-top: 16px;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1em;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #2980b9;
    }
    #answer {
      margin-top: 20px;
      padding: 16px;
      background-color: #ecf0f1;
      border-radius: 6px;
      min-height: 50px;
      white-space: pre-wrap;
      color: #2c3e50;
    }
    #loading {
      text-align: center;
      font-style: italic;
      color: #888;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>HealthAI Assistant</h1>
    <p>Ask me any health-related question:</p>
    <textarea id="question" placeholder="Type your question here..."></textarea>
    <button id="askBtn">Ask HealthAI</button>
    <div id="loading"></div>
    <div id="answer"></div>
  </div>

  <script>
    document.getElementById('askBtn').addEventListener('click', () => {
      const question = document.getElementById('question').value.trim();
      const answerBox = document.getElementById('answer');
      const loading = document.getElementById('loading');

      answerBox.textContent = "";
      loading.textContent = "";

      if (!question) {
        answerBox.textContent = "Please enter a question.";
        return;
      }

      loading.textContent = "Loading... Please wait.";

      fetch(window.location.href + '?ajax=1', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ question: question })
      })
      .then(res => res.json())
      .then(data => {
        loading.textContent = "";
        answerBox.textContent = data.answer;
      })
      .catch(error => {
        loading.textContent = "";
        answerBox.textContent = "Error: " + error;
      });
    });
  </script>
</body>
</html>
