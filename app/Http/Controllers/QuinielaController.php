<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuinielaController extends Controller
{
    public function index(){


        $games = DB::table('games')
//            ->where('userId', '=', $usuario)
            ->orderBy('dateGame','asc')
            ->orderBy('timeGame', 'asc')
            ->get();
        return view('quiniela/index', compact('games'));
    }

    public function setQuiniela(){
        DB::table('quiniela')
            ->updateOrInsert();
        return back();
    }
}
