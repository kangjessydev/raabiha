<?php

$colorMap = [
    'coral' => '#FF7F50',
    'tortilla' => '#997950',
    'emerald' => '#50C878',
    'biscuit' => '#eed9c4',
    'black' => '#000000',
    'hitam' => '#000000',
    'blue jeans' => '#5dadec',
    'burgundy' => '#800020',
    'camel' => '#c19a6b',
    'candy' => '#ff0800',
    'choco pink' => '#e29a9a',
    'cloudy' => '#b0b5ba',
    'dark grey' => '#a9a9a9',
    'dark plum' => '#65314C',
    'deep blue' => '#00008b',
    'dove purple' => '#b8b4d0',
    'grey' => '#808080',
    'irish' => '#009e60',
    'isla' => '#8fbc8f',
    'khaki' => '#c3b091',
    'khaki tua' => '#8b7355',
    'kopi' => '#4b3621',
    'maroon' => '#800000',
    'mauve' => '#e0b0ff',
    'milo' => '#c9a98e',
    'millo' => '#c9a98e',
    'navy' => '#000080',
    'oat' => '#dfc8a6',
    'olive' => '#808000',
    'peanut' => '#795c34',
    'seen olive' => '#556b2f',
    'olive seen' => '#556b2f',
    'smoke' => '#738276',
    'soft beige' => '#f5f5dc',
    'thistle' => '#d8bfd8',
    'vanhouten' => '#7b3f00',
    'mahogany' => '#c04000',
    'soft mauve' => '#e0b0ff',
    'cream' => '#fffdd0',
    'sand' => '#c2b280',
    'almond' => '#efdecd',
    'blue denim' => '#5dadec',
    'bronze' => '#cd7f32',
    'bw' => '#f8f8ff',
    'fapuchino' => '#b5651d',
    'ivory' => '#fffff0',
    'mistyc grey' => '#708090',
    'stone grey' => '#928e85',
    'plum' => '#8e4585',
    'sage' => '#bcb88a',
    'latte' => '#C8A2C8', // example fallback
];

use App\Models\AttributeOption;
use App\Models\Attribute;

$colorAttribute = Attribute::where('name', 'Warna')->orWhere('slug', 'warna')->first();

if ($colorAttribute) {
    $options = AttributeOption::where('attribute_id', $colorAttribute->id)->get();
    $updated = 0;
    foreach ($options as $option) {
        $colorName = strtolower(trim($option->value));
        if (isset($colorMap[$colorName]) && empty($option->meta)) {
            $option->meta = $colorMap[$colorName];
            $option->save();
            echo "Updated {$option->value} to {$option->meta}\n";
            $updated++;
        } elseif (empty($option->meta)) {
            // Default random-ish color if not found but we need one
            $option->meta = '#' . substr(md5($colorName), 0, 6);
            $option->save();
            echo "Updated {$option->value} to default generated {$option->meta}\n";
            $updated++;
        }
    }
    echo "Total updated: $updated\n";
} else {
    echo "Attribute 'Warna' not found\n";
}
