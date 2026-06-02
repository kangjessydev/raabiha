import re

# 1. Update page-dashboard.php fonts
try:
    with open('page-dashboard.php', 'r') as f:
        html = f.read()
    
    html = html.replace('family=Inter:wght@300;400;500;600;700;800', 'family=Poppins:wght@300;400;500;600;700')
    html = html.replace("'Inter'", "'Poppins'")
    
    with open('page-dashboard.php', 'w') as f:
        f.write(html)
except Exception as e:
    print(f"Error modifying page-dashboard.php: {e}")

# 2. Update tailwind.css font-family
try:
    with open('src/assets/css/tailwind.css', 'r') as f:
        css = f.read()
    
    css = re.sub(
        r'font-family:[^;]+;', 
        'font-family: "Poppins", sans-serif;', 
        css
    )
    with open('src/assets/css/tailwind.css', 'w') as f:
        f.write(css)
except Exception as e:
    print(f"Error modifying tailwind.css: {e}")

print("Successfully applied Poppins font.")
