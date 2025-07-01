<?php

namespace App\Services\Posts;

use App\Models\Post;
use App\Rules\ReusableAttachment;
use App\Services\Posts\Concerns\HandleAttachments;
use App\Services\Posts\Enums\Status;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PostService
{
    use HandleAttachments;

    public const IMAGE_MAX_SIZE = 5120; // 5MB

    /**
     * @throws Throwable
     */
    protected function save(array $validated, ?Post $post = null, ?Model $subject = null)
    {
        return DB::transaction(function () use ($validated, $post, $subject) {
            $publishedAt = match (Status::from($validated['status'])) {
                Status::Published => $post?->published_at ?? now(),
                Status::Draft => null,
            };

            if (! $post) {
                $post = new Post;
            }

            $post->fill([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'excerpt' => $validated['excerpt'] ?? null,
                'status' => $validated['status'],
                'published_at' => $publishedAt,
            ])->save();

            if (! empty($validated['categories'])) {
                $post->categories()->sync($validated['categories']);
            }

            if (! empty($validated['attachment'])) {
                $this->storeAttachment($post, $validated['attachment'], $subject);
            }

            return $post;
        });
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function create(array $payload, ?Model $subject = null): Post
    {
        $validated = Validator::validate($payload, [
            'title' => ['required', 'string', 'unique:posts,title'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['nullable', 'string', 'exists:categories,id'],
            'status' => ['required', Rule::enum(Status::class)],
            'attachment' => ['nullable', new ReusableAttachment([
                'image',
                'size' => static::IMAGE_MAX_SIZE,
            ])],
        ]);

        return $this->save($validated, subject: $subject);
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function update(Post $post, array $payload, ?Model $subject = null): Post
    {
        $validated = Validator::validate($payload, [
            'title' => ['required', 'string', Rule::unique(Post::class, 'title')->ignoreModel($post)],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['nullable', 'string', 'exists:categories,id'],
            'status' => ['required', Rule::enum(Status::class)],
            'attachment' => ['nullable', new ReusableAttachment([
                'image',
                'size' => static::IMAGE_MAX_SIZE,
            ])],
        ]);

        return $this->save($validated, $post, $subject);
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function delete(Post $post): bool
    {
        if ($post->isPublished()) {
            throw new Exception('Published post could not be deleted');
        }

        return DB::transaction(function () use ($post) {
            $post->attachments()->detach();

            return $post->delete();
        });
    }

    /**
     * @throws ModelNotFoundException
     */
    public function find($key): Post
    {
        return Post::findOrFail($key);
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function getPaginated(array $params = [], string $pageName = 'page'): Paginator
    {
        $validated = Validator::validate($params, [
            'page.size' => ['nullable', 'numeric'],
            'page.number' => ['nullable', 'numeric', 'min:1'],
            'sort.by' => ['nullable', 'string', Rule::in([
                'id',
                'slug',
                'title',
                'status',
                'published_at',
                'created_at',
                'updated_at',
            ])],
            'sort.direction' => ['nullable', 'in:asc,desc'],
            'search' => ['nullable', 'string'],
        ]);

        return Post::query()
            ->when(
                $validated['sort']['by'] ?? null,
                fn (Builder $query, $value) => $query->orderBy($value, $validated['sort']['direction'] ?? 'asc')
            )
            ->when(
                $validated['search'] ?? null,
                fn (Builder $query, $value) => $query->whereAny([
                    'slug',
                    'title',
                    'excerpt',
                ], 'like', "%$value")
            )
            ->paginate(
                perPage: $validated['page']['size'] ?? 10,
                pageName: $pageName,
                page: $validated['page']['number'] ?? 1,
            )
            ->onEachSide(0);
    }
}
