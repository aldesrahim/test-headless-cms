<?php

namespace App\Services\Categories;

use App\Exceptions\ConstraintViolationException;
use App\Models\Category;
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

        return DB::transaction(fn () => $category->update($validated));
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
}
