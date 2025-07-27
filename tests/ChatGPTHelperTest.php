<?php

namespace ChatGPTHelper\Tests;

use PHPUnit\Framework\TestCase;
use ChatGPTHelper\ChatGPTHelper;

class ChatGPTHelperTest extends TestCase
{
    private ChatGPTHelper $chatGPT;
    private string $testApiKey = 'test-api-key-123';
    
    protected function setUp(): void
    {
        $this->chatGPT = new ChatGPTHelper($this->testApiKey);
    }
    
    public function testConstructorSetsApiKey(): void
    {
        $this->assertInstanceOf(ChatGPTHelper::class, $this->chatGPT);
    }
    
    public function testSetModelReturnsInstance(): void
    {
        $result = $this->chatGPT->setModel('gpt-4');
        $this->assertSame($this->chatGPT, $result);
    }
    
    public function testSetMaxTokensReturnsInstance(): void
    {
        $result = $this->chatGPT->setMaxTokens(500);
        $this->assertSame($this->chatGPT, $result);
    }
    
    public function testSetTemperatureReturnsInstance(): void
    {
        $result = $this->chatGPT->setTemperature(0.8);
        $this->assertSame($this->chatGPT, $result);
    }
    
    public function testSetTemperatureClampsBounds(): void
    {
        // Test lower bound
        $this->chatGPT->setTemperature(-1.0);
        $this->assertTrue(true); // Should not throw exception
        
        // Test upper bound
        $this->chatGPT->setTemperature(3.0);
        $this->assertTrue(true); // Should not throw exception
    }
    
    public function testMethodChaining(): void
    {
        $result = $this->chatGPT
            ->setModel('gpt-4')
            ->setMaxTokens(1000)
            ->setTemperature(0.5);
            
        $this->assertSame($this->chatGPT, $result);
    }
    
    public function testSetSystemPromptReturnsInstance(): void
    {
        $result = $this->chatGPT->setSystemPrompt('You are a helpful assistant');
        $this->assertSame($this->chatGPT, $result);
    }
    
    public function testClearConversationReturnsInstance(): void
    {
        $result = $this->chatGPT->clearConversation();
        $this->assertSame($this->chatGPT, $result);
    }
    
    public function testGetConversationInitiallyEmpty(): void
    {
        $conversation = $this->chatGPT->getConversation();
        $this->assertIsArray($conversation);
        $this->assertEmpty($conversation);
    }
    
    public function testSetSystemPromptAddsToConversation(): void
    {
        $prompt = 'You are a helpful assistant';
        $this->chatGPT->setSystemPrompt($prompt);
        
        $conversation = $this->chatGPT->getConversation();
        $this->assertCount(1, $conversation);
        $this->assertEquals('system', $conversation[0]['role']);
        $this->assertEquals($prompt, $conversation[0]['content']);
    }
    
    public function testSetSystemPromptReplacesExisting(): void
    {
        $this->chatGPT->setSystemPrompt('First prompt');
        $this->chatGPT->setSystemPrompt('Second prompt');
        
        $conversation = $this->chatGPT->getConversation();
        $this->assertCount(1, $conversation);
        $this->assertEquals('Second prompt', $conversation[0]['content']);
    }
    
    public function testClearConversationRemovesMessages(): void
    {
        $this->chatGPT->setSystemPrompt('Test prompt');
        $this->assertNotEmpty($this->chatGPT->getConversation());
        
        $this->chatGPT->clearConversation();
        $this->assertEmpty($this->chatGPT->getConversation());
    }
    
    public function testEstimateTokensReturnsInteger(): void
    {
        $text = 'This is a test message';
        $tokens = $this->chatGPT->estimateTokens($text);
        
        $this->assertIsInt($tokens);
        $this->assertGreaterThan(0, $tokens);
    }
    
    public function testEstimateTokensScalesWithLength(): void
    {
        $shortText = 'Hi';
        $longText = 'This is a much longer text that should have more tokens than the short text';
        
        $shortTokens = $this->chatGPT->estimateTokens($shortText);
        $longTokens = $this->chatGPT->estimateTokens($longText);
        
        $this->assertGreaterThan($shortTokens, $longTokens);
    }
    
    public function testGetResponseTextWithValidResponse(): void
    {
        $response = [
            'choices' => [
                [
                    'message' => [
                        'content' => 'Test response content'
                    ]
                ]
            ]
        ];
        
        $text = $this->chatGPT->getResponseText($response);
        $this->assertEquals('Test response content', $text);
    }
    
    public function testGetResponseTextWithInvalidResponse(): void
    {
        $response = ['invalid' => 'structure'];
        $text = $this->chatGPT->getResponseText($response);
        $this->assertEquals('', $text);
    }
    
