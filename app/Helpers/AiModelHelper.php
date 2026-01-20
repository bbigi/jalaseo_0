<?php

namespace App\Helpers;

class AiModelHelper
{
    /**
     * Get all available AI models grouped by provider
     *
     * @return array
     */
    public static function getModelsByProvider(): array
    {
        return [
            // ========================================
            // PRIMARY PROVIDERS (Most Recommended)
            // ========================================

            'gemini' => [
                'gemini-2.5-flash' => 'Gemini 2.5 Flash (Latest - with Thinking)',
                'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite (Fastest & Most Efficient)',
                'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash (Experimental)',
                'gemini-1.5-pro-latest' => 'Gemini 1.5 Pro (Latest)',
                'gemini-1.5-flash-latest' => 'Gemini 1.5 Flash (Latest)',
                'gemini-1.5-flash-8b' => 'Gemini 1.5 Flash-8B',
            ],

            'chatgpt' => [
                'gpt-4o' => 'GPT-4o',
                'gpt-4o-mini' => 'GPT-4o Mini',
                'gpt-4-turbo' => 'GPT-4 Turbo',
                'gpt-4-turbo-preview' => 'GPT-4 Turbo Preview',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                'o1' => 'GPT-o1 (Reasoning Model)',
                'o1-mini' => 'GPT-o1 Mini',
            ],

            'deepseek' => [
                'deepseek-chat' => 'DeepSeek Chat V3',
                'deepseek-reasoner' => 'DeepSeek R1 (Reasoning Model)',
            ],

            // ========================================
            // SECONDARY PROVIDERS (High Quality)
            // ========================================

            'claude' => [
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (Oct 2024)',
                'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku (Oct 2024)',
                'claude-3-opus-20240229' => 'Claude 3 Opus',
                'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                'claude-3-haiku-20240307' => 'Claude 3 Haiku',
            ],

            'perplexity' => [
                'llama-3.1-sonar-small-128k-online' => 'Llama 3.1 Sonar Small 128K (Online)',
                'llama-3.1-sonar-large-128k-online' => 'Llama 3.1 Sonar Large 128K (Online)',
                'llama-3.1-sonar-huge-128k-online' => 'Llama 3.1 Sonar Huge 128K (Online)',
            ],

            // ========================================
            // OPEN-SOURCE / SELF-HOSTED OPTIONS
            // ========================================

            'mistral' => [
                'mistral-large-latest' => 'Mistral Large (Latest)',
                'mistral-medium-latest' => 'Mistral Medium (Latest)',
                'mistral-small-latest' => 'Mistral Small (Latest)',
                'pixtral-large-latest' => 'Pixtral Large (Multimodal)',
            ],

            'groq' => [
                'llama-3.3-70b-versatile' => 'Llama 3.3 70B (Versatile)',
                'llama-3.1-70b-versatile' => 'Llama 3.1 70B (Versatile)',
                'llama-3.1-8b-instant' => 'Llama 3.1 8B (Instant)',
                'mixtral-8x7b-32768' => 'Mixtral 8x7B',
                'gemma2-9b-it' => 'Gemma 2 9B (Instruction Tuned)',
            ],

            'cohere' => [
                'command-r-plus' => 'Command R+ (Best for Long Content)',
                'command-r' => 'Command R (Balanced)',
                'command-light' => 'Command Light (Fast)',
            ],

            // ========================================
            // EMERGING PROVIDERS
            // ========================================

            'qwen' => [
                'qwen2.5-72b-instruct' => 'Qwen 2.5 72B (Instruct)',
                'qwen2.5-32b-instruct' => 'Qwen 2.5 32B (Instruct)',
                'qwen2.5-14b-instruct' => 'Qwen 2.5 14B (Instruct)',
            ],

            'together' => [
                'meta-llama/Meta-Llama-3.1-405B-Instruct-Turbo' => 'Llama 3.1 405B (Largest)',
                'meta-llama/Meta-Llama-3.1-70B-Instruct-Turbo' => 'Llama 3.1 70B',
                'meta-llama/Meta-Llama-3.1-8B-Instruct-Turbo' => 'Llama 3.1 8B',
                'Qwen/Qwen2.5-72B-Instruct-Turbo' => 'Qwen 2.5 72B',
            ],

            // ========================================
            // SPECIALIZED MODELS
            // ========================================

            'x-ai' => [
                'grok-4-1-fast-reasoning' => 'Grok 4.1 (Fast Reasoning)',
                'grok-4-1-fast-non-reasoning' => 'Grok 4.1 (Fast Non-Reasoning)',
                'grok-4-fast-reasoning' => 'Grok 4 (Fast Reasoning)',
                'grok-4-fast-non-reasoning' => 'Grok 4 (Fast Non-Reasoning)',
            ],

            'fireworks' => [
                'accounts/fireworks/models/llama-v3p3-70b-instruct' => 'Llama 3.3 70B',
                'accounts/fireworks/models/qwen2p5-72b-instruct' => 'Qwen 2.5 72B',
                'accounts/fireworks/models/mixtral-8x22b-instruct' => 'Mixtral 8x22B',
            ],
        ];
    }

    /**
     * Get models for a specific provider
     *
     * @param string $provider
     * @return array
     */
    public static function getModelsForProvider(string $provider): array
    {
        $allModels = self::getModelsByProvider();
        return $allModels[$provider] ?? [];
    }

    /**
     * Get display name for a specific model
     *
     * @param string $provider
     * @param string $model
     * @return string
     */
    public static function getModelDisplayName(string $provider, string $model): string
    {
        $models = self::getModelsForProvider($provider);
        return $models[$model] ?? $model;
    }

    /**
     * Validate if a model exists for a provider
     *
     * @param string $provider
     * @param string $model
     * @return bool
     */
    public static function isValidModel(string $provider, string $model): bool
    {
        $models = self::getModelsForProvider($provider);
        return isset($models[$model]);
    }
}
