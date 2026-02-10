<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->when(
                value: $request->input('is_trashed') === 'true',
                callback: fn (Builder $query): Builder => $query->onlyTrashed()
            )
            ->when(
                value: $request->has(key: 'username'),
                callback: fn (Builder $query): Builder => $query->where(column: 'username', operator: 'like', value: '%' . $request->input('username') . '%')
            )
            ->when(
                value: $request->has(key: 'email'),
                callback: fn (Builder $query): Builder => $query->where(column: 'email', operator: 'like', value: '%' . $request->input('email') . '%')
            )
            ->get();

        return UserResource::collection(resource: $users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Str::random(8); // Le colocamos una contraseña por defecto
        
        // Si no se envía hiring_date, asignar la fecha actual
        if (!isset($data['hiring_date'])) {
            $data['hiring_date'] = now()->toDateString();
        }

        $user = User::create($data);
        
        return response()->json(UserResource::make($user), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource
    {
        return UserResource::make($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $user->update($request->validated());

        return UserResource::make($user);
    }

    /**
     * Partially update the specified resource in storage.
     */
    public function patch(UpdateUserRequest $request, User $user): UserResource
    {
        $user->update($request->validated());

        return UserResource::make($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'El usuario ha sido eliminado correctamente.'], 200);
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore(int $id)
    {
        $user = User::onlyTrashed()->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado entre los eliminados.'], 404);
        }

        $user->restore();

        return response()->json(['message' => 'Usuario restaurado correctamente.'], 200);
    }
}