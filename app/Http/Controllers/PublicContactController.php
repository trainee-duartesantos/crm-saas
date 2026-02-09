<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Activity;
use Illuminate\Http\Request;

class PublicContactController extends Controller
{
    public function show()
    {
        return inertia('Public/Contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'company' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        // ⚠️ Tenant default (demo SaaS)
        $tenant = \App\Models\Tenant::firstOrFail();

        // 🔤 Split name → first / last
        $nameParts = explode(' ', trim($data['name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '-';

        // 👤 Create or reuse person
        $person = Person::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'email' => $data['email'],
            ],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'company' => $data['company'] ?? null,
                'source' => 'public_form',
            ]
        );

        // 📝 Optional: create activity / log
        if (! empty($data['message'])) {
            activity_log('public.contact.submitted', $person, [
                'message' => $data['message'],
            ]);
        }

        return redirect('/dashboard')
            ->with('success', 'Thanks! We will contact you shortly.');
    }
}
