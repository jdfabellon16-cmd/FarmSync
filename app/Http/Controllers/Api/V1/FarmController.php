<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FarmController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Farm::class);
        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:150'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        /** @var User $user */
        $user = $request->user();
        $farms = Farm::accessibleTo($user)->withCount('fields');

        if (isset($validated['search'])) {
            $search = $validated['search'];
            $farms->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return FarmResource::collection(
            $farms->orderBy('name')->paginate($validated['per_page'] ?? 15)->withQueryString(),
        );
    }

    public function store(StoreFarmRequest $request): FarmResource
    {
        /** @var User $user */
        $user = $request->user();

        return new FarmResource($user->ownedFarms()->create($request->validated()));
    }

    public function show(Farm $farm): FarmResource
    {
        $this->authorize('view', $farm);

        return new FarmResource($farm->loadCount('fields'));
    }

    public function update(UpdateFarmRequest $request, Farm $farm): FarmResource
    {
        $farm->update($request->validated());

        return new FarmResource($farm->refresh()->loadCount('fields'));
    }

    public function destroy(Farm $farm): Response
    {
        $this->authorize('delete', $farm);
        $farm->delete();

        return response()->noContent();
    }
}
