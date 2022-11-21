<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();

Route::get('/registrar',                'HomeController@registrar')->name('registrar');
Route::post('/registrarParticipante',   'HomeController@registrarParticipante')->name('registrarParticipante');
Route::get('/tablaPosiciones',         'HomeController@tablaPosiciones')->name('tablaPosiciones');

Route::get('/home',                     'HomeController@index')->name('home');
Route::get('/nadaQueVer',               'HomeController@nadaQueVer')->name('nadaQueVer');
Route::get('/porPoquito',               'HomeController@porPoquito')->name('porPoquito');
Route::get('/var',                      'HomeController@var')->name('var');

Route::get('/teams',                    'TeamsController@index')->name('teams');
Route::post('/setTeam',                 'TeamsController@setTeam')->name('setTeam');

Route::get('/games',                    'GamesController@index')->name('games');
Route::post('/setGame',                 'GamesController@setGame')->name('setGame');
Route::get('/addResult',                'GamesController@addResult')->name('addResult');
Route::post('/setResultGame',           'GamesController@setResultGame')->name('setResultGame');
Route::get('/initGame',                 'GamesController@initGame')->name('initGame');
Route::post('/endGame',                 'GamesController@endGame')->name('endGame');
Route::post('/starGame',                'GamesController@starGame')->name('starGame');


Route::get('/quiniela',                 'QuinielaController@index')->name('quiniela');
Route::get('/pointsXgame',              'QuinielaController@pointsXgame')->name('pointsXgame');
Route::post('/setQuiniela',             'QuinielaController@setQuiniela')->name('setQuiniela');
