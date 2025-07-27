<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChatGPTHelper\ChatGPTHelper;

class ContentGenerator
{
    private ChatGPTHelper $chatGPT;
    private array $templates;
    
    public function __construct(string $apiKey)
    {
        $this->chatGPT = new ChatGPTHelper($apiKey);
        $this->setupTemplates();
    }
    
    private function setupTemplates(): void
    {
        $this->templates = [
            'blog_post' => [
                'prompt' => "Write a {tone} blog post about '{topic}' for {audience}. Include an engaging title, introduction, {sections} main sections, and conclusion. Target length: {word_count} words.",
                'defaults' => [
                    'tone' => 'professional',
                    'audience' => 'general readers',
                    'sections' => '3-4',
                    'word_count' => '800-1000'
                ]
            ],
            'product_description' => [
                'prompt' => "Write a compelling product description for '{product_name}'. Highlight {key_features} key features, target {audience}, and include a call-to-action. Tone: {tone}. Length: {word_count} words.",
                'defaults' => [
                    'tone' => 'persuasive',
                    'audience' => 'potential customers',
                    'key_features' => '3-5',
                    'word_count' => '150-200'
                ]
            ],
            'social_media' => [
                'prompt' => "Create {platform} posts about '{topic}'. Generate {count} variations with {tone} tone. Include relevant hashtags and call-to-action where appropriate.",
                'defaults' => [
                    'platform' => 'LinkedIn',
                    'count' => '3',
                    'tone' => 'engaging'
                ]
            ],
            'email_campaign' => [
                'prompt' => "Write an email for '{campaign_type}' campaign. Subject: '{subject}'. Target audience: {audience}. Tone: {tone}. Include personalization placeholders and clear CTA.",
                'defaults' => [
                    'campaign_type' => 'newsletter',
                    'audience' => 'subscribers',
                    'tone' => 'friendly'
                ]
            ],
            'press_release' => [
                'prompt' => "Write a press release about '{announcement}' for {company}. Include headline, dateline, body with quotes, and boilerplate. Follow AP style. Target length: {word_count} words.",
                'defaults' => [
                    'company' => 'our company',
                    'word_count' => '400-500'
                ]
            ]
        ];
    }
    
    public function generateContent(string $type, array $params = []): array
    {
        if (!isset($this->templates[$type])) {
            throw new \InvalidArgumentException("Unknown content type: {$type}");
        }
        
        $template = $this->templates[$type];
        $prompt = $this->buildPrompt($template['prompt'], array_merge($template['defaults'], $params));
        
        // Set appropriate model settings for content generation
        $this->chatGPT->setTemperature(0.8) // Creative but consistent
                      ->setMaxTokens(1500)
                      ->setModel('gpt-3.5-turbo');
        
        try {
            $response = $this->chatGPT->chat($prompt);
            
            return [
                'success' => true,
                'content' => $this->chatGPT->getResponseText($response),
                'type' => $type,
                'parameters' => $params,
                'tokens_used' => $this->chatGPT->getUsage($response)['total_tokens'] ?? 0,
                'estimated_cost' => $this->estimateCost($this->chatGPT->getUsage($response))
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'type' => $type
            ];
        }
    }
    
    public function generateMultipleVariations(string $type, array $params = [], int $variations = 3): array
    {
        $results = [];
        
        for ($i = 1; $i <= $variations; $i++) {
            // Slightly randomize temperature for variations
            $this->chatGPT->setTemperature(0.7 + (0.2 * rand(0, 10) / 10));
            
            $result = $this->generateContent($type, $params);
            $result['variation'] = $i;
            $results[] = $result;
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }
        
        return $results;
    }
    
