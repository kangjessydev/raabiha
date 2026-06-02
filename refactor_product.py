import re

with open('resources/views/livewire/product-detail.blade.php', 'r') as f:
    content = f.read()

# 1. Product Name
content = re.sub(
    r'<h1 id="product-name".*?>.*?</h1>',
    r'<h1 id="product-name" class="text-[#1c1c1a] text-2xl lg:text-5xl font-serif font-bold tracking-tight mb-2 mt-0 lg:mt-0 capitalize">\n                            {{ $product->name }}\n                        </h1>',
    content,
    flags=re.DOTALL
)

# 2. Breadcrumb category
content = re.sub(
    r'<span id="breadcrumb-category" class="text-\[#1c1c1a\]">.*?</span>',
    r'<span id="breadcrumb-category" class="text-[#1c1c1a]">{{ strtoupper($product->category->name ?? "KATEGORI") }}</span>',
    content
)

# 3. Product price
content = re.sub(
    r'<div id="main-product-price".*?>.*?</div>',
    r'<div id="main-product-price" class="text-[#615e57] text-lg md:text-3xl font-serif mb-10">\n                            Rp{{ number_format($product->price, 0, ",", ".") }}\n                        </div>',
    content,
    flags=re.DOTALL
)

# 4. Main Gallery Image
content = re.sub(
    r'<img id="main-gallery-image" src="" alt="Detail Produk"',
    r'<img id="main-gallery-image" src="{{ !empty($product->images) ? asset(\'storage/\' . $product->images[0]) : asset(\'/assets/images/gallery-1.png\') }}" alt="{{ $product->name }}"',
    content
)

# 5. Thumbnails
thumb_blade = """<ol id="gallery-thumbnails" class="flex flex-nowrap overflow-x-auto gap-3 lg:gap-4 p-0 px-6 md:px-0 m-0 list-none scroll-smooth scrollbar-none" style="scrollbar-width: none; -ms-overflow-style: none;">
                                @if(!empty($product->images))
                                    @foreach($product->images as $idx => $img)
                                        <li class="thumb-item relative shrink-0 cursor-pointer overflow-hidden bg-[#ebebeb] border {{ $idx === 0 ? 'border-[#1c1c1a] active' : 'border-transparent' }} transition-all" style="width: 20%; aspect-ratio: 4/5;">
                                            <img src="{{ asset('storage/' . $img) }}" alt="Thumb" class="w-full h-full object-cover pointer-events-none">
                                        </li>
                                    @endforeach
                                @endif
                            </ol>"""
content = re.sub(
    r'<ol id="gallery-thumbnails".*?>.*?</ol>',
    thumb_blade,
    content,
    flags=re.DOTALL
)

# 6. Description Accordion
desc_blade = """<div id="accordion-desc" class="mt-6 text-[#1c1c1a] text-[14px] leading-relaxed font-sans accordion-content">
                            <div class="space-y-6">
                                <p class="leading-relaxed">{!! nl2br(e($product->description)) !!}</p>
                            </div>
                        </div>"""
content = re.sub(
    r'<div id="accordion-desc".*?>.*?</div>',
    desc_blade,
    content,
    flags=re.DOTALL
)

# 7. Quantity Desktop
qty_html = """<input type="number" id="qty" wire:model.live="quantity" min="1" class="w-full text-center bg-transparent border-none focus:outline-none text-[#1c1c1a] font-mono text-[13px] appearance-none m-0" style="-moz-appearance: textfield;">"""
content = re.sub(
    r'<input type="number" id="qty" value="1" min="1"[^>]+>',
    qty_html,
    content
)

content = re.sub(
    r'onclick="document\.getElementById\(\'qty\'\)\.stepDown\(\)"',
    r'wire:click="decrementQuantity"',
    content
)
content = re.sub(
    r'onclick="document\.getElementById\(\'qty\'\)\.stepUp\(\)"',
    r'wire:click="incrementQuantity"',
    content
)

# 8. Add to Cart Desktop
content = re.sub(
    r'<button type="button" id="desktop-add-to-cart-btn".*?>',
    r'<button type="button" wire:click="addToCart" id="desktop-add-to-cart-btn" class="flex-1 h-full bg-[#064e3b] text-white hover:bg-[#053e2f] flex items-center justify-center gap-3 border-none transition-colors focus:outline-none">',
    content
)

# 9. Remove all <script> at the bottom.
content = re.sub(r'<script>.*?</script>', '', content, flags=re.DOTALL)

with open('resources/views/livewire/product-detail.blade.php', 'w') as f:
    f.write(content)

print("Blade file refactored successfully.")
