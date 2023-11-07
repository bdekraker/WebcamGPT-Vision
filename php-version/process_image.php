<?php

// Check for user-supplied API key, otherwise use the default
$apiKey = "YOUR_DEFAULT_API_KEY"; // Replace with your actual default API key

$data = json_decode(file_get_contents('php://input'), true);
$base64Image = $data['image'] ?? '';

// Only proceed if an image was received
if ($base64Image) {
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer {$apiKey}"
    ];

    $payload = [
        "model" => "gpt-4-vision-preview",
        "messages" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => "What’s in this image? Be descriptive. For each significant item recognized, wrap this word in <b> tags. Example: The image shows a <b>man</b> in front of a neutral-colored <b>wall</b>. He has short hair, wears <b>glasses</b>, and is donning a pair of over-ear <b>headphones</b>. ... Also output an itemized list of objects recognized, wrapped in <br> and <b> tags with label <br><b>Objects:."
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => [
                            "url" => "data:image/jpeg;base64,{$base64Image}"
                        ]
                    ]
                ]
            ]
        ],
        "max_tokens" => 300
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        // If cURL encounters an error
        echo json_encode(['error' => 'Failed to process the image.']);
    } else {
        // Output the response from the API
        echo $response;
    }
} else {
    echo json_encode(['error' => 'No image data received.']);
}

?>
