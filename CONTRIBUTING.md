# Contributing to PHP ChatGPT Helper

Thank you for considering contributing to this project! This guide will help you get started.

## Development Setup

1. **Fork and clone the repository**
   ```bash
   git clone https://github.com/yourusername/php-chatgpt-helper.git
   cd php-chatgpt-helper
   ```

2. **Run the setup script**
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

3. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

## Code Standards

### PHP Standards
- Follow **PSR-12** coding standards
- Use PHP 8.0+ features appropriately
- Add type hints for all parameters and return values
- Write clear, self-documenting code

### Code Style
We use PHP-CS-Fixer to maintain consistent code style:

```bash
# Check code style
composer run-script cs-check

# Fix code style issues
composer run-script cs-fix
```

### Documentation
- All public methods must have PHPDoc comments
- Include `@param` and `@return` tags
- Add `@throws` for exceptions
- Update README.md for new features

Example:
```php
/**
 * Send a chat message to OpenAI API
 * 
 * @param string $message The message to send
 * @param array $options Additional options for the request
 * @return array The API response
 * @throws \Exception When API request fails
 */
public function chat(string $message, array $options = []): array
{
    // Implementation
}
```

## Testing

### Writing Tests
- Write unit tests for all new functionality
- Use descriptive test method names
- Follow the AAA pattern (Arrange, Act, Assert)
- Mock external dependencies

### Test Types
1. **Unit Tests** - Test individual methods without API calls
2. **Integration Tests** - Test with real API calls (require API key)

### Running Tests
```bash
# Run all unit tests
composer run-script test-unit

# Run integration tests (requires OPENAI_API_KEY)
export OPENAI_API_KEY=your-key
composer run-script test-integration

# Run with coverage
composer run-script test-coverage
```

### Test Structure
```php
public function testMethodNameDescribesWhatItTests(): void
{
    // Arrange
    $input = 'test input';
    $expected = 'expected result';
    
    // Act
    $result = $this->chatGPT->someMethod($input);
    
    // Assert
    $this->assertEquals($expected, $result);
}
```

## Adding New Features

### 1. API Methods
When adding new OpenAI API endpoints:

1. Add the method to `ChatGPTHelper` class
2. Follow the existing pattern for error handling
3. Add comprehensive tests
4. Update documentation
5. Add usage examples

### 2. Helper Methods
For utility methods:

1. Keep them focused and single-purpose
2. Make them public only if needed by users
3. Add proper type hints and documentation
4. Write thorough tests

### 3. Examples
When adding examples:

1. Create realistic, practical scenarios
2. Include error handling
3. Add clear comments explaining the code
4. Test examples work with real API keys

## Pull Request Process

### Before Submitting
1. **Run all checks**
   ```bash
   composer run-script cs-check
   composer run-script test-unit
   ```

2. **Update documentation**
   - Add/update PHPDoc comments
   - Update README.md if needed
   - Add example usage

3. **Test your changes**
   - Test with real API key if possible
   - Verify examples still work
   - Check edge cases

### PR Guidelines
- **Title**: Clear, descriptive title
- **Description**: Explain what and why
- **Breaking Changes**: Clearly mark any breaking changes
- **Testing**: Describe how you tested the changes

### PR Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests pass
- [ ] Integration tests pass (if applicable)
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] Tests added/updated
```

## Common Patterns

### Error Handling
```php
try {
    $response = $this->makeRequest($endpoint, $data);
    return $response;
} catch (Exception $e) {
    throw new \Exception("Descriptive error message: " . $e->getMessage());
}
```

### Fluent Interface
```php
public function setOption($value): self
{
    $this->option = $value;
    return $this;
}
```

### Configuration
```php
public function configure(array $options): self
{
    foreach ($options as $key => $value) {
        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }
    return $this;
}
```

## Release Process

### Version Numbers
We follow [Semantic Versioning](https://semver.org/):
- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Checklist
1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Run full test suite
4. Create git tag
5. Update documentation

## Getting Help

- **Questions**: Open a GitHub Discussion
- **Bugs**: Open a GitHub Issue
- **Features**: Open a GitHub Issue with feature request label

## Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help newcomers get started
- Follow project conventions

Thank you for contributing! 🎉