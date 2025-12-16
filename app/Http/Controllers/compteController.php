<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class compteController extends Controller
{
    public function index(){
        return view("comptes.creerCompte");
    }

    public function store(Request $request){
        // Validate the input data
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|unique:users,email_adresse',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create the user
        $user = new User();
        $user->name = $request->input('name');
        $user->last_name = $request->input('last_name');
        $user->email_adresse = $request->input('email_adresse');
        $user->password = bcrypt($request->input('password'));
        $user->habilitations = $request->input('traitement_analytique');
        $user->save();

        // Redirect or return response
        return redirect()->route('creer_compte.index')->with('success', 'User created successfully!');
    }
}

