<?php

namespace App\Services\Attachments\Data;

use App\Models\Concerns\HasAttachments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class StoreData
{
    public function __construct(
        public TemporaryUploadedFile|UploadedFile|int $file,
        public string $path,
        public string $disk,
        public ?Model $model = null,
        public ?Model $subject = null,
        public ?string $key = null
    ) {
        $this->validateModel();
    }

    protected function validateModel(): void
    {
        $traits = trait_uses_recursive($this->model);

        if (! array_key_exists(HasAttachments::class, $traits)) {
            throw new InvalidArgumentException(sprintf(
                'Model [%s] must use [%s] trait',
                $this->model::class,
                HasAttachments::class,
            ));
        }
    }

    public function isUploadedFile(): bool
    {
        return $this->file instanceof UploadedFile;
    }
}
