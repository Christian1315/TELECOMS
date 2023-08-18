<?php

use App\Http\Controllers\Api\V1\Authorization;
use App\Http\Controllers\Api\V1\SmsController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\GroupeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1')->group(function () {
    ###========== USERs ROUTINGS ========###
    Route::controller(UserController::class)->group(function () {
        Route::any('register', 'Register');
        Route::any('login', 'Login');
        Route::middleware(['auth:api'])->get('logout', 'Logout');
        Route::any('users', 'Users');
        Route::any('{id}/password/update', 'UpdatePassword');
        Route::any('users/{id}', 'RetrieveUser');
    });
    Route::any('authorization', [Authorization::class, 'Authorization'])->name('authorization');

    ###========== SMS ROUTINGS ========###
    Route::controller(SmsController::class)->group(function () {
        Route::prefix('sms')->group(function () {
            Route::any('send', 'Send'); #ENVOIE D'SMS UNITAIRE
            Route::any('{id}/retrieve', 'getSms'); #RECUPERATION D'UN SMS
            Route::any('all', 'GetAllSms'); #RECUPERATION DE TOUT LES SMS
            Route::any('reports', 'SmsRapports'); #RAPPORTS D'UN SMS
            Route::any('send-to-groupe', 'SmsGroupe'); #ENVOIE D'SMS GROUPE
        });
    });

    ###========== CONTACT ROUTINGS ========###
    Route::controller(ContactController::class)->group(function () {
        Route::prefix('contact')->group(function () {
            Route::any('add', 'ContactCreate'); #AJOUT DE CONTACT
            Route::any('import', 'ImportContacts'); #IMPORTER DES CONTACTS
            Route::any('all', 'Contacts'); #GET ALL CONTACTS
            Route::any('{id}/retrieve', 'ContactRetrieve'); #RECUPERATION D'UN CONTACT
            Route::any('{id}/delete', 'DeleteContact'); #SUPPRESSION DE CONTACT
            Route::any('{id}/update', 'UpdateContact'); #MODIFICATION DE CONTACT
            Route::any('add-to-groupe', 'AttachContactToGroupe'); #AJOUTER UN CONTACT A UN GROUPE
        });
    });

    ###========== GROUPE ROUTINGS ========###
    Route::controller(GroupeController::class)->group(function () {
        Route::prefix('groupe')->group(function () {
            Route::any('add', 'GroupeCreate'); #AJOUT DE GROUPE
            Route::any('all', 'Groupes'); #GET ALL GROUPES
            Route::any('{id}/retrieve', 'GroupeRetrieve'); #RECUPERATION D'UN GROUPE
            Route::any('{id}/delete', 'DeleteGroupe'); #SUPPRESSION DE GROUPE
            Route::any('{id}/update', 'UpdateGroupe'); #MODIFICATION DE GROUPE
        });
    });
});
