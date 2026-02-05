<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealFollowUp;

class DealFollowUpController extends Controller
{
    public function cancel(Deal $deal)
    {
        $followUp = DealFollowUp::query()
            ->where('deal_id', $deal->id)
            ->where('active', true)
            ->first();

        // Se não houver follow-up ativo, não rebenta
        if (! $followUp) {
            return back();
        }

        $followUp->update([
            'active'      => false,
            'stopped_at'  => now(),
            'stop_reason' => 'cancelled_by_user',
        ]);

        activity_log('deal.follow_up.cancelled', $deal);

        return back();
    }
}
