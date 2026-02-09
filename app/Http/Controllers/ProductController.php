<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_if($product->tenant_id !== app('tenant')->id, 403);

        $product->load([
            'deals' => function ($q) {
                $q->withPivot(['quantity', 'unit_price', 'created_at'])
                  ->select('deals.id', 'title', 'status');
            },
        ]);

        /* -----------------------
           Breakdown by status
        ----------------------- */
        $breakdown = $product->deals
            ->groupBy('status')
            ->map(fn ($deals) => [
                'units' => $deals->sum(fn ($d) => $d->pivot->quantity),
                'value' => $deals->sum(
                    fn ($d) => $d->pivot->quantity * $d->pivot->unit_price
                ),
            ]);

        /* -----------------------
           Timeline (by month)
        ----------------------- */
        $timeline = $product->deals
            ->filter(fn ($d) => $d->pivot->created_at)
            ->groupBy(fn ($d) =>
                $d->pivot->created_at->format('Y-m')
            )
            ->map(fn ($deals) => [
                'value' => $deals->sum(
                    fn ($d) => $d->pivot->quantity * $d->pivot->unit_price
                ),
            ])
            ->map(fn ($data, $month) => [
                'month' => $month,
                'value' => $data['value'],
            ])
            ->values();

        /* -----------------------
           Revenue / Margin
        ----------------------- */
        $revenue = $product->deals->sum(
            fn ($d) => $d->pivot->quantity * $d->pivot->unit_price
        );

        $cost = $product->cost
            ? $product->deals->sum(
                fn ($d) => $d->pivot->quantity * $product->cost
            )
            : null;

        $margin = $cost !== null ? $revenue - $cost : null;

        return Inertia::render('products/Show', [
            'product'   => $product,
            'breakdown' => $breakdown,
            'timeline'  => $timeline,
            'revenue'   => $revenue,
            'cost'      => $cost,
            'margin'    => $margin,
        ]);
    }
}
