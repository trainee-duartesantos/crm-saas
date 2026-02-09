<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function show(Request $request, Product $product)
    {
        abort_if($product->tenant_id !== app('tenant')->id, 403);

        // period: 30 | 90 | all (default all)
        $period = $request->query('period', 'all');

        $from = match ($period) {
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            default => null,
        };

        $product->load([
            'deals' => function ($q) use ($from) {
                $q->select('deals.id', 'deals.title', 'deals.status')
                    ->withPivot(['quantity', 'unit_price', 'created_at'])
                    // IMPORTANT: evitar wherePivot() aqui (estava a gerar SQL inválido)
                    ->when($from, fn ($q) => $q->where('deal_products.created_at', '>=', $from))
                    ->orderByDesc('deal_products.created_at');
            },
        ]);

        // Breakdown por status (won/lost/proposal)
        $breakdown = $product->deals
            ->groupBy('status')
            ->map(function ($deals) {
                return [
                    'units' => (int) $deals->sum(fn ($d) => (int) $d->pivot->quantity),
                    'value' => (float) $deals->sum(fn ($d) => (float) $d->pivot->quantity * (float) $d->pivot->unit_price),
                ];
            });

        // Timeline por mês (usa pivot_created_at)
        $timeline = $product->deals
            ->groupBy(function ($d) {
                $dt = $d->pivot->created_at
                    ? Carbon::parse($d->pivot->created_at)
                    : null;

                return $dt ? $dt->format('Y-m') : 'unknown';
            })
            ->map(function ($deals, $month) {
                return [
                    'month' => $month,
                    'value' => (float) $deals->sum(fn ($d) => (float) $d->pivot->quantity * (float) $d->pivot->unit_price),
                ];
            })
            ->values()
            ->filter(fn ($row) => $row['month'] !== 'unknown')
            ->sortBy('month')
            ->values();

        $revenue = (float) $product->deals->sum(
            fn ($d) => (float) $d->pivot->quantity * (float) $d->pivot->unit_price
        );

        // margem (se tiveres coluna cost no product, senão fica null)
        $costPerUnit = $product->cost ?? null;

        $cost = $costPerUnit !== null
            ? (float) $product->deals->sum(fn ($d) => (float) $d->pivot->quantity * (float) $costPerUnit)
            : null;

        $margin = $cost !== null ? $revenue - $cost : null;

        return Inertia::render('products/Show', [
            'product' => $product,
            'breakdown' => $breakdown,
            'timeline' => $timeline,
            'margin' => $margin,
            'filters' => [
                'period' => $period,
            ],
        ]);
    }
}
