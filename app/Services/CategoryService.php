<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryService
{
    public static function create(array $data): Category
    {
        $data['user_id'] = Auth::id();

        return Category::create($data);
    }

    public static function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }
}
