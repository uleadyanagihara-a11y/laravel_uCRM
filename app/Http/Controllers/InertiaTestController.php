<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\InertiaTest;

class InertiaTestController extends Controller
{
    public function index(){
        return Inertia::render('Inertia/index', [
            'blogs' => InertiaTest::all()
        ]);
    }
    public function create(){
        return Inertia::render('Inertia/Create');
    }
    public function show($id)
    {
        //dd($id);
        return Inertia::render('Inertia/show', [
            'id' => $id,
            'blog' => InertiaTest::findOrFail($id)
        ]);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'title' => 'required|unique:inertia_tests|max:20',
            'content' => 'required',
        ]);

    // ブログポストは有効

        $inertiaTest = new InertiaTest;
        $inertiaTest->title = $request->title;
        $inertiaTest->content = $request->content;
        $inertiaTest->save();

        return to_route('inertia.index')
        ->with([
            'message' => '登録しました'
        ]);
    }
    public function delete($id){
        $book = InertiaTest::findOrFail($id);
        $book->delete();

        return to_route('inertia.index')
        ->with([
            'message' => '削除しました'
        ]);
    }
}
