<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        // segurança multi-tenant
        abort_if($product->tenant_id !== app('tenant')->id, 403);

        $product->load([
            'deals' => function ($q) {
                $q->withPivot(['quantity', 'unit_price'])
                  ->orderByDesc('created_at');
            },
        ]);

        return Inertia::render('products/Show', [
            'product' => $product,
        ]);
    }
}
