<?php

namespace App\Models;

use Awcodes\Curator\Models\Media as CuratorMedia;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use App\Models\SiteSetting;

class Media extends CuratorMedia
{
    protected static function booted()
    {
        parent::booted();

        static::creating(function (Media $media) {
            try {
                // Get setting, default to true
                $autoWebp = SiteSetting::where('key', 'media_auto_webp')->value('value') ?? '1';
                
                if ($autoWebp !== '1' && $autoWebp !== true && $autoWebp !== 'true') {
                    return;
                }

                // Check if it's an image and NOT already a webp
                if (in_array($media->ext, ['jpg', 'jpeg', 'png']) && $media->type !== 'image/webp') {
                    
                    $disk = Storage::disk($media->disk);
                    
                    if ($disk->exists($media->path)) {
                        $manager = new ImageManager(new Driver());
                        
                        // Read image from disk
                        $image = $manager->read($disk->get($media->path));
                        
                        // Compress and encode to webp
                        $encoded = $image->toWebp(80);
                        
                        // Generate new path
                        $newPath = preg_replace('/\.[^.]+$/', '.webp', $media->path);
                        
                        // Save new webp file
                        $disk->put($newPath, (string) $encoded);
                        
                        // Delete old file
                        $disk->delete($media->path);
                        
                        // Update media properties
                        $media->path = $newPath;
                        $media->ext = 'webp';
                        $media->type = 'image/webp';
                        $media->size = $disk->size($newPath);
                        // Curator's name field is the display name, it shouldn't have an extension.
                        $media->name = preg_replace('/\.[^.]+$/', '', $media->name);
                    }
                }
            } catch (\Exception $e) {
                // If intervention fails (e.g., memory limit), we just skip conversion and keep the original file.
                \Log::error('Media WebP Conversion Failed: ' . $e->getMessage());
            }
        });
    }
}
