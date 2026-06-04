<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index() { return view('documents.index'); }
    public function create() { return view('documents.index'); }
    public function store(Request $request) { return redirect()->back(); }
    public function show($id) { return redirect()->back(); }
    public function edit($id) { return redirect()->back(); }
    public function update(Request $request, $id) { return redirect()->back(); }
    public function destroy($id) { return redirect()->back(); }
}