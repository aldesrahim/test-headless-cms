<?php

namespace App\Services\Dashboard;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;

class DashboardService
{
    public function getCounter(): array
    {
        $models = [
            Post::class,
            Page::class,
            Category::class,
        ];

        $counts = [];

        /** @var class-string<Model> $model */
        foreach ($models as $model) {
            $basename = class_basename($model);
            $plural = str($basename)->lower()->plural()->toString();

            $counts[] = [
                'label' => __("labels.menu.$plural.plural"),
                'count' => $model::count(),
            ];
        }

        return $counts;
    }
}
