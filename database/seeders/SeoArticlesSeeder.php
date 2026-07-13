<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeoArticlesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dapatkan atau buat user admin sebagai penulis
        $admin = User::where('email', 'admin@raabiha.com')->first() 
            ?? User::first() 
            ?? User::factory()->create([
                'name' => 'Raabiha Admin',
                'email' => 'admin@raabiha.com',
                'password' => bcrypt('password'),
            ]);

        // 2. Pemetaan Gambar untuk Curator (Filament Media Manager)
        $imageMap = [
            'earth_tone' => 'blog-pool.png',
            'dress_kondangan' => 'lookbook_hero_1779445259756.png',
            'merawat_hijab' => 'blog-objects.png',
            'capsule_wardrobe' => 'gallery-5.png',
            'aksesoris' => 'blog-white-suit.png',
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

        // 3. Data Lengkap Artikel 6-10 dengan AI Generated Content
        $articles = [
            [
                'title' => 'Cara Tampil Estetik dengan Outfit Hijab Earth Tone',
                'slug' => 'outfit-hijab-earth-tone',
                'category' => 'Color Guide',
                'tags' => ['Warna Earth Tone', 'Padupadan Warna', 'Aesthetic Hijab'],
                'image_key' => 'earth_tone',
                'focus_keyword' => 'outfit hijab earth tone',
                'meta_title' => 'Cara Tampil Estetik dengan Outfit Hijab Earth Tone | Raabiha',
                'meta_description' => 'Dapatkan tampilan alami dan menenangkan dengan inspirasi outfit hijab earth tone. Pelajari kombinasi warna terakota, olive, dan beige ala Raabiha.',
                'content' => '
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Pernahkah Anda menyadari bagaimana alam selalu menyuguhkan warna-warna yang menenangkan mata? Dari pasir pantai yang hangat, dedaunan hijau sage yang lembut, hingga tanah terakota yang kokoh. Dalam dunia busana, kombinasi warna ini dikenal sebagai <em>earth tone</em>. Bagi wanita berhijab modern, mengenakan outfit dengan palet warna bumi tidak hanya memberikan kesan estetik yang instan, tetapi juga memancarkan aura ketenangan, kedewasaan, dan keanggunan yang bersahaja.
                    </p>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Filosofi Warna Bumi dalam Busana Modest</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Warna-warna bumi memiliki keunikan tersenditim di mana mereka tidak menonjol secara berlebihan, melainkan melebur dengan indah. Karakter ini sangat cocok dengan konsep busana modest yang mengedepankan kesederhanaan namun tetap terlihat berkelas. Pilihan warna seperti beige, sand (krem pasir), terracotta (terakota), sage green, dan cocoa memberikan kesan "bersih" dan minimalis yang sangat diminati oleh wanita modern saat ini.
                    </p>
                    <blockquote class="my-10 border-l-4 border-[#064e3b] pl-4 italic text-[#064e3b] font-serif text-lg">
                        "Kombinasi warna alam adalah perpaduan kehangatan dan keanggunan yang bersahaja, memancarkan kedamaian batin bagi pemakainya."
                    </blockquote>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Tips Padupadan Outfit Hijab Earth Tone Agar Tidak Kusam</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Salah satu kekhawatiran terbesar saat mengenakan warna bumi adalah tampilan yang terlihat pucat atau kusam. Untuk menghindarinya, Anda bisa menerapkan beberapa panduan berikut:
                    </p>
                    <ul class="list-disc pl-6 mb-6 text-[#615e57] space-y-2">
                        <li><strong>Gunakan Gradasi Warna (Tonal Styling):</strong> Padukan tunik berwarna beige terang dengan celana kulot berwarna cocoa yang lebih gelap. Gradasi ini menciptakan dimensi agar tampilan tidak terlihat datar.</li>
                        <li><strong>Beri Sentuhan Kontras yang Lembut:</strong> Cobalah memadukan atasan sage green dengan hijab berwarna sand. Kedua warna ini saling melengkapi tanpa saling mendominasi.</li>
                        <li><strong>Mainkan Tekstur Bahan:</strong> Kombinasikan katun poplin yang berstruktur tegas dengan hijab plisket atau sutra yang lembut untuk menambah nilai estetik pada keseluruhan penampilan Anda.</li>
                    </ul>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Mencerminkan Karakter Tenang dan Modern</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Pada akhirnya, busana modest bukan hanya tentang menutup diri, melainkan juga tentang mengekspresikan karakter. Gaya <em>earth tone</em> mencerminkan wanita yang tangguh namun lembut, menyukai keteraturan, dan menghargai detail-detail kecil yang minimalis. Siapkah Anda mencoba paduan warna bumi hari ini? Jelajahi koleksi busana modest bumi terbaik hanya di katalog produk Raabiha.
                    </p>
                '
            ],
            [
                'title' => 'Inspirasi Dress Kondangan Hijab Simple tapi Mewah',
                'slug' => 'dress-kondangan-hijab-simple',
                'category' => 'Formal Wear',
                'tags' => ['Dress Kondangan', 'Hijab Simple', 'Gaya Elegan'],
                'image_key' => 'dress_kondangan',
                'focus_keyword' => 'dress kondangan hijab simple',
                'meta_title' => 'Inspirasi Dress Kondangan Hijab Simple tapi Mewah | Raabiha',
                'meta_description' => 'Bingung memilih baju pesta? Temukan inspirasi dress kondangan hijab simple namun terlihat mewah berkat siluet minimalis dan bahan premium.',
                'content' => '
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Menghadiri acara pesta atau kondangan sering kali membuat kita bingung dalam memilih busana. Banyak wanita berhijab mengira bahwa tampil mewah ke pesta harus menggunakan pakaian yang penuh dengan payet, brokat berkilau, atau renda bertumpuk. Padahal, tren modest wear saat ini telah bergeser ke arah kemewahan yang bersahaja (<em>understated luxury</em>). Dress kondangan hijab yang simpel dengan potongan minimalis dan material berkualitas tinggi kini menjadi lambang keanggunan sejati.
                    </p>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Mengapa Siluet Simple Terlihat Lebih Mewah?</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Siluet gaun yang simpel memberikan ruang bagi mata untuk fokus pada kualitas potongan (<em>cutting</em>) dan keindahan natural dari bahan kain itu sendiri. Potongan asimetris, teknik <em>drapery</em> yang presisi, atau potongan A-line yang menyapu lantai dengan anggun dapat menciptakan siluet tubuh yang tinggi dan jenjang tanpa perlu dekorasi yang ramai. Gaya minimalis ini tidak hanya membuat Anda terlihat berkelas, tetapi juga sangat nyaman dikenakan selama acara berlangsung.
                    </p>
                    <blockquote class="my-10 border-l-4 border-[#064e3b] pl-4 italic text-[#064e3b] font-serif text-lg">
                        "Kemewahan sejati tidak terletak pada ramainya manik-manik yang menempel, melainkan pada presisi jahitan dan keindahan jatuhnya bahan."
                    </blockquote>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Panduan Memilih Bahan Premium untuk Gaun Pesta</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Kunci utama agar gaun simpel terlihat mewah terletak pada pemilihan material kain. Pastikan Anda memilih kain yang memiliki karakteristik premium berikut:
                    </p>
                    <ul class="list-disc pl-6 mb-6 text-[#615e57] space-y-2">
                        <li><strong>Satin Silk Premium:</strong> Memiliki kilau yang lembut dan tidak mencolok, serta memiliki efek jatuh (<em>flowy</em>) yang sangat anggun saat Anda berjalan.</li>
                        <li><strong>Katun Poplin Jepang Berkualitas Tinggi:</strong> Memberikan struktur gaun yang tegas dan kokoh pada bagian kerah atau lengan balon, sangat cocok untuk gaya formal yang kontemporer.</li>
                        <li><strong>Chiffon Ceruti:</strong> Memberikan efek melayang (layering) yang manis dan feminin tanpa menambah beban volume pakaian Anda secara berlebihan.</li>
                    </ul>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Sentuhan Akhir yang Menyempurnakan Gaya</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Untuk melengkapi dress pesta simpel Anda, gunakan hijab polos dari bahan satin atau voal premium dengan warna yang senada. Padukan dengan tas tangan terstruktur (<em>clutch</em>) beraksen logam tipis berwarna emas, serta sepatu berhak tinggi berdesain minimalis. Dengan paduan yang pas, Anda siap mencuri perhatian di acara pesta dengan penampilan yang sangat menawan dan anggun bersama koleksi eksklusif Raabiha.
                    </p>
                '
            ],
            [
                'title' => 'Cara Mencuci Hijab Sutra & Premium Agar Awet',
                'slug' => 'cara-mencuci-hijab-sutra',
                'category' => 'Fabric Care',
                'tags' => ['Merawat Hijab', 'Hijab Sutra', 'Edukasi Bahan'],
                'image_key' => 'merawat_hijab',
                'focus_keyword' => 'cara mencuci hijab sutra',
                'meta_title' => 'Cara Mencuci Hijab Sutra & Premium Agar Awet | Raabiha',
                'meta_description' => 'Jangan sampai hijab premium Anda rusak! Pelajari cara mencuci hijab sutra dan sifon yang benar agar warnanya tetap cerah dan seratnya tidak rusak.',
                'content' => '
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Hijab berbahan sutra (silk) dan sifon premium adalah investasi fashion yang berharga bagi wanita modest. Serat kainnya yang lembut, kilaunya yang anggun, serta kenyamanannya saat dipakai seharian membuat hijab ini sering menjadi andalan untuk acara formal maupun gaya kasual berkelas. Namun, kain premium membutuhkan perhatian yang ekstra dalam perawatannya. Kesalahan kecil saat mencuci dapat merusak kehalusan serat sutra, memudarkan kilau kain, bahkan membuat hijab Anda berbulu atau robek.
                    </p>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Langkah 1: Hindari Mesin Cuci dan Pemutih</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Aturan nomor satu dalam merawat hijab sutra adalah **jangan pernah mencucinya menggunakan mesin cuci**. Putaran mesin cuci terlalu kasar untuk serat sutra yang halus. Selain itu, hindari penggunaan detergen keras atau cairan pemutih pakaian karena zat kimianya dapat merusak warna alami kain dan melemahkan kekuatan serat kain secara permanen.
                    </p>
                    <blockquote class="my-10 border-l-4 border-[#064e3b] pl-4 italic text-[#064e3b] font-serif text-lg">
                        "Merawat serat kain halus seperti sutra adalah bentuk apresiasi terhadap keindahan karya seni tekstil, menjaga kilaunya tetap abadi."
                    </blockquote>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Langkah 2: Cuci Menggunakan Tangan dengan Sampo Bayi</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Sebagai gantinya, cucilah hijab Anda secara manual menggunakan tangan di dalam wadah berisi air dingin. Larutkan sedikit sampo bayi atau sabun cair pencuci kain halus. Celupkan hijab dan usap dengan lembut menggunakan jari. Jangan sekali-kali mengucek atau memeras hijab sutra dengan keras. Cukup remas-remas perlahan untuk membersihkan kotoran yang menempel.
                    </p>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Langkah 3: Menjemur di Tempat Teduh & Menyetrika Suhu Rendah</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Setelah dibilas bersih dengan air dingin, jangan memeras hijab dengan memutarnya. Letakkan hijab di atas handuk kering bersih, gulung handuk perlahan untuk menyerap kelebihan air. Jemurlah hijab di tempat yang teduh dengan diangin-anginkan saja. Paparan sinar matahari langsung dapat membuat warna hijab sutra cepat memudar. Saat menyetrika, gunakan pengaturan suhu khusus sutra (rendah) dan lapisi hijab dengan selembar kain tipis di atasnya untuk menghindari kontak panas langsung.
                    </p>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Dengan menerapkan panduan perawatan ini secara disiplin, koleksi hijab sutra premium Anda akan selalu tampak seperti baru, mempertahankan tekstur lembutnya, dan siap menemani hari-hari Anda dengan keanggunan abadi bersama Raabiha.
                    </p>
                '
            ],
            [
                'title' => 'Cara Membangun Capsule Wardrobe Modest untuk Pemula',
                'slug' => 'capsule-wardrobe-modest-hijab',
                'category' => 'Lifestyle',
                'tags' => ['Capsule Wardrobe', 'Hijab Minimalis', 'Gaya Hidup'],
                'image_key' => 'capsule_wardrobe',
                'focus_keyword' => 'capsule wardrobe modest hijab',
                'meta_title' => 'Cara Membangun Capsule Wardrobe Modest untuk Pemula | Raabiha',
                'meta_description' => 'Hemat waktu berdandan dan kurangi tumpukan baju dengan membuat capsule wardrobe modest hijab. Temukan 9 item fashion wajib yang harus Anda miliki.',
                'content' => '
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Pernahkah Anda menatap lemari pakaian yang penuh sesak namun merasa "tidak punya baju untuk dipakai"? Masalah klasik ini sering kali muncul karena kita membeli pakaian secara impulsif tanpa memikirkan bagaimana cara memadupadankannya di rumah. Konsep <em>capsule wardrobe</em> hadir sebagai solusi gaya hidup minimalis. Dengan memiliki sedikit item pakaian berkualitas tinggi yang saling melengkapi, Anda bisa menciptakan puluhan gaya berbeda dengan mudah sekaligus menghemat waktu berdandan setiap pagi.
                    </p>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Langkah Awal Membangun Capsule Wardrobe Modest</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Untuk wanita berhijab, kunci utama membangun <em>capsule wardrobe</em> adalah memilih pakaian yang longgar, tidak transparan, namun memiliki potongan yang rapi dan terstruktur. Langkah pertamanya adalah menetapkan palet warna dasar yang netral, seperti hitam, putih, beige, sand, atau abu-abu arang. Warna-warna netral ini sangat mudah dikombinasikan satu sama lain tanpa terlihat membosankan.
                    </p>
                    <blockquote class="my-10 border-l-4 border-[#064e3b] pl-4 italic text-[#064e3b] font-serif text-lg">
                        "Memiliki lebih sedikit pakaian berkualitas tinggi membawa kejernihan pikiran di pagi hari dan mengurangi konsumerisme berlebih."
                    </blockquote>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">9 Item Esensial Modest Capsule Wardrobe</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Sebagai pemula, berikut adalah 9 item fashion dasar yang wajib ada di gantungan lemari pakaian minimalis Anda:
                    </p>
                    <ul class="list-disc pl-6 mb-6 text-[#615e57] space-y-2">
                        <li><strong>Kemeja Oversized Putih:</strong> Item paling serbaguna yang bisa dipakai sebagai atasan formal maupun luaran (outer) santai.</li>
                        <li><strong>Gamis Polos Hitam:</strong> Gaun dasar yang anggun untuk berbagai acara, mudah dipadukan dengan aksesoris apa pun.</li>
                        <li><strong>Celana Palazzo Beige:</strong> Memberikan kenyamanan bergerak maksimal dengan potongan kaki lebar yang santun.</li>
                        <li><strong>Blazer Struktur Netral:</strong> Menambahkan kesan profesional secara instan saat menghadiri rapat kerja.</li>
                        <li><strong>Cardigan Knit Panjang:</strong> Memberikan lapisan kehangatan yang manis untuk aktivitas luar ruangan kasual.</li>
                        <li><strong>Hijab Voal Warna Sand & Cocoa:</strong> Hijab netral harian yang nyaman dan mudah disesuaikan dengan segala baju.</li>
                    </ul>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">Investasi pada Kualitas, Bukan Kuantitas</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Memilih gaya hidup minimalis berarti Anda lebih mengutamakan kualitas bahan dan jahitan daripada kuantitas pakaian murah yang cepat rusak. Pakaian berkualitas dari brand tepercaya seperti Raabiha dirancang menggunakan serat kain premium pilihan yang awet bertahun-tahun, sehingga tidak hanya menghemat anggaran belanja jangka panjang tetapi juga ramah lingkungan. Mari mulai susun koleksi capsule wardrobe modest Anda hari ini!
                    </p>
                '
            ],
            [
                'title' => 'Tips Memilih Aksesoris Gamis Polos Agar Lebih Berkelas',
                'slug' => 'aksesoris-gamis-polos',
                'category' => 'Styling Tips',
                'tags' => ['Aksesoris Gamis', 'Gamis Polos', 'Hijab Chic'],
                'image_key' => 'aksesoris',
                'focus_keyword' => 'aksesoris gamis polos',
                'meta_title' => 'Tips Memilih Aksesoris Gamis Polos Agar Lebih Berkelas | Raabiha',
                'meta_description' => 'Buat tampilan gamis polos Anda jauh lebih mewah dan berkelas dengan sentuhan aksesoris minimalis yang tepat. Baca tips lengkapnya di blog Raabiha.',
                'content' => '
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Gamis polos dengan warna solid sering kali dianggap sebagai busana rumah atau pakaian santai yang terlalu sederhana. Namun, di mata seorang desainer fashion, gamis polos adalah kanvas kosong yang sempurna. Melalui sentuhan aksesoris yang terkurasi dengan matang, sebuah gamis polos harian dapat disulap menjadi pakaian pesta yang luar biasa chic, elegan, dan memancarkan kemewahan berkelas tanpa terkesan berlebihan.
                    </p>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">1. Kalung Rantai Tipis yang Panjang (Long Minimalist Necklace)</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Untuk memberikan aksen garis vertikal yang menawan pada gamis polos Anda, pakailah kalung rantai tipis berwarna emas atau perak dengan liontin geometris kecil di bagian ujungnya. Kalung yang menjuntai hingga ke dada ini tidak hanya memecah kekosongan warna gamis, tetapi juga memberikan ilusi tubuh yang terlihat lebih ramping dan jenjang secara halus.
                    </p>
                    <blockquote class="my-10 border-l-4 border-[#064e3b] pl-4 italic text-[#064e3b] font-serif text-lg">
                        "Aksesoris pada busana gamis polos bertindak sebagai aksen penyeimbang estetika, memancarkan detail keindahan tanpa mendominasi penampilan."
                    </blockquote>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">2. Bros Geometris Minimalis pada Dada atau Hijab</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Bros adalah salah satu aksesoris paling klasik dalam modest wear. Namun, hindari bros berbentuk bunga besar yang penuh dengan manik-manik warna-warni karena dapat merusak konsep minimalis. Pilihlah bros bermotif geometris (lingkaran, garis, atau persegi abstrak) berlapis logam solid. Pasang bros ini di sisi dada gamis atau untuk mengancingkan lipatan hijab di pundak Anda untuk sentuhan akhir yang mewah.
                    </p>
                    <h2 class="text-2xl font-serif text-[#1c1c1a] mt-12 mb-6">3. Ikat Pinggang Kulit Tipis (Thin Leather Belt)</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Gamis dengan potongan lurus longgar (loose dress) bisa disempurnakan bentuknya dengan sabuk kulit tipis berwarna netral (seperti cokelat tua atau hitam) dengan gesper logam kecil. Mengikat sabuk tipis ini tepat di batas pinggang akan mempertegas siluet tubuh Anda secara sopan dan memberikan kesan gaya berpakaian yang rapi serta kontemporer.
                    </p>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Ingatlah aturan emas dalam bergaya minimalis: <em>less is more</em>. Cukup pilih satu atau maksimal dua aksesoris penyeimbang di atas agar konsep keanggunan bersahaja tetap terjaga. Temukan berbagai inspirasi padupadan gamis premium polos terbaik Anda hanya di jurnal gaya busana Raabiha.
                    </p>
                '
            ]
        ];

        // 4. Proses Seeding Artikel
        foreach ($articles as $artData) {
            // A. Dapatkan atau buat Kategori
            $category = PostCategory::firstOrCreate(
                ['name' => $artData['category']],
                ['slug' => Str::slug($artData['category'])]
            );

            // B. Cari ID Gambar dari curator media map
            $imageId = $mediaIds[$artData['image_key']] ?? null;

            // C. Buat atau update Artikel
            $post = Post::updateOrCreate(
                ['slug' => $artData['slug']],
                [
                    'post_category_id' => $category->id,
                    'user_id' => $admin->id,
                    'title' => $artData['title'],
                    'content' => trim($artData['content']),
                    'image' => $imageId,
                    'is_published' => true,
                    'meta_title' => $artData['meta_title'],
                    'meta_description' => $artData['meta_description'],
                    'focus_keyword' => $artData['focus_keyword'],
                    'published_at' => now(),
                ]
            );

            // D. Hubungkan Tags
            $tagIds = [];
            foreach ($artData['tags'] as $tagName) {
                $tag = PostTag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }
    }
}
