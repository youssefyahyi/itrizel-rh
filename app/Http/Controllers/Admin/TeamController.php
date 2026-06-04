<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::withCount('users')->orderBy('name')->get();
        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        return view('admin.teams.form', ['team' => new Team]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:teams,name',
            'description' => 'nullable|string|max:255',
            'couleur'     => 'required|string|size:7',
            'actif'       => 'boolean',
        ]);

        $team = Team::create([
            'name'        => $request->name,
            'description' => $request->description,
            'couleur'     => $request->couleur,
            'actif'       => $request->boolean('actif', true),
        ]);

        return redirect()->route('admin.teams.show', $team)
                         ->with('success', 'Équipe créée.');
    }

    public function show(Team $team)
    {
        $team->loadCount('users');
        $members     = $team->users()->orderBy('name')->get();
        $nonMembers  = User::whereNull('team_id')
                           ->orWhere('team_id', '!=', $team->id)
                           ->orderBy('name')->get();

        return view('admin.teams.show', compact('team', 'members', 'nonMembers'));
    }

    public function edit(Team $team)
    {
        return view('admin.teams.form', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:teams,name,' . $team->id,
            'description' => 'nullable|string|max:255',
            'couleur'     => 'required|string|size:7',
            'actif'       => 'boolean',
        ]);

        $team->update([
            'name'        => $request->name,
            'description' => $request->description,
            'couleur'     => $request->couleur,
            'actif'       => $request->boolean('actif', true),
        ]);

        return redirect()->route('admin.teams.show', $team)
                         ->with('success', 'Équipe mise à jour.');
    }

    public function destroy(Team $team)
    {
        $team->users()->update(['team_id' => null]);
        $team->delete();

        return redirect()->route('admin.teams.index')
                         ->with('success', 'Équipe supprimée.');
    }

    public function addMember(Team $team, User $user)
    {
        $user->update(['team_id' => $team->id]);

        return back()->with('success', "{$user->name} ajouté à l'équipe.");
    }

    public function removeMember(Team $team, User $user)
    {
        if ($user->team_id === $team->id) {
            $user->update(['team_id' => null]);
        }

        return back()->with('success', "{$user->name} retiré de l'équipe.");
    }
}
