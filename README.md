# PHP ChatGPT Helper

A simple, powerful PHP library for integrating with OpenAI's ChatGPT API. Perfect for adding AI capabilities to your PHP applications with minimal setup.

## Features

- 🚀 **Easy to use** - Simple, fluent interface
- 💬 **Conversation management** - Maintain context across multiple messages
- 🎛️ **Flexible configuration** - Customize models, temperature, tokens, and more
- 🛡️ **Error handling** - Robust error handling with meaningful messages  
- 🎨 **Image generation** - DALL-E integration for AI image creation
- 📊 **Usage tracking** - Monitor token usage and costs
- 🔧 **Multiple examples** - Ready-to-use examples for common scenarios

## Installation

Install via Composer:

```bash
composer require yourname/php-chatgpt-helper
```

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use ChatGPTHelper\ChatGPTHelper;

$chatGPT = new ChatGPTHelper('your-openai-api-key');

// Simple chat
$response = $chatGPT->chat("Hello, how are you?");
echo $chatGPT->getResponseText($response);

// Conversation with context
$chatGPT->setSystemPrompt("You are a helpful coding assistant.")
        ->conversation("I'm learning PHP")
        ->conversation("What should I build first?");
```

## Configuration

### API Key

Get your API key from [OpenAI's platform](https://platform.openai.com/account/api-keys):

```php
$chatGPT = new ChatGPTHelper('sk-your-api-key-here');
```

### Model Selection

```php
$chatGPT->setModel('gpt-4')           // Most capable
        ->setModel('gpt-3.5-turbo')   // Fast and efficient (default)
        ->setModel('gpt-3.5-turbo-16k'); // Longer context
```

### Response Settings

```php
$chatGPT->setMaxTokens(500)        // Limit response length
        ->setTemperature(0.7);     // Control creativity (0.0-2.0)
```

## Usage Examples

### Basic Chat

```php
// One-off questions
$response = $chatGPT->chat("What is machine learning?");
echo $chatGPT->getResponseText($response);

// Check token usage
$usage = $chatGPT->getUsage($response);
echo "Tokens used: " . $usage['total_tokens'];
```

### Conversations

```php
// Set context for the conversation
$chatGPT->setSystemPrompt("You are a helpful cooking assistant.")
        ->setTemperature(0.3); // Lower temperature for consistent responses

// Have a back-and-forth conversation
$response1 = $chatGPT->conversation("I want to make pasta");
$response2 = $chatGPT->conversation("What ingredients do I need?");
$response3 = $chatGPT->conversation("How long does it take to cook?");

// View conversation history
$history = $chatGPT->getConversation();
foreach ($history as $message) {
    echo $message['role'] . ": " . $message['content'] . "\n";
}
```

### Image Generation

```php
// Generate images with DALL-E
$response = $chatGPT->generateImage("A sunset over mountains", [
    'size' => '1024x1024',
    'n' => 1
]);

// Get image URL
$imageUrl = $response['data'][0]['url'];
echo "Generated image: " . $imageUrl;
```

### Advanced Usage

```php
// Chain methods for complex setups
$response = $chatGPT
    ->setModel('gpt-4')
    ->setTemperature(1.2)
    ->setMaxTokens(200)
    ->setSystemPrompt("You are a creative writer")
    ->conversation("Write a short story about robots");

// Custom options for specific requests
$response = $chatGPT->complete("Explain quantum physics", [
    'temperature' => 0.1,    // Very focused
    'max_tokens' => 500,
    'top_p' => 0.9
]);
```

## Complete Examples

### 1. Basic Chat Bot
See `examples/basic-chat.php` for a simple chatbot implementation.

### 2. Customer Support Bot  
See `examples/support-bot.php` for an advanced support system with:
- Knowledge base integration
- Conversation summaries
- Escalation detection
- Customer context handling

### 3. Content Generator
See `examples/content-generator.php` for automated content creation tools.

## API Reference

### Core Methods

| Method | Description |
|--------|-------------|
| `chat(string $message)` | Send a single message |
| `conversation(string $message)` | Continue a conversation with context |
| `complete(string $prompt)` | Generate text completion |
| `generateImage(string $prompt)` | Create AI-generated images |

### Configuration Methods

| Method | Description |
|--------|-------------|
| `setModel(string $model)` | Set the AI model to use |
| `setMaxTokens(int $tokens)` | Limit response length |
| `setTemperature(float $temp)` | Control creativity (0.0-2.0) |
| `setSystemPrompt(string $prompt)` | Set conversation context |

### Utility Methods

| Method | Description |
|--------|-------------|
| `getResponseText(array $response)` | Extract text from API response |
| `getUsage(array $response)` | Get token usage statistics |
| `getConversation()` | Get conversation history |
| `clearConversation()` | Reset conversation context |
| `estimateTokens(string $text)` | Rough token count estimation |

## Error Handling

The library throws exceptions for API errors:

```php
try {
    $response = $chatGPT->chat("Hello!");
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    
    // Common issues:
    // - Invalid API key
    // - Rate limit exceeded  
    // - Network connectivity
    // - Invalid model name
}
```

## Requirements

- PHP 8.0+
- cURL extension
- JSON extension
- OpenAI API key

## Testing

Run the test suite:

```bash
composer test
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

MIT License. See `LICENSE` file for details.

## Support

- 📧 Email: your.email@example.com
- 🐛 Issues: [GitHub Issues](https://github.com/yourname/php-chatgpt-helper/issues)
- 📚 Documentation: [Full API docs](https://github.com/yourname/php-chatgpt-helper/wiki)

## Changelog

### v1.0.0
- Initial release
- Basic chat functionality
- Conversation management
- Image generation support
- Comprehensive examples