<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Http\Resources\FieldResource;
use App\Models\Farm;
use App\Models\Field;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FieldController extends Controller
{
    public function index(Request $request, Farm $farm): AnonymousResourceCollection
    {
        $this->authorize('view', $farm);
        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', Field::STATUSES)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $fields = $farm->fields();

        if (isset($validated['search'])) {
            $search = $validated['search'];
            $fields->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('soil_type', 'like', "%{$search}%");
            });
        }

        if (isset($validated['status'])) {
            $fields->where('status', $validated['status']);
        }

        return FieldResource::collection(
            $fields->orderBy('name')->paginate($validated['per_page'] ?? 15)->withQueryString(),
        );
    }

    public function store(StoreFieldRequest $request, Farm $farm): FieldResource
    {
        return new FieldResource($farm->fields()->create($request->validated()));
    }

    public function show(Farm $farm, Field $field): FieldResource
    {
        abort_unless($field->farm_id === $farm->id, 404);
        $this->authorize('view', $field);

        return new FieldResource($field);
    }

    public function update(UpdateFieldRequest $request, Farm $farm, Field $field): FieldResource
    {
        abort_unless($field->farm_id === $farm->id, 404);
        $field->update($request->validated());

        return new FieldResource($field->refresh());
    }

    public function destroy(Farm $farm, Field $field): Response
    {
        abort_unless($field->farm_id === $farm->id, 404);
        $this->authorize('delete', $field);
        $field->delete();

        return response()->noContent();
    }
}
