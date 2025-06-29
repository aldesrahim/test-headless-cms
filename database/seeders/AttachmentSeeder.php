<?php

namespace Database\Seeders;

use App\Models\Attachment;
use Illuminate\Database\Seeder;
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

        $disk = Storage::disk('public');
        $files = File::files(__DIR__.'/resources/attachments');

        $attachments = [];

        foreach ($files as $file) {
            $attachments[] = [
                'disk' => 'public',
                'path' => $path = $disk->putFile(self::TARGET_DIR, $file->getPathname()),
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'mime_type' => $disk->mimeType($path),
                'extension' => $file->getExtension(),
            ];
        }

        foreach ($attachments as $attachment) {
            Attachment::create($attachment);
        }
    }

    protected function createTargetDirectory(): void
    {
        $disk = Storage::disk('public');

        if (! $disk->exists(self::TARGET_DIR)) {
            $disk->makeDirectory(self::TARGET_DIR);
        }
    }

    protected function emptyTargetDirectory(): void
    {
        $disk = Storage::disk('public');
        $files = $disk->files(self::TARGET_DIR);

        foreach ($files as $file) {
            $disk->delete($file);
        }
    }
}
