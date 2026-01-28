<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;


class PersonController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ActivityLog::class);

        $people = Person::query()
            ->where('tenant_id', app('tenant')->id)
            ->with('company')
            ->latest()
            ->get();

        return Inertia::render('people/Index', [
            'people' => $people,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'nullable|email',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $person = Person::create([
            ...$data,
            'tenant_id' => app('tenant')->id,
        ]);

        activity_log(
            'person.created',
            $person,
            ['name' => $person->full_name]
        );

        return back();
    }
}

