<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::with('reviews')
            ->withCount('reviews');

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('description', 'like', '%'.$request->search.'%');
        }

        // Apply sorting
        switch ($request->get('sort', 'featured')) {
            case 'price-low':
                $query->orderBy('price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'rating':
                $query->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->orderBy('id', 'asc');
        }

        $products = $query->paginate(25);

        foreach ($products->items() as $product) {
            $product->category_color = $product->category->getColor();
        }

        // Get all categories for filter dropdown
        $categories = Category::orderBy('name')->get();

        return Inertia::render('Catalogue', [
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            'filters' => [
                'search' => $request->get('search'),
                'category' => $request->get('category'),
                'sort' => $request->get('sort', 'featured'),
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Display a single product.
     */
    public function show(Product $product): Response
    {
        $product->load(['reviews', 'category']);

        return Inertia::render('Product', [
            'product' => $product,
        ]);
    }
}
