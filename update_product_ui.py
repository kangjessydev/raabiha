import re

def update_product_detail():
    with open('resources/views/product-detail.blade.php', 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Title case for Product Name
    content = content.replace('text-3xl lg:text-5xl font-serif font-bold tracking-tight mb-2 mt-2 lg:mt-0 uppercase', 'text-3xl lg:text-5xl font-serif font-bold tracking-tight mb-2 mt-2 lg:mt-0 capitalize')
    
    # 2. Fix Add to Cart Button Row (Remove Quantity Picker, make buttons full width like mockup)
    add_to_cart_html = """
                            <!-- Add to Cart Widget -->
                            <div class="woocommerce-variation-add-to-cart variations_button w-full flex flex-col gap-3">
                                <!-- Desktop Add to Cart Button -->
                                <button type="button" id="desktop-add-to-cart-btn" class="hidden md:flex w-full h-14 bg-[#064e3b] text-white hover:bg-[#053e2f] flex-col items-center justify-center border-none transition-colors focus:outline-none">
                                    <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">TAMBAH KE KERANJANG</span>
                                </button>

                                <!-- Wishlist & Trust Badges -->
                                <button class="hidden lg:flex w-full border border-[#1c1c1a] h-14 text-[#1c1c1a] text-[10px] font-mono font-bold tracking-[0.2em] uppercase hover:bg-[#f2efe8] transition-colors justify-center items-center gap-2">
                                    WISHLIST PRODUCT
                                </button>
                                
                                <!-- MOBILE Sticky Action Bar -->
"""
    # Replace from <!-- Add to Cart Widget --> up to <!-- Wishlist & Trust Badges -->
    content = re.sub(r'<!-- Add to Cart Widget -->.*?<!-- MOBILE Sticky Action Bar -->', add_to_cart_html, content, flags=re.DOTALL)
    
    # Remove the old wishlist button since we moved it above
    content = re.sub(r'<!-- Wishlist & Trust Badges -->\s*<button class="hidden lg:flex w-full mt-2 border border-\[#1c1c1a\] h-14.*?WHISHLIST PRODUCT\s*</button>', '', content, flags=re.DOTALL)

    # 3. Update Accordions
    content = content.replace('02. Reviews', '02. Styling Tips')
    content = content.replace('ULASAN (0)', 'STYLING TIPS')
    content = content.replace('Belum ada ulasan untuk produk ini.', 'Cocok dipadukan dengan celana kulot lebar atau rok lipit untuk memberikan dimensi yang seimbang. Gunakan hijab dengan warna senada untuk tampilan monokromatik yang elegan.')
    content = content.replace('<div class="border border-[#e5e2de] p-6 bg-[#fcf9f5]">', '<div class="border border-[#e5e2de] p-6 bg-[#fcf9f5] hidden">') # Hide the review form

    # 4. Modify JS for Color Swatches and Vertical Gallery
    js_color_logic = """
            // Render colors options (Swatches)
            const colorGrid = document.getElementById('color-options-grid');
            const colorLabel = document.getElementById('selected-color-label');
            const colorMap = {
                'Charcoal': '#333333',
                'Slate Sand': '#d9cbb8',
                'Dusty Rose': '#c09891',
                'Off-White': '#f2efe8',
                'Green': '#064e3b'
            };

            if (colorGrid) {
                colorGrid.innerHTML = '';
                // If the product doesn't have Green but we want to show mockup, let's inject it for dummy
                let colors = currentProduct.variations.colors;
                if(currentProduct.id === 'monolith-overcoat' && !colors.includes('Green')) {
                    colors.push('Green');
                }

                colors.forEach((color, idx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    // Outer ring for selected state
                    btn.className = 'w-8 h-8 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 ';
                    
                    const hex = colorMap[color] || colorMap['Charcoal'];
                    const innerCircle = `<div class="w-full h-full rounded-full border border-black/10" style="background-color: ${hex}"></div>`;
                    btn.innerHTML = innerCircle;

                    if (idx === 0) {
                        btn.className += 'border-[#1c1c1a]'; // Selected ring
                        selectedColor = color;
                        if(colorLabel) colorLabel.textContent = color;
                    } else {
                        btn.className += 'border-transparent hover:border-gray-300';
                    }
                    
                    btn.addEventListener('click', function() {
                        // Deselect other
                        colorGrid.querySelectorAll('button').forEach(x => {
                            x.className = 'w-8 h-8 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 border-transparent hover:border-gray-300';
                        });
                        // Select current
                        this.className = 'w-8 h-8 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 border-[#1c1c1a]';
                        selectedColor = color;
                        if(colorLabel) colorLabel.textContent = color;
                    });
                    colorGrid.appendChild(btn);
                });
            }

            // Desktop Gallery: Vertical Stack
            const galleryContainer = document.getElementById('desktop-gallery-stack');
            if (galleryContainer && currentProduct.images.length > 0) {
                galleryContainer.innerHTML = '';
                currentProduct.images.forEach(img => {
                    const imgDiv = document.createElement('div');
                    imgDiv.className = 'w-full aspect-[4/5] bg-[#ebebeb] overflow-hidden relative mb-4';
                    imgDiv.innerHTML = `<img src="${img}" alt="Detail Produk" class="absolute inset-0 w-full h-full object-cover">`;
                    galleryContainer.appendChild(imgDiv);
                });
            }
"""
    content = re.sub(r'// Render colors options.*?// Gallery and thumbnails setup', js_color_logic + '\n\n            // Gallery and thumbnails setup', content, flags=re.DOTALL)

    # Modify Color selector HTML
    color_html = """
                            <!-- Color Select -->
                            <div class="mb-8" id="color-selector-container">
                                <label class="block text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase mb-2">Color: <span id="selected-color-label" class="font-normal text-[#615e57]"></span></label>
                                <div class="flex flex-wrap gap-3" id="color-options-grid">
                                    <!-- Dynamic colors -->
                                </div>
                            </div>
    """
    content = re.sub(r'<!-- Color Select -->.*?</div>\s*</div>', color_html, content, flags=re.DOTALL)

    # Modify Left Column Gallery HTML to support vertical stack
    gallery_html = """
                    <!-- Gallery View -->
                    <div id="desktop-gallery-stack" class="hidden lg:flex flex-col gap-4">
                        <!-- Desktop Vertical Stack injected via JS -->
                    </div>
                    <div class="lg:hidden sticky top-32">
                        <div class="w-full aspect-[4/5] bg-[#ebebeb] overflow-hidden relative">
                            <img id="main-gallery-image" src="" alt="Detail Produk" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                        
                        <!-- Thumbnail Wrapper -->
                        <div class="relative w-full group mt-6">
                            <ol id="gallery-thumbnails" class="flex flex-nowrap overflow-x-auto gap-4 p-0 m-0 list-none scroll-smooth scrollbar-none" style="scrollbar-width: none; -ms-overflow-style: none;">
                                <!-- Dynamically Rendered -->
                            </ol>
                            
                            <!-- Gallery navigation arrows -->
                            <div id="thumb-prev" class="absolute top-0 left-0 w-12 h-full text-[#1c1c1a] flex items-center justify-center cursor-pointer z-10 transition-colors bg-white/70 hover:bg-white/90 hidden">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </div>
                            <div id="thumb-next" class="absolute top-0 right-0 w-12 h-full text-[#1c1c1a] flex items-center justify-center cursor-pointer z-10 transition-colors bg-white/70 hover:bg-white/90 hidden">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </div>
                    </div>
    """
    content = re.sub(r'<!-- Gallery View -->.*?</div>\s*</div>\s*</div>', gallery_html + '</div>\n', content, flags=re.DOTALL)

    # 5. Fix Size Selector width (Mockup shows sizes filling the space or grid)
    # Actually, the mockup shows "SELECT SIZE" and sizes as big buttons.
    size_js = """
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'flex-1 py-3 text-[10px] font-mono border uppercase tracking-wider transition-all duration-200 ';
                    if (idx === 0) {
                        btn.className += 'border-[#1c1c1a] bg-[#1c1c1a] text-white font-bold';
                        selectedSize = size;
                    } else {
                        btn.className += 'border-[#e5e2de] text-[#1c1c1a] hover:border-[#1c1c1a]';
                    }
"""
    content = re.sub(r'const btn = document\.createElement\(\'button\'\);.*?if \(idx === 0\) \{', size_js + '                    if (idx === 0) {', content, flags=re.DOTALL)
    # Also fix deselect size classes
    content = re.sub(r'x\.className = \'px-4 py-2 text-\[10px\].*?hover:border-\[#1c1c1a\] transition-all duration-200\';', 'x.className = \'flex-1 py-3 text-[10px] font-mono border border-[#e5e2de] text-[#1c1c1a] uppercase tracking-wider hover:border-[#1c1c1a] transition-all duration-200\';', content)
    content = re.sub(r'this\.className = \'px-4 py-2 text-\[10px\].*?bg-\[#1c1c1a\] text-white font-bold uppercase tracking-wider transition-all duration-200\';', 'this.className = \'flex-1 py-3 text-[10px] font-mono border border-[#1c1c1a] bg-[#1c1c1a] text-white font-bold uppercase tracking-wider transition-all duration-200\';', content)
    
    # Change "Ukuran" to "SELECT SIZE" and "Panduan Ukuran" to "SIZE GUIDE"
    content = content.replace('Ukuran</label>', 'SELECT SIZE</label>')
    content = content.replace('Panduan Ukuran</span>', 'SIZE GUIDE</span>')

    # Change "Warna" to "COLOR"
    content = content.replace('Warna</label>', 'Color: <span id="selected-color-label" class="font-normal text-[#615e57]"></span></label>')

    with open('resources/views/product-detail.blade.php', 'w', encoding='utf-8') as out:
        out.write(content)

update_product_detail()
