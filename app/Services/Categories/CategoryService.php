<?php

namespace App\Services\Categories;

use App\Exceptions\ConstraintViolationException;
use App\Models\Category;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryService
{
    public function create(array $payload): Category
    {
        $validated = Validator::validate($payload, [
            'name' => ['required', 'string', 'unique:categories,name'],
        ]);

        return DB::transaction(fn () => Category::create($validated));
    }

    public function update(Category $category, array $payload): Category
    {
        $validated = Validator::validate($payload, [
            'name' => ['required', 'string', Rule::unique(Category::class, 'name')->ignoreModel($category)],
        ]);

        return DB::transaction(fn () => tap($category, fn () => $category->update($validated)));
    }

    public function delete(Category $category): bool
    {
        if ($category->posts()->exists()) {
            throw new ConstraintViolationException;
        }

        return DB::transaction(fn () => $category->delete());
    }

    public function find(string $key): Category
    {
        return Category::findOrFail($key);
    }

    public function getPaginated(array $params = [], string $pageName = 'page'): Paginator
    {
        $validated = Validator::validate($params, [
            'page.size' => ['nullable', 'numeric'],
            'page.number' => ['nullable', 'numeric', 'min:1'],
            'sort.by' => ['nullable', 'string', Rule::in(['id', 'slug', 'name', 'created_at', 'updated_at'])],
            'sort.direction' => ['nullable', 'in:asc,desc'],
            'search' => ['nullable', 'string'],
        ]);

        $baseQuery = Category::query()
            ->withCount('posts')
            ->when(
                $validated['sort']['by'] ?? null,
                fn (Builder $query, $value) => $query->orderBy($value, $validated['sort']['direction'] ?? 'asc')
            )
            ->when(
                $validated['search'] ?? null,
                fn (Builder $query, $value) => $query->whereAny(['slug', 'name'], 'like', "%$value")
            );

        return $baseQuery
            ->paginate(
                perPage: $validated['page']['size'] ?? 10,
                pageName: $pageName,
                page: $validated['page']['number'] ?? 1,
                total: $baseQuery->toBase()->getCountForPagination(),
            )
            ->onEachSide(0);
    }
}
