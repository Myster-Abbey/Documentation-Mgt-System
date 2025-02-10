<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserManager extends Controller
{
    public function create(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|ends_with:@gwosevo.com|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $role = Role::where('name', 'user')->first();
        $user->assignRole($role);

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function update(Request $request, User $user) {
        $user->update($request->all());
        return response()->json(['message' => 'User updated successfully']);
    }

    public function delete(User $user) {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}


