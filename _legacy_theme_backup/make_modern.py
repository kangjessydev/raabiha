import re

# 1. Add Google Fonts to index.html
try:
    with open('index.html', 'r') as f:
        html = f.read()
    
    if 'fonts.googleapis.com' not in html:
        font_link = '<link rel="preconnect" href="https://fonts.googleapis.com">\n    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\n    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">'
        html = html.replace('</head>', f'{font_link}\n  </head>')
        with open('index.html', 'w') as f:
            f.write(html)
except Exception as e:
    print(f"Error modifying index.html: {e}")

# 2. Update tailwind.css font-family
try:
    with open('src/assets/css/tailwind.css', 'r') as f:
        css = f.read()
    
    # Replace font-family line
    css = re.sub(
        r'font-family:[^;]+;', 
        'font-family: "Poppins", sans-serif;', 
        css
    )
    with open('src/assets/css/tailwind.css', 'w') as f:
        f.write(css)
except Exception as e:
    print(f"Error modifying tailwind.css: {e}")

# 3. Update App.vue rounded classes
try:
    with open('src/App.vue', 'r') as f:
        app = f.read()
    
    # Replace sharp borders with rounded-xl for containers and rounded-lg for small things
    app = app.replace('rounded-[2px]', 'rounded-xl')
    app = app.replace('rounded-[4px]', 'rounded-xl')
    # Update some text tracking since Poppins is a bit wider than Inter
    app = app.replace('tracking-widest', 'tracking-wider')
    
    with open('src/App.vue', 'w') as f:
        f.write(app)
except Exception as e:
    print(f"Error modifying App.vue: {e}")

print("Successfully applied Poppins font and modern rounded borders.")
