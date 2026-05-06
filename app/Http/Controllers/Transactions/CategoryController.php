<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Transactions\StoreCategoryRequest;
use App\Http\Requests\Transactions\UpdateCategoryRequest;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories
     */
    public function index()
    {
        return Inertia::render('transactions/Categories', [
            'categories' => Category::latest()->get()
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect('/categories')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update category
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect('/categories')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category
     */
    public function destroy(Category $category)
    {
        if ($category->transactions()->exists()) {
            return redirect('/categories')
                ->with('error', 'Category is in use and cannot be deleted.');
        }

        $category->delete();

        return redirect('/categories')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Restore category
     */
    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        return redirect('/categories')
            ->with('success', 'Category restored successfully.');
    }

    /**
     * Force delete category
     */
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

        if ($category->transactions()->exists()) {
            return redirect('/categories')
                ->with('error', 'Category has transactions.');
        }

        $category->forceDelete();

        return redirect('/categories')
            ->with('success', 'Category permanently deleted.');
    }
}