<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class UserController extends Controller
{
    // get all the users
    public function index()
    {
        $users = UserService::getAllUsers();

        return response()->json($users);
    }

    // show data for selected user
    public function show($id)
    {
        $user = User::findOrFail($id);
        Gate::authorize('manage-user', $user);

        return response()->json($user);
    }

    // create (register) a new user
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = UserService::createUser($data);

        return response()->json($user);
    }

    // update data for selected user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        Gate::authorize('manage-user', $user);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['sometimes', 'required', 'string'],
        ]);
        $data = UserService::normalizePhone($data);
        Validator::make($data, [
            'phone' => ['nullable', Rule::unique('users')->ignore($user)],
        ])->validate();

        if (! $user->update($data)) {
            throw new RuntimeException('The user could not be updated.');
        }

        return response()->json($user);
    }

    // delete selected user
    public function destroy(Request $request, $id): JsonResponse
    {
        $target = User::findOrFail($id);
        Gate::authorize('manage-user', $target);
        $deletingOwnAccount = $request->user()->is($target);
        if (! $target->delete()) {
            throw new RuntimeException('The user could not be deleted.');
        }

        if ($deletingOwnAccount) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json($target);
    }

    // login for an existing user
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = UserService::loginUser($credentials);

        return response()->json($user);
    }
}
