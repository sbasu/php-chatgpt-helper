<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChatGPTHelper\ChatGPTHelper;

class SupportBot
{
    private ChatGPTHelper $chatGPT;
    private array $knowledgeBase;
    
    public function __construct(string $apiKey)
    {
        $this->chatGPT = new ChatGPTHelper($apiKey);
        $this->setupKnowledgeBase();
        $this->setupSystemPrompt();
    }
    
    private function setupKnowledgeBase(): void
    {
        $this->knowledgeBase = [
            'company' => 'TechCorp Solutions',
            'products' => [
                'CloudHost Pro' => 'Premium web hosting with 99.9% uptime',
                'DataSync' => 'Real-time data synchronization service',
                'SecureVault' => 'Encrypted cloud storage solution'
            ],
            'support_hours' => 'Monday-Friday 9 AM - 6 PM EST',
            'escalation_email' => 'escalate@techcorp.com',
            'common_issues' => [
                'login' => 'Reset password at /forgot-password or contact support',
                'billing' => 'View invoices in account dashboard or email billing@techcorp.com',
                'downtime' => 'Check status page at status.techcorp.com'
            ]
        ];
    }
    
    private function setupSystemPrompt(): void
    {
        $knowledge = json_encode($this->knowledgeBase, JSON_PRETTY_PRINT);
        
        $systemPrompt = "You are a helpful customer support assistant for {$this->knowledgeBase['company']}. 

Your knowledge base:
{$knowledge}

Guidelines:
1. Be friendly, professional, and empathetic
2. Use the knowledge base to answer questions accurately
3. If you don't know something, admit it and offer to escalate
4. Always try to solve the customer's problem
5. Keep responses concise but helpful
6. Ask clarifying questions when needed";

        $this->chatGPT->setSystemPrompt($systemPrompt)
                      ->setTemperature(0.3) // Lower temperature for consistent support responses
                      ->setMaxTokens(300);
    }
    
    public function handleTicket(string $customerMessage, array $customerInfo = []): array
    {
        // Add customer context if provided
        $contextualMessage = $customerMessage;
        if (!empty($customerInfo)) {
            $context = "Customer Info: " . json_encode($customerInfo) . "\n\nCustomer Message: " . $customerMessage;
            $contextualMessage = $context;
        }
        
        try {
            $response = $this->chatGPT->conversation($contextualMessage);
            
            return [
                'success' => true,
                'response' => $this->chatGPT->getResponseText($response),
                'tokens_used' => $this->chatGPT->getUsage($response)['total_tokens'] ?? 0,
                'needs_escalation' => $this->detectEscalation($response)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback_response' => "I'm experiencing technical difficulties. Please email support@techcorp.com or try again later."
            ];
        }
    }
    
    private function detectEscalation(array $response): bool
    {
        $responseText = strtolower($this->chatGPT->getResponseText($response));
        $escalationKeywords = ['escalate', 'supervisor', 'manager', 'complex', 'technical team', 'specialist'];
        
        foreach ($escalationKeywords as $keyword) {
            if (strpos($responseText, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getConversationSummary(): string
    {
        $history = $this->chatGPT->getConversation();
        $customerMessages = array_filter($history, fn($msg) => $msg['role'] === 'user');
        
        if (empty($customerMessages)) {
            return "No conversation yet.";
        }
        
        $summaryPrompt = "Summarize this customer support conversation in 2-3 sentences, focusing on the main issue and resolution status:\n\n" . 
                        json_encode($history, JSON_PRETTY_PRINT);
        
        try {
            $response = $this->chatGPT->chat($summaryPrompt);
            return $this->chatGPT->getResponseText($response);
        } catch (Exception $e) {
            return "Unable to generate conversation summary.";
        }
    }
    
    public function resetConversation(): void
    {
        $this->chatGPT->clearConversation();
        $this->setupSystemPrompt();
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $apiKey = 'your-openai-api-key-here';
    $supportBot = new SupportBot($apiKey);
    
    echo "=== Customer Support Bot Demo ===\n\n";
    
    try {
        // Simulate customer tickets
        $tickets = [
            [
                'message' => "I can't log into my CloudHost Pro account. It says my password is wrong but I'm sure it's correct.",
                'customer' => ['email' => 'john@example.com', 'product' => 'CloudHost Pro']
            ],
            [
                'message' => "Hi, I need help understanding my latest bill. There's a charge I don't recognize.",
                'customer' => ['email' => 'sarah@company.com', 'product' => 'DataSync']
            ],
            [
                'message' => "Is your service down? My website has been offline for 20 minutes.",
                'customer' => ['email' => 'admin@mysite.com', 'product' => 'CloudHost Pro']
            ]
        ];
        
        foreach ($tickets as $index => $ticket) {
            echo "--- Ticket #" . ($index + 1) . " ---\n";
            echo "Customer: " . $ticket['customer']['email'] . "\n";
            echo "Message: " . $ticket['message'] . "\n\n";
            
            $result = $supportBot->handleTicket($ticket['message'], $ticket['customer']);
            
            if ($result['success']) {
                echo "Bot Response: " . $result['response'] . "\n";
                echo "Tokens Used: " . $result['tokens_used'] . "\n";
                echo "Needs Escalation: " . ($result['needs_escalation'] ? 'Yes' : 'No') . "\n\n";
                
                // Follow up question for first ticket
                if ($index === 0) {
                    $followUp = $supportBot->handleTicket("I tried the password reset but didn't receive an email.");
                    echo "Follow-up Response: " . $followUp['response'] . "\n\n";
                }
            } else {
                echo "Error: " . $result['error'] . "\n";
                echo "Fallback: " . $result['fallback_response'] . "\n\n";
            }
            
            echo "Conversation Summary: " . $supportBot->getConversationSummary() . "\n";
            echo str_repeat("-", 50) . "\n\n";
            
            $supportBot->resetConversation();
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}