    public function generateContentSeries(string $topic, int $posts = 5): array
    {
        // Generate a content series with interconnected posts
        $seriesPrompt = "Create an outline for a {$posts}-part content series about '{$topic}'. 
                        List {$posts} blog post titles that build upon each other logically. 
                        Each title should be engaging and SEO-friendly.
                        Format as numbered list.";
        
        try {
            $this->chatGPT->setTemperature(0.6)->setMaxTokens(300);
            $outlineResponse = $this->chatGPT->chat($seriesPrompt);
            $outline = $this->chatGPT->getResponseText($outlineResponse);
            
            // Extract titles (simple regex - could be improved)
            preg_match_all('/\d+\.\s*(.+)$/m', $outline, $matches);
            $titles = $matches[1] ?? [];
            
            $series = [];
            foreach ($titles as $index => $title) {
                $title = trim($title);
                $postNumber = $index + 1;
                
                $result = $this->generateContent('blog_post', [
                    'topic' => $title,
                    'tone' => 'informative',
                    'audience' => 'professionals interested in ' . $topic,
                    'word_count' => '600-800'
                ]);
                
                $result['series_info'] = [
                    'part' => $postNumber,
                    'total_parts' => count($titles),
                    'series_topic' => $topic,
                    'title' => $title
                ];
                
                $series[] = $result;
                
                // Longer delay between series posts
                usleep(500000); // 0.5 second
            }
            
            return [
                'success' => true,
                'series_outline' => $outline,
                'posts' => $series,
                'total_tokens' => array_sum(array_column($series, 'tokens_used')),
                'total_estimated_cost' => array_sum(array_column($series, 'estimated_cost'))
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function optimizeForSEO(string $content, string $keyword): array
    {
        $optimizationPrompt = "Optimize this content for SEO with focus keyword '{$keyword}':

{$content}

Provide:
1. SEO-optimized title (include keyword)
2. Meta description (150-160 chars, include keyword)
3. 3-5 relevant hashtags
4. Suggestions for internal linking opportunities
5. Content improvements for better SEO";

        try {
            $this->chatGPT->setTemperature(0.3) // Lower temperature for consistent SEO advice
                          ->setMaxTokens(800);
            
            $response = $this->chatGPT->chat($optimizationPrompt);
            
            return [
                'success' => true,
                'original_content' => $content,
                'keyword' => $keyword,
                'seo_recommendations' => $this->chatGPT->getResponseText($response),
                'tokens_used' => $this->chatGPT->getUsage($response)['total_tokens'] ?? 0
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function buildPrompt(string $template, array $params): string
    {
        $prompt = $template;
        foreach ($params as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }
        return $prompt;
    }
    
    private function estimateCost(array $usage): float
    {
        // Rough cost estimation (prices as of 2024)
        $inputCost = 0.0015 / 1000; // $0.0015 per 1K input tokens
        $outputCost = 0.002 / 1000; // $0.002 per 1K output tokens
        
        $promptTokens = $usage['prompt_tokens'] ?? 0;
        $completionTokens = $usage['completion_tokens'] ?? 0;
        
        return ($promptTokens * $inputCost) + ($completionTokens * $outputCost);
    }
    
    public function getAvailableTypes(): array
    {
        return array_keys($this->templates);
    }
    
    public function getTemplateInfo(string $type): array
    {
        return $this->templates[$type] ?? [];
    }
}

// Example usage and demo
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $apiKey = 'your-openai-api-key-here';
    $generator = new ContentGenerator($apiKey);
    
    echo "=== Content Generator Demo ===\n\n";
    
    try {
        // 1. Blog Post Generation
        echo "1. BLOG POST GENERATION\n";
        echo str_repeat("-", 30) . "\n";
        
        $blogResult = $generator->generateContent('blog_post', [
            'topic' => 'The Future of Remote Work',
            'tone' => 'professional',
            'audience' => 'business professionals',
            'word_count' => '600-700'
        ]);
        
        if ($blogResult['success']) {
            echo "Generated blog post:\n";
            echo substr($blogResult['content'], 0, 300) . "...\n\n";
            echo "Tokens used: " . $blogResult['tokens_used'] . "\n";
            echo "Estimated cost: $" . number_format($blogResult['estimated_cost'], 4) . "\n\n";
        }
        
        // 2. Product Description Variations
        echo "2. PRODUCT DESCRIPTION VARIATIONS\n";
        echo str_repeat("-", 35) . "\n";
        
        $productVariations = $generator->generateMultipleVariations('product_description', [
            'product_name' => 'Smart Fitness Tracker Pro',
            'key_features' => '5',
            'audience' => 'fitness enthusiasts',
            'tone' => 'exciting'
        ], 2);
        
        foreach ($productVariations as $variation) {
            if ($variation['success']) {
                echo "Variation {$variation['variation']}:\n";
                echo $variation['content'] . "\n";
                echo str_repeat("-", 20) . "\n";
            }
        }
        
        // 3. Social Media Posts
        echo "3. SOCIAL MEDIA CONTENT\n";
        echo str_repeat("-", 25) . "\n";
        
        $socialResult = $generator->generateContent('social_media', [
            'platform' => 'LinkedIn',
            'topic' => 'AI in Business',
            'count' => '3',
            'tone' => 'thought-provoking'
        ]);
        
        if ($socialResult['success']) {
            echo $socialResult['content'] . "\n\n";
        }
        
        // 4. Content Series (limited example)
        echo "4. CONTENT SERIES OUTLINE\n";
        echo str_repeat("-", 28) . "\n";
        
        // Generate just the outline for demo (full series would use many tokens)
        $this->chatGPT = $generator->chatGPT ?? new ChatGPTHelper($apiKey);
        $outlineResponse = $this->chatGPT->chat("Create an outline for a 3-part blog series about 'Sustainable Business Practices'. List 3 engaging, SEO-friendly titles.");
        echo $this->chatGPT->getResponseText($outlineResponse) . "\n\n";
        
        // 5. SEO Optimization Demo
        echo "5. SEO OPTIMIZATION\n";
        echo str_repeat("-", 20) . "\n";
        
        $sampleContent = "Remote work has become increasingly popular. Many companies are adopting flexible work policies. This trend is changing how we think about productivity and work-life balance.";
        
        $seoResult = $generator->optimizeForSEO($sampleContent, 'remote work productivity');
        
        if ($seoResult['success']) {
            echo "SEO Recommendations:\n";
            echo $seoResult['seo_recommendations'] . "\n\n";
        }
        
        // 6. Available Content Types
        echo "6. AVAILABLE CONTENT TYPES\n";
        echo str_repeat("-", 30) . "\n";
        
        $types = $generator->getAvailableTypes();
        foreach ($types as $type) {
            $info = $generator->getTemplateInfo($type);
            echo "• " . ucwords(str_replace('_', ' ', $type)) . "\n";
            echo "  Default params: " . implode(', ', array_keys($info['defaults'])) . "\n";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "\nMake sure to:\n";
        echo "1. Set your actual OpenAI API key\n";
        echo "2. Run 'composer install'\n";
        echo "3. Check your internet connection\n";
        echo "4. Verify your OpenAI account has credits\n";
    }
}