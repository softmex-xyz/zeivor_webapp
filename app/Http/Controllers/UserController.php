<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $user = UserService::getUserById($id);

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
        $user = UserService::updateUser($id, $request->all());

        return response()->json($user);
    }

    // delete selected user
    public function destroy($id)
    {
        $user = UserService::deleteUser($id);

        return response()->json($user);
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
