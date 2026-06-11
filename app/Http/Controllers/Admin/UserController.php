<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $employes      = $this->employes();
        $emailDomain   = $this->emailDomain();
        return view('admin.users.form', ['user' => new User, 'employes' => $employes, 'emailDomain' => $emailDomain]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|in:admin,gestionnaire,lecteur,salarie',
            'actif'      => 'boolean',
            'telephone'  => 'nullable|string|max:20',
            'employe_id' => 'nullable|exists:employes,id',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'actif'      => $request->boolean('actif', true),
            'telephone'  => $request->telephone,
            'employe_id' => $request->role === 'salarie' ? $request->employe_id : null,
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $employes    = $this->employes($user->employe_id);
        $emailDomain = $this->emailDomain();
        return view('admin.users.form', compact('user', 'employes', 'emailDomain'));
    }

    private function employes(?int $currentEmployeId = null): \Illuminate\Database\Eloquent\Collection
    {
        // Exclure les employés déjà liés à un autre compte salarié
        $usedIds = User::whereNotNull('employe_id')
            ->where('role', 'salarie')
            ->when($currentEmployeId, fn($q) => $q->where('employe_id', '!=', $currentEmployeId))
            ->pluck('employe_id');

        return Employe::whereNotIn('id', $usedIds)
            ->orderBy('nom')->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'matricule', 'telephone', 'email']);
    }

    private function emailDomain(): string
    {
        $adminEmail = auth()->user()->email;
        $parts = explode('@', $adminEmail);
        return count($parts) === 2 ? $parts[1] : 'itrizel-rh.ma';
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => ['required', 'email', Rule::unique('users')->ignore($user)],
            'password'   => 'nullable|string|min:8|confirmed',
            'role'       => 'required|in:admin,gestionnaire,lecteur,salarie',
            'actif'      => 'boolean',
            'telephone'  => 'nullable|string|max:20',
            'employe_id' => 'nullable|exists:employes,id',
        ]);

        $user->update([
            'name'       => $request->name,
            'email'      => $request->email,
            'role'       => $request->role,
            'actif'      => $request->boolean('actif', true),
            'telephone'  => $request->telephone,
            'employe_id' => $request->role === 'salarie' ? $request->employe_id : null,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')
                         ->with('success', 'Utilisateur mis à jour.');
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }
        $user->update(['actif' => !$user->actif]);
        return back()->with('success', $user->actif ? 'Compte activé.' : 'Compte désactivé.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé.');
    }
}
