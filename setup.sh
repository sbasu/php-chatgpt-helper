#!/bin/bash

# PHP ChatGPT Helper - Setup Script
# This script helps you get the project up and running quickly

echo "🚀 Setting up PHP ChatGPT Helper..."
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.0 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
if [[ $(echo "$PHP_VERSION 8.0" | awk '{print ($1 >= $2)}') -eq 0 ]]; then
    echo "❌ PHP version $PHP_VERSION is too old. Please upgrade to PHP 8.0 or higher."
    exit 1
fi

echo "✅ PHP $PHP_VERSION detected"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    echo "   Visit: https://getcomposer.org/download/"
    exit 1
fi

echo "✅ Composer detected"

# Install dependencies
echo ""
echo "📦 Installing dependencies..."
composer install

if [ $? -ne 0 ]; then
    echo "❌ Failed to install dependencies"
    exit 1
fi

echo "✅ Dependencies installed successfully"

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo ""
    echo "📝 Creating .env file..."
    cat > .env << EOL
# OpenAI API Configuration
OPENAI_API_KEY=your-openai-api-key-here

# Default Model Settings
DEFAULT_MODEL=gpt-3.5-turbo
DEFAULT_MAX_TOKENS=1000
DEFAULT_TEMPERATURE=0.7

# Environment
APP_ENV=development
EOL
    echo "✅ .env file created"
    echo "⚠️  Please edit .env and add your OpenAI API key"
else
    echo "✅ .env file already exists"
fi

# Run tests to make sure everything works
echo ""
echo "🧪 Running tests..."
composer run-script test-unit

if [ $? -eq 0 ]; then
    echo "✅ All tests passed!"
else
    echo "⚠️  Some tests failed, but this is expected without a valid API key"
fi

echo ""
echo "🎉 Setup complete!"
echo ""
echo "Next steps:"
echo "1. Edit .env file and add your OpenAI API key"
echo "2. Try the examples:"
echo "   php examples/basic-chat.php"
echo "   php examples/support-bot.php"
echo "   php examples/content-generator.php"
echo ""
echo "3. Run tests with your API key:"
echo "   export OPENAI_API_KEY=your-key"
echo "   composer run-script test-integration"
echo ""
echo "📚 Read the README.md for detailed documentation"
echo ""