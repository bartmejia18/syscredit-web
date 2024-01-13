<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class AccessController extends Controller {

    public function randomPasswordAccess(Request $request){
        
        $today = \Carbon\Carbon::now()->format('d');
        $year = \Carbon\Carbon::now()->format('y');

        $todayString = $today * 2;
        $password = "r{$todayString}c{$year}";

        return response()->json($password == $request->input("password"));
    }
}