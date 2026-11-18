<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    private function rules(Request $request, ?int $ignore = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories')->where('type', $request->type)->ignore($ignore)],
            'type' => ['required', 'in:income,expense'],
            'is_hpp' => ['boolean'],
        ];
    }

    public function index()
    {
        return Inertia::render('Categories/Index', [
            'categories' => Category::withCount('transactions')->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request));
        $data['is_hpp'] = $data['type'] === 'expense' && ($data['is_hpp'] ?? false);
        Category::create($data);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate($this->rules($request, $category->id));
        $data['is_hpp'] = $data['type'] === 'expense' && ($data['is_hpp'] ?? false);
        $category->update($data);

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->transactions()->exists()) {
            return back()->withErrors(['name' => 'Kategori masih dipakai oleh transaksi dan tidak dapat dihapus.']);
        }
        $category->delete();

        return back()->with('success', 'Kategori dihapus.');
    }
}
