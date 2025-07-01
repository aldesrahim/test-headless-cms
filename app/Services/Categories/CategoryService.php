<?php

namespace App\Services\Categories;

use App\Exceptions\ConstraintViolationException;
use App\Models\Category;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CategoryService
{
    /**
     * @throws ConstraintViolationException
     * @throws Throwable
     */
    public function create(array $payload): Category
    {
        $validated = Validator::validate($payload, [
            'name' => ['required', 'string', 'unique:categories,name'],
        ]);

        return DB::transaction(fn () => Category::create($validated));
    }

    /**
     * @throws ConstraintViolationException
     * @throws Throwable
     */
    public function update(Category $category, array $payload): Category
    {
        $validated = Validator::validate($payload, [
            'name' => ['required', 'string', Rule::unique(Category::class, 'name')->ignoreModel($category)],
        ]);

        return DB::transaction(fn () => tap($category, fn () => $category->update($validated)));
    }

    /**
     * @throws ConstraintViolationException
     * @throws Throwable
     */
    public function delete(Category $category): bool
    {
        if ($category->posts()->exists()) {
            throw new ConstraintViolationException;
        }

        return DB::transaction(fn () => $category->delete());
    }

    /**
     * @throws ModelNotFoundException
     */
    public function find(string $key): Category
    {
        return Category::findOrFail($key);
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
                'name',
                'created_at',
                'updated_at',
            ])],
            'sort.direction' => ['nullable', 'in:asc,desc'],
            'search' => ['nullable', 'string'],
        ]);

        return Category::query()
            ->withCount('posts')
            ->when(
                $validated['sort']['by'] ?? null,
                fn (Builder $query, $value) => $query->orderBy($value, $validated['sort']['direction'] ?? 'asc')
            )
            ->when(
                $validated['search'] ?? null,
                fn (Builder $query, $value) => $query->whereAny(['slug', 'name'], 'like', "%$value")
            )
            ->paginate(
                perPage: $validated['page']['size'] ?? 10,
                pageName: $pageName,
                page: $validated['page']['number'] ?? 1,
            )
            ->onEachSide(0);
    }
}
