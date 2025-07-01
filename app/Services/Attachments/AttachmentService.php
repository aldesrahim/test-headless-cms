<?php

namespace App\Services\Attachments;

use App\Exceptions\ConstraintViolationException;
use App\Models\Attachment;
use App\Services\Attachments\Data\StoreData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttachmentService
{
    public function find($key): Attachment
    {
        return Attachment::findOrFail($key);
    }

    public function getPaginated(array $params = [], string $pageName = 'page')
    {
        $validated = Validator::validate($params, [
            'page.size' => ['nullable', 'numeric'],
            'page.number' => ['nullable', 'numeric', 'min:1'],
            'sort.by' => ['nullable', 'string', Rule::in([
                'id',
                'filename',
                'size',
                'created_at',
                'updated_at',
            ])],
            'sort.direction' => ['nullable', 'in:asc,desc'],
            'search' => ['nullable', 'string'],
        ]);

        return Attachment::query()
            ->withCount('attachmentHasModels as usage_count')
            ->when(
                $validated['sort']['by'] ?? null,
                fn (Builder $query, $value) => $query->orderBy($value, $validated['sort']['direction'] ?? 'asc')
            )
            ->when(
                $validated['search'] ?? null,
                fn (Builder $query, $value) => $query->whereAny(['filename'], 'like', "%$value")
            )
            ->paginate(
                perPage: $validated['page']['size'] ?? 10,
                pageName: $pageName,
                page: $validated['page']['number'] ?? 1,
            )
            ->onEachSide(0);
    }

    public function create(UploadedFile $file, string $path = 'uploaded', string $disk = 'default'): Attachment
    {
        if ($disk === 'default') {
            $disk = config('filesystems.default');
        }

        $storedPath = $file->store($path, $disk);

        return Attachment::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'disk' => $disk,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->extension(),
        ]);
    }

    public function delete(Attachment $attachment): void
    {
        if ($attachment->attachmentHasModels()->exists()) {
            throw new ConstraintViolationException;
        }

        $attachment->delete();

        $disk = Storage::disk($attachment->disk);

        if ($disk->exists($attachment->path)) {
            $disk->delete($attachment->path);
        }
    }

    public function store(StoreData $data): ?Attachment
    {
        return DB::transaction(function () use ($data) {
            if (! $data->isUploadedFile()) {
                $attachment = Attachment::findOrFail($data->file);
            } else {
                $attachment = $this->create($data->file, $data->path, $data->disk);
            }

            if (blank($data->model)) {
                return $attachment;
            }

            /** @var MorphToMany $relation */
            $relation = $data->model->attachments();

            if (
                $attachment->wasRecentlyCreated ||
                ! $relation
                    ->wherePivot('key', $data->key)
                    ->wherePivot('attachment_id', $attachment->id)
                    ->exists()
            ) {
                $relation->attach($attachment, [
                    'key' => $data->key,
                    ...($data->subject ? [
                        'subject_id' => $data->subject->getKey(),
                        'subject_type' => $data->subject::class,
                    ] : []),
                ]);
            }

            return $attachment;
        });
    }
}
