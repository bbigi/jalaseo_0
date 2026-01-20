<?php

return [
    'title' => 'Analisis Gap Kompetitor',
    'back' => 'Kembali ke Website',
    'no_competitors' => [
        'title' => 'Belum ada kompetitor',
        'description' => 'Tambah kompetitor di Analytics untuk melihat analisis gap.',
    ],
    'controls' => [
        'select_competitor' => 'Pilih Kompetitor',
        'primary' => 'Utama',
        'search_label' => 'Cari keyword',
        'search_placeholder' => 'Cari keyword...',
        'filters' => [
            'all' => 'Semua',
            'we_win' => 'Kita Menang',
            'opportunity' => 'Peluang',
            'content_gap' => 'Gap Konten',
            'low_priority' => 'Prioritas Rendah',
        ],
    ],
    'stats' => [
        'total_keywords' => [
            'title' => 'Total Keyword',
            'subtitle' => '200 keyword peringkat teratas dari kompetitor',
        ],
        'win_rate' => [
            'title' => 'Win Rate',
            'subtitle' => 'Peringkat kita lebih tinggi dari kompetitor',
        ],
        'median_delta' => [
            'title' => 'Delta Median',
            'subtitle' => 'Peringkat kita dikurangi peringkat kompetitor',
        ],
        'card_labels' => [
            'we_win' => 'Kita Menang',
            'opportunity' => 'Peluang',
            'content_gap' => 'Gap Konten',
            'low_priority' => 'Prioritas Rendah',
        ],
    ],
    'table' => [
        'keyword' => 'Keyword',
        'volume' => 'Volume',
        'our_position' => 'Posisi Kita',
        'competitor_position' => 'Posisi Kompetitor',
        'delta' => 'Delta',
        'competitor_url' => 'URL Kompetitor',
        'etv' => 'ETV',
        'actions' => 'Aksi',
        'badge' => [
            'ahead' => 'Unggul',
            'behind' => 'Tertinggal',
            'same' => 'Sama',
        ],
        'classification' => [
            'we_win' => 'Kita Menang',
            'opportunity' => 'Peluang',
            'content_gap' => 'Gap Konten',
            'low_priority' => 'Prioritas Rendah',
        ],
        'generate' => [
            'title' => 'Buat Artikel',
            'using_keyword' => 'Gunakan Keyword',
            'duplicate' => 'Duplikasi Konten',
        ],
        'save_track' => 'Simpan & Pantau',
        'empty' => 'Belum ada keyword yang cocok dengan filter ini.',
    ],
    'pagination' => [
        'showing' => 'Menampilkan :from sampai :to dari :total keyword',
        'prev' => 'Sebelumnya',
        'next' => 'Berikutnya',
        'per_page' => 'Per halaman',
    ],
    'recommendations' => [
        'title' => 'Rekomendasi AI',
        'subtitle' => 'Buat rencana prioritas dari data gap kompetitor Anda.',
        'button' => 'Generate Rekomendasi',
        'loading' => 'Sedang membuat rekomendasi...',
        'error' => 'Gagal membuat rekomendasi. Coba lagi.',
        'empty' => 'Belum ada keyword untuk dianalisis.',
        'last_generated' => 'Terakhir dibuat:',
    ],
];
