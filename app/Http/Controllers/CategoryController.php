<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Track;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Afficher la liste de toutes les catégories.
     */
    public function index()
    {
        $categories = Category::withCount('tracks')->get();
        return view('app.categories.index', compact('categories'));
    }

    /**
     * Afficher les contributions d'une catégorie spécifique.
     *
     * @param  Category  $category
     * @return \Illuminate\View\View
     */
    public function show(Category $category)
    {

        $tracks = Track::where('category_id', $category->id)
            ->withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->paginate(10);

        return view('app.categories.show', compact('category', 'tracks'));
    }
}

