<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class USER_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##
    static function register_rules(): array
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'phone' => 'required',
            'email' => ['required', 'email', Rule::unique('users')],
            'password' => ['required'],
        ];
    }

    static function register_messages(): array
    {
        return [
            'firstname.required' => 'Le champ Firstname est réquis!',
            'lastname.required' => 'Le champ Lastname est réquis!',
            'email.required' => 'Le champ Email est réquis!',
            'email.email' => 'Ce champ est un mail!',
            'email.unique' => 'Ce mail existe déjà!',
            'password.required' => 'Le champ Password est réquis!',
        ];
    }

    static function Register_Validator($formDatas)
    {
        #
        $rules = self::register_rules();
        $messages = self::register_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    ##======== NEW PASSWORD VALIDATION =======##
    static function NEW_PASSWORD_rules(): array
    {
        return [
            'old_password' => 'required',
            'new_password' => 'required',
        ];
    }

    static function NEW_PASSWORD_messages(): array
    {
        return [
            // 'new_password.required' => 'Veuillez renseigner soit votre username,votre phone ou soit votre email',
            // 'password.required' => 'Le champ Password est réquis!',
        ];
    }

    static function NEW_PASSWORD_Validator($formDatas)
    {
        #
        $rules = self::NEW_PASSWORD_rules();
        $messages = self::NEW_PASSWORD_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    ##======== LOGIN VALIDATION =======##
    static function login_rules(): array
    {
        return [
            'username' => 'required',
            'password' => 'required',
        ];
    }

    static function login_messages(): array
    {
        return [
            'username.required' => 'Le champ Username est réquis!',
            'password.required' => 'Le champ Password est réquis!',
        ];
    }

    static function Login_Validator($formDatas)
    {
        #
        $rules = self::login_rules();
        $messages = self::login_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }


    #CREATION D'UN USER
    static function createUser($formData)
    {
        $formData['password'] = Hash::make($formData['password']); #Hashing du password
        $user = User::create($formData); #ENREGISTREMENT DU USER DANS LA DB

        $username = Get_Username($user, "USER");
        $user->username = $username;
        $user->save();
        return self::sendResponse($user, 'User crée avec succès!!');
    }

    #AUTHENTIFICATION D'UN USER
    static function userAuthentification($request)
    {
        $credentials = ['username' => $request->username, 'password' => $request->password];
        if (Auth::attempt($credentials)) { #SI LE USER EST AUTHENTIFIE
            $user = Auth::user();
            $token = $user->createToken('MyToken', ['api-access'])->accessToken;
            $user['token'] = $token;

            #RENVOIE D'ERREURE VIA **sendResponse** DE LA CLASS BASE_HELPER
            return self::sendResponse($user, 'Vous etes connecté(e) avec succès!!');
        }

        #RENVOIE D'ERREURE VIA **sendResponse** DE LA CLASS BASE_HELPER
        return self::sendError('Connexion échouée! Vérifiez vos données puis réessayez à nouveau!', 500);
    }

    static function getUsers()
    {
        $users =  User::all();
        return self::sendResponse($users, 'Touts les utilisatreurs récupérés avec succès!!');
    }

    static function retrieveUsers($id)
    {
        $user = User::where('id', $id)->get();
        if ($user->count() == 0) {
            return self::sendError("Ce utilisateur n'existe pas!", 404);
        }
        return self::sendResponse($user, "Utilisateur récupéré(e) avec succès:!!");
    }

    static function userLogout($request)
    {
        $request->user()->token()->revoke();
        // DELETING ALL TOKENS REMOVED
        // Artisan::call('passport:purge');
        return self::sendResponse([], 'Vous etes déconnecté(e) avec succès!');
    }

    static function _updatePassword($formData, $id)
    {
        $user = User::where(['id' => $id])->get();
        if (count($user) == 0) {
            return self::sendError("Ce utilisateur n'existe pas!", 404);
        };

        if (Hash::check($formData["old_password"], $user[0]->password)) { #SI LE old_password correspond au password du user dans la DB
            $user[0]->update(["password" => $formData["new_password"]]);
            return self::sendResponse($user, 'Mot de passe modifié avec succès!');
        }
        return self::sendError("Votre mot de passe est incorrect", 505);
    }
}
