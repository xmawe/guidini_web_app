<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $filter = $request->input('filter', 'all');

        $query = User::with('city');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        if ($filter === 'online') {
            Log::info('Applying online filter');
            $query->where('last_activity_at', '>=', now()->subMinutes(5)); // Explicit condition
            Log::info('Raw query before pagination:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);
        } elseif ($filter === 'guides') {
            $query->role('guide');
        } elseif ($filter === 'admins') {
            $query->role('admin');
        }

        $usersBefore = $query->get(); // Fetch all matching records before pagination
        Log::info('Users before pagination:', $usersBefore->toArray());

        $users = $query->paginate(10);
        Log::info('Users after pagination:', $users->toArray());

        $users->getCollection()->transform(function ($user) {
            $user->role = $user->getRoleNames()->first() ?? 'default';
            $user->isOnline = $user->last_activity_at && $user->last_activity_at >= now()->subMinutes(5);
            return $user;
        });

        $metrics = [
            'totalUsers' => User::count(),
            'onlineUsers' => User::online()->count(),
            'guides' => User::role('guide')->count(),
            'admins' => User::role('admin')->count(),
        ];

        $cities = City::all();

        return Inertia::render('backoffice/users/index', [
            'users' => $users,
            'search' => $search,
            'filter' => $filter,
            'metrics' => $metrics,
            'cities' => $cities,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'city_id' => 'nullable|exists:cities,id',
            'role' => 'required|in:default,admin,guide',
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
