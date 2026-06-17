<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\AttributeOption;
use Illuminate\Support\Str;

#[Signature('raabiha:assign-colors')]
#[Description('Otomatis mengisi meta hex code untuk opsi atribut warna berdasarkan namanya')]
class AssignColorsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dictionary of colors (Fashion + Standard)
        $colorDictionary = [
            'charcoal' => '#36454F',
            'navy' => '#000080',
            'maroon' => '#800000',
            'sage green' => '#879D7C',
            'sage' => '#B2AC88',
            'mustard' => '#FFDB58',
            'taupe' => '#483C32',
            'dusty pink' => '#DCAE96',
            'cream' => '#FFFDD0',
            'black' => '#000000',
            'hitam' => '#000000',
            'white' => '#FFFFFF',
            'putih' => '#FFFFFF',
            'grey' => '#808080',
            'abu' => '#808080',
            'abu-abu' => '#808080',
            'olive' => '#808000',
            'mocca' => '#a38068',
            'moka' => '#a38068',
            'coklat' => '#964B00',
            'brown' => '#964B00',
            'khaki' => '#C3B091',
            'beige' => '#F5F5DC',
            'terracotta' => '#E2725B',
            'burgundy' => '#800020',
            'lilac' => '#C8A2C8',
            'lavender' => '#E6E6FA',
            'emerald' => '#50C878',
            'tosca' => '#8BBEB2',
            'peach' => '#FFE5B4',
            'salmon' => '#FA8072',
            'gold' => '#FFD700',
            'silver' => '#C0C0C0',
            'bronze' => '#CD7F32',
            'milo' => '#c2a58b',
            'army' => '#4b5320',
            'wardah' => '#b8d6d3',
            'latte' => '#e8cdb5',
            'caramel' => '#c5834c',
            'nude' => '#e3bc9a',
            'denim' => '#1560bd',
            'bw' => '#f8f8f8',
            'broken white' => '#f8f8f8',
            'fuchsia' => '#FF00FF',
            'pink' => '#FFC0CB',
            'rose' => '#FF007F',
            'rose gold' => '#B76E79',
            'teal' => '#008080',
            'mint' => '#98FF98',
            'coral' => '#FF7F50',
            'lime' => '#BFFF00',
            'lemon' => '#FFF700',
            'plum' => '#8E4585',
            'ruby' => '#E0115F',
            'sapphire' => '#0F52BA',
            'jade' => '#00A86B',
            'turquoise' => '#40E0D0',
            'magenta' => '#FF00FF',
            'cyan' => '#00FFFF',
            'indigo' => '#4B0082',
            'violet' => '#8F00FF',
            'baby blue' => '#89CFF0',
            'baby pink' => '#F4C2C2',
            'mustard yellow' => '#E1AD01',
            'brick red' => '#CB4154',
            'copper' => '#B87333',
            'rust' => '#B7410E',
            'tan' => '#D2B48C',
            'camel' => '#C19A6B',
            'sand' => '#C2B280',
            'ivory' => '#FFFFF0',
            'espresso' => '#4B3621',
            'mocha' => '#A38068',
            'cappuccino' => '#6A4B3A',
            'wine' => '#722F37',
            'bordeaux' => '#4C1C24',
            'champagne' => '#F7E7CE',
            'slate' => '#708090',
            'ash' => '#B2BEB5',
            'stone' => '#877F6C',
            'ice blue' => '#A5F2F3',
            'sky blue' => '#87CEEB',
            'electric blue' => '#7DF9FF',
            'royal blue' => '#4169E1',
            'navy blue' => '#000080',
            'midnight blue' => '#191970',
            'forest green' => '#228B22',
            'hunter green' => '#355E3B',
            'pine green' => '#01796F',
            'sea green' => '#2E8B57',
            'neon green' => '#39FF14',
            'neon pink' => '#FF10F0',
            'neon yellow' => '#FFFF33',
            'peach fuzz' => '#FFBE98',
        ];

        // Fetch options where attribute name implies it's a color
        $options = AttributeOption::whereHas('attribute', function($q) {
            $q->where('name', 'like', '%warna%')->orWhere('type', 'color');
        })->get();

        $updatedCount = 0;

        foreach ($options as $option) {
            $val = strtolower(trim($option->value));
            
            // Try exact match
            if (isset($colorDictionary[$val])) {
                $option->meta = $colorDictionary[$val];
                $option->save();
                $updatedCount++;
                $this->line("Updated: {$val} -> {$colorDictionary[$val]}");
                continue;
            }

            // Try partial match if exact fails
            foreach ($colorDictionary as $name => $hex) {
                // Ignore very short names for partial match to prevent false positives
                if (strlen($name) < 4) continue;
                
                if (Str::contains($val, $name) || Str::contains($name, $val)) {
                    $option->meta = $hex;
                    $option->save();
                    $updatedCount++;
                    $this->line("Updated (Partial Match): {$val} -> {$hex}");
                    break; // Break outer loop (move to next option) by using break 1;
                }
            }
        }

        $this->info("Berhasil mengupdate {$updatedCount} warna!");
    }
}
