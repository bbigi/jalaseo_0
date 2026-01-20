<?php

return [
    'title' => 'Generator Artikel AI',
    'subtitle' => 'Hasilkan artikel berkualitas dengan bantuan AI',
    'form' => [
        'website' => 'Website Tujuan',
        'website_optional' => '(opsional)',
        'website_placeholder' => 'Pilih website...',
        'website_search' => 'Cari website...',

        'template' => 'Template',
        'template_placeholder' => 'Pilih template...',
        'template_search' => 'Cari template...',
        'template_create' => '+ Buat template baru',

        'generation_source' => 'Sumber Generasi',
        'generation_source_placeholder' => 'Generate dari Judul',
        'generation_source_search' => 'Cari sumber...',
        'generation_options' => [
            'title' => 'Generate dari Judul',
            'reference' => 'Generate dari Referensi (URL/Deskripsi)',
            'text' => 'Generate dari Teks (Input Manual)',
            'insight' => 'Generate dari Insight',
        ],

        'topic' => 'Topik/Kata Kunci',
        'topic_placeholder' => 'Masukkan topik atau kata kunci',
        'reference_button' => 'Cari Referensi',
        'autocomplete_help' => 'Autocomplete menggabungkan kata kunci tersimpan dan saran Google langsung.',

        'reference_topic' => 'Topik Referensi',
        'reference_topic_placeholder' => 'Topik utama untuk referensi',
        'reference_description' => 'Deskripsi Referensi',
        'reference_description_placeholder' => 'Deskripsi singkat atau poin penting',
        'reference_url' => 'URL Referensi (opsional)',
        'reference_url_placeholder' => 'https://contoh.com/artikel',

        'insight_select' => 'Pilih Insight',
        'insight_placeholder' => 'Pilih insight...',
        'insight_search' => 'Cari insight...',
        'insight_empty' => 'Tidak ada insight tersedia',
        'insight_help' => 'Topik dan deskripsi insight akan dipakai sebagai referensi.',

        'text_reference' => 'Teks Referensi',
        'text_reference_placeholder' => 'Tempel teks yang ingin diolah/dikembangkan AI...',
        'text_source_name' => 'Nama Sumber (opsional)',
        'text_source_name_placeholder' => 'mis. Wikipedia, Makalah Riset',

        'submit' => 'Generate Artikel',
    ],
];
