<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
 
class USER_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##
    static function register_rules() : array {
        return [
            'role'=>'required',
            'name'=>'required',
            'email'=>['required','email',Rule::unique('users')],
            'password'=>['required',Rule::unique('users')],
        ];
    }

    static function register_messages() : array {
        return [
            'role.required'=>'Veuillez precisez le role de ce utilisateur!',
            'name.required'=>'Le champ Name est réquis!',
            'email.required'=>'Le champ Email est réquis!',
            'email.email'=>'Ce champ est un mail!',
            'email.unique'=>'Ce mail existe déjà!',
            'password.required'=>'Le champ Password est réquis!',
            'password.unique'=>'Ce mot de passe existe déjà!!',
        ];
    }

    static function Register_Validator($formDatas){
        #
        $rules = self::register_rules();
        $messages = self::register_messages();

        $validator = Validator::make($formDatas,$rules,$messages);
        return $validator;
    }

    static function createUser($formData){
        $role = Role::where('role',$formData['role'])->get();
        // return $role->count()!==0;
        if ($role->count()!==0) {
            $formData['password']= Hash::make($formData['password']);#Hashing du password
            $user = User::create($formData);#ENREGISTREMENT DU USER DANS LA DB
            #AFFECTATION DU ROLE **$role** AU USER **$user** 
            $user->roles()->attach($role);
            return self::sendResponse($user,'User crée avec succès!!');
            // return $user;
        }
        #LORSQUE LE ROLE ENVOYE PAR LA REQUETE N'EXISTE PAS DANS LA DB
        return self::sendError("Ce Rôle n'existe pas!",404);

    }

    ##======== LOGIN VALIDATION =======##
    static function login_rules() : array {
        return [
            'email'=>'required',
            'password'=>'required',
        ];
    }

    static function login_messages() : array {
        return [
            'email.required'=>'Le champ Email est réquis!',
            'email.email'=>'Ce champ est un mail!',
            'password.required'=>'Le champ Password est réquis!',
        ];
    }

    static function Login_Validator($formDatas){
        #
        $rules = self::login_rules();
        $messages = self::login_messages();

        $validator = Validator::make($formDatas,$rules,$messages);
        return $validator;
    }

    static function userAuthentification($request) {
        $credentials = ['email'=>$request->email,'password'=>$request->password];
        if(Auth::attempt($credentials)){#SI LE USER EST AUTHENTIFIE
            $user = Auth::user();
            $token = $user->createToken('MyToken',['api-access'])->accessToken; 
            $user['token'] = $token; 
            $user['role'] = $user->roles;
            $user['notifications'] = $user->notifications;

            #RENVOIE D'ERREURE VIA **sendResponse** DE LA CLASS BASE_HELPER
            return self::sendResponse($user,'Vous etes connecté(e) avec succès!!');
        }

        #RENVOIE D'ERREURE VIA **sendResponse** DE LA CLASS BASE_HELPER
        return self::sendError('Connexion échouée! Vérifiez vos données puis réessayez à nouveau!',500);
    }

    static function getUsers(){
        $users =  User::with(['transports','roles','frets','notifications'])->get();
        return self::sendResponse($users,'Touts les utilisatreurs récupérés avec succès!!');
    }

    static function retrieveUsers($id){
        $user = User::with(['transports','roles','frets','notifications'])->where('id',$id)->get();
        if($user->count()==0){
            return self::sendError("Ce utilisateur n'existe pas!", 404);
        }
        return self::sendResponse($user,"Utilisateur récupé avec succès:!!");
    }

    static function userLogout($request){
       $request->user()->token()->revoke();
       return self::sendResponse([],'Vous etes déconnecté(e) avec succès!');
    }
}
