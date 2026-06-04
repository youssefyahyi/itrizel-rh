<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ParametrageController extends Controller
{
    public function index() { return view('parametrage.index'); }
    public function create() { return view('parametrage.index'); }
    public function store(Request $request) { return redirect()->back(); }
    public function show($id) { return redirect()->back(); }
    public function edit($id) { return redirect()->back(); }
    public function update(Request $request, $id) { return redirect()->back(); }
    public function destroy($id) { return redirect()->back(); }
}