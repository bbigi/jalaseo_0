# AI Models Reference for Article Generation

Comprehensive list of AI models supported by the platform, organized by provider and use case.

## Table of Contents
- [Primary Providers](#primary-providers) - Most recommended for production
- [Secondary Providers](#secondary-providers) - High quality alternatives
- [Open-Source / Self-Hosted](#open-source--self-hosted) - Cost-effective options
- [Emerging Providers](#emerging-providers) - Newer players
- [Specialized Models](#specialized-models) - Niche use cases
- [Pricing Comparison](#pricing-comparison)
- [Best Practices](#best-practices)

---

## Primary Providers
*Best for: Production use, reliability, support*

### 1. **Google Gemini**
**Provider Code:** `gemini`

| Model ID | Display Name | Context | Best For |
|----------|-------------|---------|----------|
| `gemini-2.0-flash-exp` | Gemini 2.0 Flash (Experimental) | 1M tokens | Latest features, fastest response |
| `gemini-1.5-pro-latest` | Gemini 1.5 Pro (Latest) | 2M tokens | Long-form content, complex topics |
| `gemini-1.5-flash-latest` | Gemini 1.5 Flash (Latest) | 1M tokens | Fast generation, balanced cost |
| `gemini-1.5-flash-8b` | Gemini 1.5 Flash-8B | 1M tokens | High volume, lowest cost |

**Pricing (Per 1M tokens):**
- Input: $0.075 - $1.25
- Output: $0.30 - $5.00

**Strengths:**
- Very competitive pricing
- Excellent multilingual support
- Large context window (up to 2M tokens)
- Strong performance on long-form content

**When to Use:**
- Default choice for most article generation
- Multilingual content (supports 100+ languages)
- Long articles (>3000 words)
- Budget-conscious projects

---

### 2. **OpenAI ChatGPT**
**Provider Code:** `chatgpt`

| Model ID | Display Name | Context | Best For |
|----------|-------------|---------|----------|
| `gpt-4o` | GPT-4o | 128K tokens | Highest quality articles |
| `gpt-4o-mini` | GPT-4o Mini | 128K tokens | Cost-effective, good quality |
| `gpt-4-turbo` | GPT-4 Turbo | 128K tokens | Complex reasoning |
| `gpt-3.5-turbo` | GPT-3.5 Turbo | 16K tokens | Simple articles, high volume |
| `o1` | GPT-o1 (Reasoning Model) | 128K tokens | Research articles, analysis |
| `o1-mini` | GPT-o1 Mini | 128K tokens | Fast reasoning, lower cost |

**Pricing (Per 1M tokens):**
- GPT-4o: $2.50 (input) / $10.00 (output)
- GPT-4o-mini: $0.15 (input) / $0.60 (output)
- GPT-3.5-turbo: $0.50 (input) / $1.50 (output)

**Strengths:**
- Industry standard, most tested
- Excellent instruction following
- Strong creative writing capabilities
- Best for English content

**When to Use:**
- Premium content requiring highest quality
- Creative writing, storytelling
- Technical documentation
- When budget allows for premium pricing

---

### 3. **DeepSeek**
**Provider Code:** `deepseek`

| Model ID | Display Name | Context | Best For |
|----------|-------------|---------|----------|
| `deepseek-chat` | DeepSeek Chat V3 | 64K tokens | General article generation |
| `deepseek-reasoner` | DeepSeek R1 (Reasoning Model) | 64K tokens | Research, analysis articles |

**Pricing (Per 1M tokens):**
- DeepSeek Chat V3: $0.27 (input) / $1.10 (output)
- DeepSeek R1: $0.55 (input) / $2.19 (output)

**Strengths:**
- Extremely cost-effective
- Strong reasoning capabilities
- Good for technical content
- Competitive with GPT-4 level performance

**When to Use:**
- Cost-sensitive projects
- Technical/analytical articles
- High-volume content generation
- Alternative to expensive models

---

## Secondary Providers
*Best for: Specific use cases, quality alternatives*

### 4. **Anthropic Claude**
**Provider Code:** `claude`

| Model ID | Display Name | Context | Best For |
|----------|-------------|---------|----------|
| `claude-3-5-sonnet-20241022` | Claude 3.5 Sonnet (Oct 2024) | 200K tokens | Long-form, nuanced writing |
| `claude-3-5-haiku-20241022` | Claude 3.5 Haiku (Oct 2024) | 200K tokens | Fast, cost-effective |
| `claude-3-opus-20240229` | Claude 3 Opus | 200K tokens | Premium quality |

**Pricing (Per 1M tokens):**
- Opus: $15.00 (input) / $75.00 (output)
- Sonnet: $3.00 (input) / $15.00 (output)
- Haiku: $0.25 (input) / $1.25 (output)

**Strengths:**
- Best-in-class for long-form content
- Excellent at following complex instructions
- Strong safety features
- Very nuanced writing style

**When to Use:**
- Long-form articles (>5000 words)
- Content requiring high coherence
- Sensitive topics (good safety filters)
- Premium blog content

---

### 5. **Perplexity**
**Provider Code:** `perplexity`

| Model ID | Display Name | Context | Best For |
|----------|-------------|---------|----------|
| `llama-3.1-sonar-small-128k-online` | Llama 3.1 Sonar Small (Online) | 128K tokens | News articles, current events |
| `llama-3.1-sonar-large-128k-online` | Llama 3.1 Sonar Large (Online) | 128K tokens | Research-based content |

**Pricing:** Contact for pricing

**Strengths:**
- Real-time web search integration
- Up-to-date information
- Citation generation
- Fact-checking capabilities

**When to Use:**
- News articles
- Content requiring latest information
- Research-backed articles
- Fact-heavy content

---

## Open-Source / Self-Hosted
*Best for: Cost control, privacy, customization*

### 6. **Mistral AI**
**Provider Code:** `mistral`

| Model ID | Display Name | Best For |
|----------|-------------|----------|
| `mistral-large-latest` | Mistral Large | Premium quality |
| `mistral-medium-latest` | Mistral Medium | Balanced |
| `mistral-small-latest` | Mistral Small | Fast & cheap |

**Pricing:** €2-8 per 1M tokens

**When to Use:**
- European data residency requirements
- Open-source alternative to GPT-4
- Multilingual content (strong French support)

---

### 7. **Groq**
**Provider Code:** `groq`

| Model ID | Display Name | Speed |
|----------|-------------|-------|
| `llama-3.3-70b-versatile` | Llama 3.3 70B | Ultra-fast |
| `llama-3.1-8b-instant` | Llama 3.1 8B | Instant response |

**Pricing:** $0.05-0.79 per 1M tokens

**Strengths:**
- Fastest inference speed (500+ tokens/sec)
- Very competitive pricing
- Good for real-time applications

**When to Use:**
- High-volume generation
- Real-time content creation
- Speed-critical applications

---

### 8. **Cohere**
**Provider Code:** `cohere`

| Model ID | Display Name | Best For |
|----------|-------------|----------|
| `command-r-plus` | Command R+ | Long documents |
| `command-r` | Command R | General use |

**Pricing:** $0.50-3.00 per 1M tokens

**When to Use:**
- Enterprise deployments
- RAG (Retrieval-Augmented Generation)
- Multilingual content

---

## Emerging Providers

### 9. **Qwen (Alibaba)**
**Provider Code:** `qwen`

Models: Qwen 2.5 series (14B, 32B, 72B)

**Strengths:**
- Strong multilingual (especially Chinese)
- Competitive performance
- Open-source friendly

---

### 10. **Together AI**
**Provider Code:** `together`

Hosts: Llama 3.1 (up to 405B), Qwen, Mixtral

**Pricing:** $0.18-0.90 per 1M tokens

**When to Use:**
- Access to largest open-source models
- Flexible deployment options

---

## Specialized Models

### 11. **X.AI Grok**
**Provider Code:** `x-ai`

Models: Grok 2, Grok 2 Vision

**Strengths:**
- Real-time Twitter/X integration
- Up-to-date knowledge
- Multimodal (text + images)

**When to Use:**
- Social media content
- Viral content analysis
- Current events

---

### 12. **Fireworks AI**
**Provider Code:** `fireworks`

Hosts: Llama, Qwen, Mixtral variants

**Pricing:** $0.20-0.90 per 1M tokens

**When to Use:**
- Low-latency requirements
- Serverless deployments

---

## Pricing Comparison

### Budget Tier (<$1/1M tokens)
1. **DeepSeek Chat V3** - $0.27 / $1.10
2. **Gemini Flash-8B** - $0.075 / $0.30
3. **GPT-4o Mini** - $0.15 / $0.60
4. **Groq Llama 3.1 8B** - $0.05 / $0.08

### Mid Tier ($1-5/1M tokens)
1. **Gemini 1.5 Pro** - $1.25 / $5.00
2. **GPT-4o** - $2.50 / $10.00
3. **Claude Sonnet** - $3.00 / $15.00
4. **DeepSeek R1** - $0.55 / $2.19

### Premium Tier (>$5/1M tokens)
1. **Claude Opus** - $15.00 / $75.00
2. **GPT-o1** - $15.00 / $60.00

---

## Best Practices

### Choosing the Right Model

**For Blog Posts (500-1500 words):**
- Primary: Gemini 1.5 Flash, GPT-4o Mini, DeepSeek Chat
- Premium: Claude Sonnet, GPT-4o

**For Long-Form Articles (>3000 words):**
- Primary: Gemini 1.5 Pro, Claude Sonnet
- Budget: DeepSeek Chat V3

**For Technical Content:**
- Primary: GPT-4o, DeepSeek R1, Claude Sonnet
- Budget: Gemini 1.5 Pro

**For Multilingual Content:**
- Primary: Gemini series, Qwen 2.5
- Alternative: Mistral, Cohere

**For High-Volume Generation:**
- Primary: Groq Llama models, Gemini Flash-8B
- Alternative: DeepSeek Chat, GPT-4o Mini

**For Research/Analysis:**
- Primary: GPT-o1, DeepSeek R1
- Alternative: Claude Opus, Perplexity

---

## Token Usage Estimates

**Average Article Sizes:**
- Short blog (500 words): ~750 tokens
- Medium article (1500 words): ~2250 tokens
- Long-form (3000 words): ~4500 tokens
- Research article (5000 words): ~7500 tokens

**Cost Examples (using GPT-4o Mini at $0.15/$0.60):**
- 1000 short blogs: $1,125
- 1000 medium articles: $3,375
- 1000 long articles: $6,750

**Cost Examples (using DeepSeek Chat at $0.27/$1.10):**
- 1000 short blogs: $2,025
- 1000 medium articles: $6,075
- 1000 long articles: $12,150

---

## Implementation Notes

### Adding New Provider

1. Add to `app/Helpers/AiModelHelper.php`:
```php
'provider_name' => [
    'model-id' => 'Display Name',
],
```

2. Add API credentials in Admin Panel:
```
/admin/api-keys/create
Category: article_generation
Provider: provider_name
```

3. Implement in `app/Services/AIService.php`:
```php
private function callProviderAPI($provider, $model, $prompt, $options)
{
    switch($provider) {
        case 'provider_name':
            return $this->callProviderNameAPI($model, $prompt, $options);
    }
}
```

---

## Monitoring & Optimization

### Track These Metrics:
- Cost per article by model
- Average tokens used per article type
- Model success rate (articles requiring regeneration)
- User satisfaction by model
- Response time by provider

### Optimization Tips:
1. Use cheaper models for draft/bulk generation
2. Use premium models for final polish
3. Cache common prompts/templates
4. Implement token limits per article type
5. Monitor and adjust quotas based on usage patterns

---

**Last Updated:** December 2024
**Maintained By:** AneWP Development Team
