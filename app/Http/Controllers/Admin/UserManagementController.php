<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users for dropdown selection
     */
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'pangkat', 'nomor_registrasi')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        // Load pangkat options
        $pangkatOptions = $this->getPangkatOptions();

        return Inertia::render('admin/BuatAkun', [
            'pangkatOptions' => $pangkatOptions,
        ]);
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'string', 'min:8'],
            'pangkat' => 'nullable|string|max:255',
            'nomor_registrasi' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'pangkat' => $request->pangkat,
            'nomor_registrasi' => $request->nomor_registrasi,
            'role' => 'non-admin', // Default role
        ]);

        return redirect()->route('admin.users.create');
    }

    /**
     * Show the form for editing a user
     */
    public function edit(User $user)
    {
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $pangkatOptions = $this->getPangkatOptions();

        return Inertia::render('admin/EditAkun', [
            'users' => $users,
            'selectedUser' => $user,
            'pangkatOptions' => $pangkatOptions,
        ]);
    }

    /**
     * Show the edit form without pre-selected user
     */
    public function editIndex()
    {
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $pangkatOptions = $this->getPangkatOptions();

        return Inertia::render('admin/EditAkun', [
            'users' => $users,
            'selectedUser' => null,
            'pangkatOptions' => $pangkatOptions,
        ]);
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'string', 'min:8'],
            'pangkat' => 'nullable|string|max:255',
            'nomor_registrasi' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'pangkat' => $request->pangkat,
            'nomor_registrasi' => $request->nomor_registrasi,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.edit.index');
    }

    /**
     * Get user data for dropdown selection
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Generate a random password
     */
    public function generatePassword()
    {
        $password = Str::random(12);
        
        return response()->json([
            'password' => $password
        ]);
    }

    /**
     * Get pangkat options
     */
    private function getPangkatOptions()
    {
        $pangkatFile = resource_path('js/data/pangkat.json');
        return json_decode(file_get_contents($pangkatFile), true);
    }
}