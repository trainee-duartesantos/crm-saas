<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_if($product->tenant_id !== app('tenant')->id, 403);

        $period = request('period', '90');

        $from = match ($period) {
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            default => null,
        };

        $product->load([
            'deals' => function ($q) use ($from) {
                $q->select('deals.id', 'title', 'status')
                ->withPivot(['quantity', 'unit_price', 'created_at'])
                ->when($from, fn ($q) =>
                    $q->wherePivot('created_at', '>=', $from)
                );
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
        Timeline (won / lost / margin)
        ----------------------- */
        $timeline = $product->deals
            ->groupBy(fn ($d) => $d->pivot->created_at->format('Y-m'))
            ->map(function ($deals) use ($product) {
                $won = $deals->where('status', 'won');
                $lost = $deals->where('status', 'lost');

                $wonValue = $won->sum(
                    fn ($d) => $d->pivot->quantity * $d->pivot->unit_price
                );

                $lostValue = $lost->sum(
                    fn ($d) => $d->pivot->quantity * $d->pivot->unit_price
                );

                $margin = $product->cost
                    ? $won->sum(
                        fn ($d) =>
                            ($d->pivot->unit_price - $product->cost)
                            * $d->pivot->quantity
                    )
                    : null;

                return [
                    'won' => $wonValue,
                    'lost' => $lostValue,
                    'margin' => $margin,
                ];
            })
            ->map(fn ($v, $month) => array_merge(['month' => $month], $v))
            ->values();

        return Inertia::render('products/Show', [
            'product' => $product,
            'breakdown' => $breakdown,
            'timeline' => $timeline,
            'period' => $period,
        ]);
    }

}
