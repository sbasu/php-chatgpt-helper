<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChatGPTHelper\ChatGPTHelper;

// Initialize with your OpenAI API key
$apiKey = 'your-openai-api-key-here';
$chatGPT = new ChatGPTHelper($apiKey);

try {
    echo "=== Basic Chat Example ===\n\n";
    
    // Simple one-off question
    echo "1. Simple Question:\n";
    $response = $chatGPT->chat("What is the capital of France?");
    echo "Answer: " . $chatGPT->getResponseText($response) . "\n\n";
    
    // Using different models and settings
    echo "2. Creative Writing (Higher Temperature):\n";
    $response = $chatGPT
        ->setModel('gpt-3.5-turbo')
        ->setTemperature(1.2)
        ->setMaxTokens(150)
        ->chat("Write a short poem about coding");
    
    echo "Poem: " . $chatGPT->getResponseText($response) . "\n\n";
    
    // Show usage statistics
    $usage = $chatGPT->getUsage($response);
    echo "Tokens used: " . ($usage['total_tokens'] ?? 'N/A') . "\n\n";
    
    // Conversation with context
    echo "3. Conversation with Context:\n";
    $chatGPT->clearConversation()
             ->setSystemPrompt("You are a helpful coding assistant. Keep responses concise.")
             ->setTemperature(0.3); // Lower temperature for more focused responses
    
    $response1 = $chatGPT->conversation("I'm learning PHP. What's a good first project?");
    echo "Assistant: " . $chatGPT->getResponseText($response1) . "\n\n";
    
    $response2 = $chatGPT->conversation("How long would that take a beginner?");
    echo "Assistant: " . $chatGPT->getResponseText($response2) . "\n\n";
    
    // Show conversation history
    echo "4. Conversation History:\n";
    $history = $chatGPT->getConversation();
    foreach ($history as $message) {
        echo ucfirst($message['role']) . ": " . substr($message['content'], 0, 50) . "...\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure to:\n";
    echo "1. Set your actual OpenAI API key\n";
    echo "2. Run 'composer install' to install dependencies\n";
    echo "3. Check your internet connection\n";
}