<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\StoreCategoryRequest;
use App\Http\Requests\Transactions\UpdateCategoryRequest;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories
     */
    public function index()
    {
        return Inertia::render('transactions/Categories', [
            'categories' => Category::withCount('transactions')->latest()->get(),
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Update category
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category
     */
    public function destroy(Category $category)
    {
        if ($category->transactions()->exists()) {
            return back()->with('error', 'Category is in use and cannot be deleted.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }

    /**
     * Restore category
     */
    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category restored successfully.');
    }

    /**
     * Force delete category
     */
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

        if ($category->transactions()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Category has transactions.');
        }

        $category->forceDelete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category permanently deleted.');
    }
}
