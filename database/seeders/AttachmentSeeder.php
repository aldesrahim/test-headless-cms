<?php

namespace Database\Seeders;

use App\Models\Attachment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AttachmentSeeder extends Seeder
{
    const TARGET_DIR = 'uploaded';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createTargetDirectory();
        $this->emptyTargetDirectory();

        $disk = config('filesystems.default');
        $files = File::files(__DIR__.'/resources/attachments');

        $attachments = [];

        foreach ($files as $file) {
            $attachments[] = [
                'disk' => $disk,
                'path' => Storage::putFile(self::TARGET_DIR, $path = $file->getPathname()),
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'mime_type' => File::mimeType($path),
                'extension' => $file->getExtension(),
            ];
        }

        foreach ($attachments as $attachment) {
            $stored = Attachment::create($attachment);
            Cache::forget($stored->getCacheKey());
        }
    }

    protected function createTargetDirectory(): void
    {
        if (! Storage::exists(self::TARGET_DIR)) {
            Storage::makeDirectory(self::TARGET_DIR);
        }
    }

    protected function emptyTargetDirectory(): void
    {
        $files = Storage::files(self::TARGET_DIR);

        foreach ($files as $file) {
            Storage::delete($file);
        }
    }
}
