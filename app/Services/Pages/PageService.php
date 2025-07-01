<?php

namespace App\Services\Pages;

use App\Models\Page;
use App\Services\Pages\Enums\Status;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PageService
{
    protected function save(array $validated, ?Page $page = null, ?Model $subject = null)
    {
        return DB::transaction(function () use ($validated, $page) {
            $publishedAt = match (Status::from($validated['status'])) {
                Status::Published => $page?->published_at ?? now(),
                Status::Draft => null,
            };

            if (! $page) {
                $page = new Page;
            }

            $page->fill([
                'title' => $validated['title'],
                'body' => $validated['body'],
                'status' => $validated['status'],
                'published_at' => $publishedAt,
            ])->save();

            return $page;
        });
    }

    public function create(array $payload, ?Model $subject = null): Page
    {
        $validated = Validator::validate($payload, [
            'title' => ['required', 'string', 'unique:pages,title'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::enum(Status::class)],
        ]);

        return $this->save($validated, subject: $subject);
    }

    public function update(Page $page, array $payload, ?Model $subject = null): Page
    {
        $validated = Validator::validate($payload, [
            'title' => ['required', 'string', Rule::unique(Page::class, 'title')->ignoreModel($page)],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::enum(Status::class)],
        ]);

        return $this->save($validated, $page, $subject);
    }

    public function delete(Page $page): bool
    {
        if ($page->isPublished()) {
            throw new Exception('Published page could not be deleted');
        }

        return DB::transaction(fn () => $page->delete());
    }

    public function find($key): Page
    {
        return Page::findOrFail($key);
    }

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

        $baseQuery = Page::query()
            ->when(
                $validated['sort']['by'] ?? null,
                fn (Builder $query, $value) => $query->orderBy($value, $validated['sort']['direction'] ?? 'asc')
            )
            ->when(
                $validated['search'] ?? null,
                fn (Builder $query, $value) => $query->whereAny([
                    'slug',
                    'title',
                ], 'like', "%$value")
            );

        return $baseQuery
            ->paginate(
                perPage: $validated['page']['size'] ?? 10,
                pageName: $pageName,
                page: $validated['page']['number'] ?? 1,
            )
            ->onEachSide(0);
    }
}
