<?php

namespace App\Http\Controllers;

use App\Models\ClauseContrat;
use App\Services\ContratPdfGenerator;
use Illuminate\Http\Request;

class ParametrageContratController extends Controller
{
    public function index(Request $request)
    {
        $type    = $request->get('type', 'CDI');
        $clauses = ClauseContrat::pourType($type)->get();
        $variables = ContratPdfGenerator::variablesDisponibles();

        return view('parametrage.contrats', compact('clauses', 'type', 'variables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type_doc'    => ['required', 'in:CDI,CDD,avenant'],
            'titre'       => ['required', 'string', 'max:150'],
            'contenu'     => ['required', 'string'],
            'obligatoire' => ['boolean'],
        ]);

        $data['obligatoire'] = $request->boolean('obligatoire');
        $data['ordre']       = ClauseContrat::where('type_doc', $data['type_doc'])->max('ordre') + 1;

        ClauseContrat::create($data);

        return redirect()->route('parametrage.contrats.index', ['type' => $data['type_doc']])
            ->with('success', 'Clause ajoutée.');
    }

    public function update(Request $request, ClauseContrat $clause)
    {
        $data = $request->validate([
            'titre'       => ['required', 'string', 'max:150'],
            'contenu'     => ['required', 'string'],
            'obligatoire' => ['boolean'],
            'actif'       => ['boolean'],
        ]);

        $clause->update([
            'titre'       => $data['titre'],
            'contenu'     => $data['contenu'],
            'obligatoire' => $request->boolean('obligatoire'),
            'actif'       => $request->boolean('actif', true),
        ]);

        return redirect()->route('parametrage.contrats.index', ['type' => $clause->type_doc])
            ->with('success', 'Clause mise à jour.');
    }

    public function destroy(ClauseContrat $clause)
    {
        $type = $clause->type_doc;
        $clause->delete();

        // Réordonner les clauses restantes
        ClauseContrat::pourType($type)->get()->each(fn($c, $i) => $c->update(['ordre' => $i + 1]));

        return redirect()->route('parametrage.contrats.index', ['type' => $type])
            ->with('success', 'Clause supprimée.');
    }

    public function monter(ClauseContrat $clause)
    {
        $precedente = ClauseContrat::where('type_doc', $clause->type_doc)
            ->where('ordre', '<', $clause->ordre)
            ->orderByDesc('ordre')->first();

        if ($precedente) {
            [$clause->ordre, $precedente->ordre] = [$precedente->ordre, $clause->ordre];
            $clause->save();
            $precedente->save();
        }

        return redirect()->route('parametrage.contrats.index', ['type' => $clause->type_doc]);
    }

    public function descendre(ClauseContrat $clause)
    {
        $suivante = ClauseContrat::where('type_doc', $clause->type_doc)
            ->where('ordre', '>', $clause->ordre)
            ->orderBy('ordre')->first();

        if ($suivante) {
            [$clause->ordre, $suivante->ordre] = [$suivante->ordre, $clause->ordre];
            $clause->save();
            $suivante->save();
        }

        return redirect()->route('parametrage.contrats.index', ['type' => $clause->type_doc]);
    }
}
