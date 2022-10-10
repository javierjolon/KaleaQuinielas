<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->email == 'admin@admin.com'){
            return view('Admin/index');
        }else{
            $positions = DB::table('users')
                ->where('id', '<>', '1')
                ->orderBy('actualPosition', 'asc')
                ->get();
            return view('User/index', compact('positions'));
        }
    }

    public function getPoints(){
        $points = DB::table('quiniela')->get();
    }

    public function registrar(){
        return view('Registrar.registrar');
    }

    public function registrarParticipante(Request $data){
        $respuesta = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        if ($respuesta->exists){
            return back()->with('success', 'Creado correctamente');
        }else{
            return back()->with('error', 'Error al crear usuario');
        }
    }

}
