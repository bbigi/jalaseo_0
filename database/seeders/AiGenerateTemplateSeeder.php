<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiGenerateTemplate;

class AiGenerateTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Blog Post - Comprehensive Guide',
                'description' => 'SEO-optimized blog post dengan struktur lengkap',
                'article_type' => 'blog',
                'language' => 'id',
                'is_active' => true,
                'prompt_template' => $this->getBlogPostPrompt(),
            ],
            [
                'name' => 'News Article - Breaking News',
                'description' => 'Artikel berita dengan gaya jurnalistik',
                'article_type' => 'news',
                'language' => 'id',
                'is_active' => true,
                'prompt_template' => $this->getNewsPrompt(),
            ],
            [
                'name' => 'Tutorial - Step by Step',
                'description' => 'Panduan tutorial dengan langkah-langkah detail',
                'article_type' => 'tutorial',
                'language' => 'id',
                'is_active' => true,
                'prompt_template' => $this->getTutorialPrompt(),
            ],
            [
                'name' => 'Listicle - Top 10 Format',
                'description' => 'Artikel daftar dengan format angka',
                'article_type' => 'listicle',
                'language' => 'id',
                'is_active' => true,
                'prompt_template' => $this->getListiclePrompt(),
            ],
            [
                'name' => 'Product Review',
                'description' => 'Review produk dengan analisis mendalam',
                'article_type' => 'review',
                'language' => 'id',
                'is_active' => true,
                'prompt_template' => $this->getReviewPrompt(),
            ],
        ];

        foreach ($templates as $template) {
            AiGenerateTemplate::create($template);
        }
    }

    private function getBlogPostPrompt(): string
    {
        return <<<'PROMPT'
Anda adalah penulis konten expert yang membuat blog post SEO-optimized dalam Bahasa Indonesia.

**Topik:** {{title}}

{{#if reference_description}}
**Informasi Referensi:**
{{reference_description}}
{{/if}}

{{#if url_content}}
**Konten Sumber:**
{{url_content}}
{{/if}}

{{#if reference_text}}
**Teks Referensi untuk Dikembangkan:**
{{reference_text}}

Atribusi: "dilansir dari {{source_name}}"
{{/if}}

**Instruksi:**
1. Tulis blog post komprehensif SEO-optimized (1500-2000 kata)
2. Gunakan heading jelas (H2, H3) untuk struktur
3. Sertakan tips actionable dan contoh praktis
4. Tulis dengan tone conversational dan engaging
5. Optimasi untuk keyword: {{title}}
6. Sertakan kesimpulan dengan call-to-action
7. Gunakan paragraf pendek (3-4 baris) untuk readability

**Format Output (STRICT JSON):**
{
  "title": "Judul artikel SEO-optimized",
  "content": "Konten HTML lengkap dengan format proper",
  "meta_description": "Meta description SEO (max 160 karakter)",
  "focus_keyword": "keyword utama",
  "featured_image_prompt": "Prompt untuk AI image generator"
}

Return ONLY valid JSON, no markdown, no explanation.
PROMPT;
    }

    private function getNewsPrompt(): string
    {
        return <<<'PROMPT'
Anda adalah jurnalis profesional yang menulis artikel berita dalam Bahasa Indonesia.

**Topik Berita:** {{title}}

{{#if reference_description}}
**Detail Berita:**
{{reference_description}}
{{/if}}

{{#if url_content}}
**Sumber Berita:**
{{url_content}}
{{/if}}

**Instruksi:**
1. Tulis artikel berita faktual (500-800 kata)
2. Gunakan struktur piramida terbalik (info penting di awal)
3. Sertakan 5W+1H (Who, What, When, Where, Why, How)
4. Tone objektif dan netral
5. Hindari opini pribadi
6. Sertakan kutipan jika ada dalam referensi

**Format Output (STRICT JSON):**
{
  "title": "Judul berita menarik dan informatif",
  "content": "Konten HTML artikel berita",
  "meta_description": "Ringkasan berita (max 160 karakter)",
  "focus_keyword": "keyword berita utama",
  "featured_image_prompt": "Prompt untuk featured image"
}

Return ONLY valid JSON.
PROMPT;
    }

    private function getTutorialPrompt(): string
    {
        return <<<'PROMPT'
Anda adalah instructor ahli yang membuat tutorial step-by-step dalam Bahasa Indonesia.

**Topik Tutorial:** {{title}}

{{#if reference_description}}
**Detail Tutorial:**
{{reference_description}}
{{/if}}

**Instruksi:**
1. Tulis tutorial detail dengan langkah-langkah numbered (1000-1500 kata)
2. Setiap langkah harus spesifik dan actionable
3. Sertakan screenshot/visual guide description jika perlu
4. Tambahkan tips & troubleshooting di akhir
5. Gunakan bahasa sederhana dan mudah dipahami
6. Struktur: Intro → Requirements → Steps → Conclusion

**Format Output (STRICT JSON):**
{
  "title": "Cara {{title}}: Panduan Lengkap Step-by-Step",
  "content": "Konten HTML tutorial dengan numbered steps",
  "meta_description": "Panduan lengkap {{title}} (max 160 chars)",
  "focus_keyword": "cara {{title}}",
  "featured_image_prompt": "Tutorial illustration for {{title}}"
}

Return ONLY valid JSON.
PROMPT;
    }

    private function getListiclePrompt(): string
    {
        return <<<'PROMPT'
Anda adalah content writer yang membuat artikel listicle engaging dalam Bahasa Indonesia.

**Topik:** {{title}}

{{#if reference_description}}
**Deskripsi:**
{{reference_description}}
{{/if}}

**Instruksi:**
1. Tulis artikel listicle dengan format angka (1200-1500 kata)
2. Minimal 7-10 poin dalam daftar
3. Setiap poin harus:
   - Punya sub-heading jelas
   - Penjelasan 2-3 paragraf
   - Contoh konkret jika relevan
4. Intro menarik yang preview semua poin
5. Conclusion yang rangkum key takeaways
6. Gunakan transition words antar poin

**Format Output (STRICT JSON):**
{
  "title": "[Angka] {{title}} yang [Adjektif]",
  "content": "Konten HTML dengan numbered list format",
  "meta_description": "Daftar {{title}} terbaik (max 160 chars)",
  "focus_keyword": "{{title}}",
  "featured_image_prompt": "Illustration for listicle about {{title}}"
}

Return ONLY valid JSON.
PROMPT;
    }

    private function getReviewPrompt(): string
    {
        return <<<'PROMPT'
Anda adalah reviewer profesional yang menulis review mendalam dalam Bahasa Indonesia.

**Produk/Topik Review:** {{title}}

{{#if reference_description}}
**Informasi Produk:**
{{reference_description}}
{{/if}}

{{#if reference_text}}
**Detail dari Referensi:**
{{reference_text}}
{{/if}}

**Instruksi:**
1. Tulis review komprehensif (1500-2000 kata)
2. Struktur:
   - Intro & First Impression
   - Spesifikasi/Features (bullet points)
   - Kelebihan (pros)
   - Kekurangan (cons)
   - Perbandingan dengan kompetitor
   - Kesimpulan & Rekomendasi
3. Tone honest dan balanced
4. Sertakan rating (X/10) di berbagai aspek
5. Akhiri dengan "Apakah Worth It?" section

**Format Output (STRICT JSON):**
{
  "title": "Review {{title}}: Apakah Worth It?",
  "content": "Konten HTML review dengan rating table",
  "meta_description": "Review lengkap {{title}} - Kelebihan, kekurangan (max 160)",
  "focus_keyword": "review {{title}}",
  "featured_image_prompt": "Product review image for {{title}}"
}

Return ONLY valid JSON.
PROMPT;
    }
}
