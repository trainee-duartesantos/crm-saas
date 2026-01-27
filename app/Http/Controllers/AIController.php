<?php

namespace App\Http\Controllers;

use App\Services\AIService;

class AIController extends Controller
{
    public function suggestInviteFollowUp(AIService $ai)
    {
        $message = $ai->suggest('invite_pending');

        activity_log(
            'ai.suggestion',
            null,
            [
                'message' => $message,
                'context' => 'invite_pending',
            ]
        );

        return back()->with('success', 'AI suggestion generated.');
    }
}
