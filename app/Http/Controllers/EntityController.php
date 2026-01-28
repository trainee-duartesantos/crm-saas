<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;


class EntityController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $tenantId = app('tenant')->id;

        $query = Entity::query()
            ->where('tenant_id', $tenantId);

        // 🔍 Filtros
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vat')) {
            $query->where('vat', 'like', '%' . $request->vat . '%');
        }

        $entities = $query->latest()->paginate(20);

        return Inertia::render('entities/Index', [
            'entities' => $entities,
            'filters' => $request->only(['search', 'status', 'vat']),
        ]);
    }

    public function create()
    {
        $this->authorize('viewAny', ActivityLog::class);

        return Inertia::render('entities/Create');
    }
    
    public function store(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'vat' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $entity = Entity::create([
            ...$data,
            'tenant_id' => app('tenant')->id,
        ]);

        activity_log('entity.created', $entity, [
            'name' => $entity->name,
        ]);

        return redirect()->route('entities.index');
    }

    public function show(Entity $entity)
    {
        $this->authorize('viewAny', ActivityLog::class);

        abort_if($entity->tenant_id !== app('tenant')->id, 403);

        return Inertia::render('entities/Show', [
            'entity' => $entity->load(['people', 'deals']),
        ]);
    }

    public function update(Request $request, Entity $entity)
    {
        $this->authorize('viewAny', ActivityLog::class);

        abort_if($entity->tenant_id !== app('tenant')->id, 403);

        $entity->update($request->only([
            'name','vat','email','phone','website','status','notes'
        ]));

        activity_log('entity.updated', $entity);

        return back();
    }

    public function destroy(Entity $entity)
    {
        $this->authorize('viewAny', ActivityLog::class);

        abort_if($entity->tenant_id !== app('tenant')->id, 403);

        activity_log('entity.deleted', $entity, [
            'name' => $entity->name,
        ]);

        $entity->delete();

        return redirect()->route('entities.index');
    }
}
