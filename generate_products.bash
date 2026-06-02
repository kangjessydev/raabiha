#!/bin/bash
echo "Importing media..."
IMG1=$(wp media import raabiha-theme/assets/images/gallery-1.png --porcelain)
IMG2=$(wp media import raabiha-theme/assets/images/gallery-2.png --porcelain)
IMG3=$(wp media import raabiha-theme/assets/images/gallery-3.png --porcelain)
IMG4=$(wp media import raabiha-theme/assets/images/blog-coat.png --porcelain)
IMG5=$(wp media import raabiha-theme/assets/images/blog-white-suit.png --porcelain)
IMG6=$(wp media import raabiha-theme/assets/images/blog-objects.png --porcelain)
IMG7=$(wp media import raabiha-theme/assets/images/blog-hero.png --porcelain)

echo "Creating products..."
wp wc product create --name="KIMONO STRUCTURAL PARKA" --type="simple" --regular_price="1499000" --status="publish" --images='[{"id":'$IMG1'}]' --user=1
wp wc product create --name="MONOLITH OVERCOAT" --type="simple" --regular_price="2850000" --status="publish" --images='[{"id":'$IMG4'}]' --user=1
wp wc product create --name="RAW EDGE BOX TEE" --type="simple" --regular_price="449000" --status="publish" --images='[{"id":'$IMG3'}]' --user=1
wp wc product create --name="GEOMETRIC CARGO PANTS" --type="simple" --regular_price="1120000" --status="publish" --images='[{"id":'$IMG2'}]' --user=1
wp wc product create --name="COLUMN MAXI DRESS" --type="simple" --regular_price="1890000" --status="publish" --images='[{"id":'$IMG5'}]' --user=1
wp wc product create --name="SCULPTED HOODIE 02" --type="simple" --regular_price="899000" --status="publish" --images='[{"id":'$IMG6'}]' --user=1
wp wc product create --name="UTILITY BEANIE" --type="simple" --regular_price="299000" --status="publish" --images='[{"id":'$IMG7'}]' --user=1
wp wc product create --name="FIELD SHELL JACKET" --type="simple" --regular_price="2150000" --status="publish" --images='[{"id":'$IMG1'}]' --user=1
wp wc product create --name="MODEST URBAN COAT" --type="simple" --regular_price="3100000" --status="publish" --images='[{"id":'$IMG4'}]' --user=1
wp wc product create --name="ASYMMETRICAL TUNIC" --type="simple" --regular_price="950000" --status="publish" --images='[{"id":'$IMG3'}]' --user=1

echo "Done!"