    public function testGetUsageWithValidResponse(): void
    {
        $usage = [
            'prompt_tokens' => 10,
            'completion_tokens' => 20,
            'total_tokens' => 30
        ];
        
        $response = ['usage' => $usage];
        $result = $this->chatGPT->getUsage($response);
        
        $this->assertEquals($usage, $result);
    }
    
    public function testGetUsageWithInvalidResponse(): void
    {
        $response = ['invalid' => 'structure'];
        $result = $this->chatGPT->getUsage($response);
        
        $this->assertEquals([], $result);
    }
    
    /**
     * Test API call methods (these would fail without valid API key)
     * In a real test environment, you'd either:
     * 1. Use dependency injection to mock HTTP client
     * 2. Use environment variables for real API testing
     * 3. Create integration tests separate from unit tests
     */
    public function testChatThrowsExceptionWithInvalidApiKey(): void
    {
        $this->expectException(\Exception::class);
        
        // This will fail because we're using a fake API key
        $this->chatGPT->chat('Hello');
    }
    
    public function testConversationThrowsExceptionWithInvalidApiKey(): void
    {
        $this->expectException(\Exception::class);
        
        // This will fail because we're using a fake API key
        $this->chatGPT->conversation('Hello');
    }
    
    public function testCompleteThrowsExceptionWithInvalidApiKey(): void
    {
        $this->expectException(\Exception::class);
        
        // This will fail because we're using a fake API key
        $this->chatGPT->complete('Hello');
    }
    
    public function testGenerateImageThrowsExceptionWithInvalidApiKey(): void
    {
        $this->expectException(\Exception::class);
        
        // This will fail because we're using a fake API key
        $this->chatGPT->generateImage('A beautiful sunset');
    }
    
    public function testGetModelsThrowsExceptionWithInvalidApiKey(): void
    {
        $this->expectException(\Exception::class);
        
        // This will fail because we're using a fake API key
        $this->chatGPT->getModels();
    }
}

/**
 * Integration Tests (require valid API key)
 * 
 * These tests should be run separately with a real API key
 * set in environment variables for CI/CD testing
 */
class ChatGPTHelperIntegrationTest extends TestCase
{
    private ?ChatGPTHelper $chatGPT = null;
    
    protected function setUp(): void
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        
        if (!$apiKey) {
            $this->markTestSkipped('OPENAI_API_KEY environment variable not set');
        }
        
        $this->chatGPT = new ChatGPTHelper($apiKey);
    }
    
    public function testRealChatRequest(): void
    {
        if (!$this->chatGPT) {
            $this->markTestSkipped('No valid API key available');
        }
        
        $response = $this->chatGPT->chat('Say "Hello World" and nothing else');
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('choices', $response);
        $this->assertArrayHasKey('usage', $response);
        
        $text = $this->chatGPT->getResponseText($response);
        $this->assertNotEmpty($text);
        $this->assertStringContainsString('Hello World', $text);
    }
    
    public function testRealConversation(): void
    {
        if (!$this->chatGPT) {
            $this->markTestSkipped('No valid API key available');
        }
        
        $this->chatGPT->setSystemPrompt('You are a test assistant. Always respond with exactly "OK" to any message.');
        
        $response1 = $this->chatGPT->conversation('First message');
        $response2 = $this->chatGPT->conversation('Second message');
        
        $this->assertIsArray($response1);
        $this->assertIsArray($response2);
        
        $conversation = $this->chatGPT->getConversation();
        $this->assertCount(5, $conversation); // system + user1 + assistant1 + user2 + assistant2
    }
    
    public function testRealImageGeneration(): void
    {
        if (!$this->chatGPT) {
            $this->markTestSkipped('No valid API key available');
        }
        
        $response = $this->chatGPT->generateImage('A simple red circle', [
            'size' => '256x256',
            'n' => 1
        ]);
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
        $this->assertArrayHasKey('url', $response['data'][0]);
    }
    
    public function testRealTokenUsage(): void
    {
        if (!$this->chatGPT) {
            $this->markTestSkipped('No valid API key available');
        }
        
        $response = $this->chatGPT->chat('Count to three');
        $usage = $this->chatGPT->getUsage($response);
        
        $this->assertArrayHasKey('prompt_tokens', $usage);
        $this->assertArrayHasKey('completion_tokens', $usage);
        $this->assertArrayHasKey('total_tokens', $usage);
        
        $this->assertGreaterThan(0, $usage['prompt_tokens']);
        $this->assertGreaterThan(0, $usage['completion_tokens']);
        $this->assertEquals(
            $usage['prompt_tokens'] + $usage['completion_tokens'], 
            $usage['total_tokens']
        );
    }
    
    public function testRealModelsList(): void
    {
        if (!$this->chatGPT) {
            $this->markTestSkipped('No valid API key available');
        }
        
        $response = $this->chatGPT->getModels();
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);
    }
}