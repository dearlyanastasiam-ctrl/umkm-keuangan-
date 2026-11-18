<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::withCount('transactions')->orderBy('role')->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'business_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:admin,owner'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        User::create($data);

        return back()->with('success', 'Akun berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,owner'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if ($user->id === $request->user()->id) {
            $data['role'] = $user->role; // cegah admin menurunkan perannya sendiri
        }
        $user->update($data);

        return back()->with('success', 'Akun diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['name' => 'Anda tidak dapat menghapus akun sendiri.']);
        }
        $user->delete();

        return back()->with('success', 'Akun dihapus.');
    }
}
