<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingCategory;
use App\Models\NewsCategory;
use App\Models\ProfessionalCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'listingCategories' => ListingCategory::withCount('listings')
                ->orderBy('sort_order')->get(['id', 'name', 'slug', 'icon', 'sort_order']),
            'newsCategories' => NewsCategory::withCount('posts')
                ->orderBy('sort_order')->get(['id', 'parent_id', 'name', 'slug', 'sort_order']),
            'professionalCategories' => ProfessionalCategory::withCount('professionals')
                ->orderBy('sort_order')->get(['id', 'name', 'slug', 'icon', 'sort_order']),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $class = $this->modelClass($type);
        $data = $this->validateData($request, $type);
        $data['slug'] = $this->uniqueSlug($class, $data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $class::create($data);

        return back()->with('success', 'Ангилал нэмэгдлээ.');
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $class = $this->modelClass($type);
        $category = $class::findOrFail($id);
        $data = $this->validateData($request, $type);

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($class, $data['name'], $category->id);
        }

        // "Ангилалгүй" ангиллын slug-ийг хадгална (uncategorized() үргэлж олж чадахын тулд).
        if ($type === 'news' && $category instanceof NewsCategory && $category->isUncategorized()) {
            unset($data['slug'], $data['parent_id']);
        }

        // Өөрийгөө эцэг болгохоос сэргийлнэ.
        if (($data['parent_id'] ?? null) === $category->id) {
            $data['parent_id'] = null;
        }

        $category->update($data);

        return back()->with('success', 'Ангилал шинэчлэгдлээ.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $class = $this->modelClass($type);
        $category = $class::findOrFail($id);

        if ($type === 'news') {
            return $this->destroyNewsCategory($category);
        }

        // Зар / Мэргэжилтний ангилал: бичлэгтэй бол устгахыг хориглоно.
        $count = $type === 'listing' ? $category->listings()->count() : $category->professionals()->count();
        if ($count > 0) {
            return back()->with('error', "Энэ ангилалд {$count} бичлэг байгаа тул устгах боломжгүй.");
        }

        $category->delete();

        return back()->with('success', 'Ангилал устгагдлаа.');
    }

    private function destroyNewsCategory(NewsCategory $category): RedirectResponse
    {
        // "Ангилалгүй" ангиллыг хэзээ ч устгахгүй.
        if ($category->isUncategorized()) {
            return back()->with('error', '"Ангилалгүй" ангиллыг устгах боломжгүй.');
        }

        // Дэд ангилалтай эцэг ангиллыг устгахгүй — эхлээд дэд ангилалуудыг устгана.
        if ($category->children()->exists()) {
            return back()->with('error', 'Энэ ангилалд дэд ангилал байна. Устгахыг хүсвэл эхлээд доторх дэд ангилалуудыг устгана уу.');
        }

        // Доторх мэдээг "Ангилалгүй" рүү шилжүүлнэ (байхгүй бол үүсгэнэ).
        if ($category->posts()->exists()) {
            $category->posts()->update(['news_category_id' => NewsCategory::uncategorized()->id]);
        }

        $category->delete();

        return back()->with('success', 'Ангилал устгаж, мэдээг "Ангилалгүй" рүү шилжүүллээ.');
    }

    /**
     * @return class-string<Model>
     */
    private function modelClass(string $type): string
    {
        return match ($type) {
            'listing' => ListingCategory::class,
            'news' => NewsCategory::class,
            'professional' => ProfessionalCategory::class,
            default => abort(404),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, string $type): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
        if ($type === 'listing' || $type === 'professional') {
            $rules['icon'] = ['nullable', 'string', 'max:40'];
        }
        if ($type === 'news') {
            $rules['parent_id'] = ['nullable', 'exists:news_categories,id'];
        }

        return $request->validate($rules);
    }

    /**
     * @param  class-string<Model>  $class
     */
    private function uniqueSlug(string $class, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'cat';
        $slug = $base;
        $i = 1;

        while ($class::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
