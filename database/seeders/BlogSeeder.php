<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada user admin
        $admin = User::first() ?? User::factory()->create();

        // 1. Buat Kategori
        $categories = [
            'Design Process' => PostCategory::firstOrCreate(['name' => 'Design Process', 'slug' => 'design-process']),
            'Style & Styling' => PostCategory::firstOrCreate(['name' => 'Style & Styling', 'slug' => 'style-styling']),
            'Architecture' => PostCategory::firstOrCreate(['name' => 'Architecture', 'slug' => 'architecture']),
            'Lifestyle' => PostCategory::firstOrCreate(['name' => 'Lifestyle', 'slug' => 'lifestyle']),
        ];

        // 2. Buat Tags
        $tags = [
            'Minimalism' => PostTag::firstOrCreate(['name' => 'Minimalism', 'slug' => 'minimalism']),
            'Modest Fashion' => PostTag::firstOrCreate(['name' => 'Modest Fashion', 'slug' => 'modest-fashion']),
            'Craftsmanship' => PostTag::firstOrCreate(['name' => 'Craftsmanship', 'slug' => 'craftsmanship']),
            'Editorial' => PostTag::firstOrCreate(['name' => 'Editorial', 'slug' => 'editorial']),
        ];

        // Prepare images for Curator
        $imageMap = [
            'hero' => 'blog-hero.png',
            'gallery2' => 'gallery-2.png',
            'gallery3' => 'gallery-3.png',
            'gallery4' => 'gallery-4.png',
            'coat' => 'blog-coat.png',
            'objects' => 'blog-objects.png',
        ];

        $mediaIds = [];
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('media')) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('media');
        }

        foreach ($imageMap as $key => $filename) {
            $sourcePath = public_path('assets/images/' . $filename);
            if (file_exists($sourcePath)) {
                $targetPath = 'media/' . $filename;
                \Illuminate\Support\Facades\Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
                
                $media = \Awcodes\Curator\Models\Media::firstOrCreate(
                    ['name' => pathinfo($filename, PATHINFO_FILENAME)],
                    [
                        'disk' => 'public',
                        'directory' => 'media',
                        'path' => $targetPath,
                        'width' => 1000,
                        'height' => 1000,
                        'size' => filesize($sourcePath),
                        'type' => mime_content_type($sourcePath),
                        'ext' => pathinfo($filename, PATHINFO_EXTENSION),
                        'alt' => $filename,
                        'title' => $filename,
                    ]
                );
                $mediaIds[$key] = $media->id;
            }
        }

        // 3. Data Artikel
        $articles = [
            [
                'title' => 'The Geometry of Modesty: Fall 2024 Design Philosophy',
                'category' => 'Design Process',
                'tags' => ['Modest Fashion', 'Minimalism'],
                'image_key' => 'hero',
                'content' => '
                    <p class="text-lg md:text-2xl text-[#1c1c1a] leading-relaxed mb-10">
                        Arsitektur bukan hanya soal membangun ruang fisik, tetapi juga ruang psikologis. Di Raabiha, kami melihat garmen sebagai "mini-arsitektur" yang memberikan batas, kenyamanan, dan pernyataan bagi pemakainya.
                    </p>
                    <h2 class="text-2xl md:text-4xl mt-12 md:mt-16 mb-6 md:mb-8 font-serif">Precision in Drapery</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Tantangan dalam mendesain modest fashion adalah bagaimana menciptakan ruang tanpa membuat siluet terlihat berat. Kami menemukan jawabannya melalui potongan bias dan manipulasi kain tiga dimensi. Alih-alih menyembunyikan, kami "membingkai".
                    </p>
                    <blockquote class="not-prose my-12 md:my-16 text-center px-0">
                        <p class="text-2xl md:text-4xl lg:text-5xl font-serif text-[#064e3b] leading-tight italic max-w-2xl mx-auto">
                            "Modesty is not the absence of presence, but the presence of architectural intent."
                        </p>
                    </blockquote>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Setiap lipatan memiliki tujuan. Seperti pilar yang menopang atap, setiap jahitan menopang struktur pakaian, memberikan kebebasan bergerak sekaligus mempertahankan bentuk yang sempurna.
                    </p>
                ',
                'focus_keyword' => 'modest fashion design',
                'meta_description' => 'Mengeksplorasi titik temu antara siluet tradisional dan struktur arsitektural modern dalam koleksi terbaru Raabiha.',
                'date' => now(),
            ],
            [
                'title' => 'Precision in Pattern Making: The Structural Sleeve',
                'category' => 'Design Process',
                'tags' => ['Craftsmanship', 'Minimalism'],
                'image_key' => 'gallery3',
                'content' => '
                    <p>Di balik desain sutra premium kami, terdapat perhitungan matematis untuk menciptakan drapery yang sempurna tanpa mengorbankan volume.</p>
                    <h2>The Mathematics of Fabric</h2>
                    <p>Memotong pola untuk busana modest membutuhkan pemahaman geometri yang mendalam. Sebuah lengan tidak hanya sekadar kain penutup, melainkan struktur silinder yang harus mampu mengakomodasi rentang gerak tanpa kehilangan bentuk asalnya.</p>
                    <p>Kami menghabiskan ratusan jam di studio hanya untuk menyempurnakan jatuhnya kain pada bagian bahu. Teknik ini terinspirasi dari struktur lipatan origami, di mana setiap garis memiliki ketegangan (tension) yang saling menopang.</p>
                ',
                'focus_keyword' => 'pattern making',
                'meta_description' => 'Di balik desain silk premium Raabiha, terdapat perhitungan matematis untuk menciptakan drapery yang sempurna tanpa mengorbankan volume.',
                'date' => now()->subDays(2),
            ],
            [
                'title' => 'The Art of Visual Layering',
                'category' => 'Style & Styling',
                'tags' => ['Modest Fashion', 'Editorial'],
                'image_key' => 'coat',
                'content' => '
                    <p>Bagaimana menggabungkan tekstur wol berat dengan siluet ramping untuk menciptakan kedalaman desain yang elegan.</p>
                    <h2>Mastering Textures</h2>
                    <p>Layering seringkali disalahartikan sebagai penumpukan pakaian semata. Padahal, layering adalah seni menyeimbangkan berat visual. Kunci dari visual layering yang elegan adalah kontras tekstur.</p>
                    <ul>
                        <li><strong>Base Layer:</strong> Gunakan material ringan seperti katun mercerized atau sutra.</li>
                        <li><strong>Mid Layer:</strong> Masukkan elemen struktural seperti vest linen berpotongan kaku.</li>
                        <li><strong>Outer Layer:</strong> Sempurnakan dengan mantel wol atau trench coat berpotongan loose.</li>
                    </ul>
                    <p>Warna monokromatik menjadi sahabat terbaik dalam layering, membiarkan tekstur yang berbicara alih-alih warna.</p>
                ',
                'focus_keyword' => 'visual layering',
                'meta_description' => 'Bagaimana menggabungkan tekstur wol berat dengan siluet ramping untuk menciptakan kedalaman desain yang elegan.',
                'date' => now()->subDays(5),
            ],
            [
                'title' => 'Brutalist Spaces and the Point of Design',
                'category' => 'Architecture',
                'tags' => ['Minimalism', 'Editorial'],
                'image_key' => 'gallery2',
                'content' => '
                    <p>Mengapa beton dan kekosongan menjadi inspirasi utama di balik estetika Modest Architectural kami.</p>
                    <h2>Finding Warmth in Cold Concrete</h2>
                    <p>Brutalisme sering dianggap dingin dan tidak ramah. Namun, bagi kami, blok-blok beton raksasa dan kekosongan ruang menawarkan kanvas yang sangat hening. Keheningan inilah yang kami terjemahkan ke dalam pakaian.</p>
                    <p>Sebuah busana Raabiha tidak butuh ornamen atau payet yang berlebihan. Kekuatannya terletak pada bentuk yang solid, material yang jujur, dan ruang yang diciptakannya di sekitar tubuh. Modesty adalah ruang, dan kami adalah arsiteknya.</p>
                ',
                'focus_keyword' => 'brutalist design',
                'meta_description' => 'Mengapa beton dan kekosongan menjadi inspirasi utama di balik estetika Modest Architectural kami.',
                'date' => now()->subDays(8),
            ],
            [
                'title' => 'Behind the Bespoke: Our Fitting Process',
                'category' => 'Design Process',
                'tags' => ['Craftsmanship', 'Behind the Scenes'],
                'image_key' => 'objects',
                'content' => '
                    <p>Melihat lebih dekat standar ketat dan perhatian terhadap detail dalam proses pembuatan busana made-to-order kami.</p>
                    <h2>The Fitting Ritual</h2>
                    <p>Bagi klien bespoke kami, proses fitting adalah sebuah ritual. Ini bukan sekadar mengukur lingkar pinggang atau panjang lengan. Ini adalah percakapan antara klien, desainer, dan cermin.</p>
                    <p>Kami menggunakan kain muslin (toile) untuk draf pertama, memastikan bahwa arsitektur pakaian bekerja harmonis dengan postur unik setiap individu. Tidak ada dua pesanan bespoke yang dipotong dengan pola yang persis sama.</p>
                ',
                'focus_keyword' => 'bespoke fitting',
                'meta_description' => 'Melihat lebih dekat standar ketat dan perhatian terhadap detail dalam proses pembuatan busana made-to-order kami.',
                'date' => now()->subDays(12),
            ],
        ];

        foreach ($articles as $data) {
            $imageId = isset($mediaIds[$data['image_key']]) ? $mediaIds[$data['image_key']] : null;

            $post = Post::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'user_id' => $admin->id,
                    'post_category_id' => $categories[$data['category']]->id,
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'is_published' => true,
                    'published_at' => $data['date'],
                    'meta_title' => $data['title'],
                    'meta_description' => $data['meta_description'],
                    'focus_keyword' => $data['focus_keyword'],
                    'image' => $imageId,
                ]
            );

            $tagIds = array_map(function($tagName) use ($tags) {
                return $tags[$tagName]->id ?? null;
            }, $data['tags']);

            $post->tags()->sync(array_filter($tagIds));
        }
    }
}
