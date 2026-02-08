<?php

namespace App\Services;

use App\Models\DealProduct;
use Illuminate\Support\Facades\DB;

class ProductStatsService
{
    /**
     * Estatísticas agregadas de produtos
     */
    public function getStats(
        int $tenantId,
        array $statuses = [],
        ?string $from = null,
        ?string $to = null
    ) {
        $query = DealProduct::query()
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(deal_products.quantity) as total_units'),
                DB::raw('SUM(deal_products.quantity * deal_products.unit_price) as total_value'),
            ])
            ->join('products', 'products.id', '=', 'deal_products.product_id')
            ->join('deals', 'deals.id', '=', 'deal_products.deal_id')
            ->where('deal_products.tenant_id', $tenantId);

        // Filtro por estado do negócio
        if (!empty($statuses)) {
            $query->whereIn('deals.status', $statuses);
        }

        // Filtro por datas
        if ($from) {
            $query->whereDate('deals.created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('deals.created_at', '<=', $to);
        }

        return $query
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_value')
            ->get();
    }
}
