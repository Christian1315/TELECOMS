<?php

use App\Http\Controllers\Api\V1\ActionController;
use App\Http\Controllers\Api\V1\Authorization;
use App\Http\Controllers\Api\V1\CampagneController;
use App\Http\Controllers\Api\V1\CampagneStatusController;
use App\Http\Controllers\Api\V1\SmsController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DeveloperController;
use App\Http\Controllers\Api\V1\DifferedSmsController;
use App\Http\Controllers\Api\V1\ExpeditorController;
use App\Http\Controllers\Api\V1\GroupeController;
use App\Http\Controllers\Api\V1\RangController;
use App\Http\Controllers\Api\V1\RightController;
use App\Http\Controllers\Api\V1\ExpeditorStatusController;
use App\Http\Controllers\Api\V1\ProfilController;
use App\Http\Controllers\Api\V1\SmsModelController;
use App\Http\Controllers\Api\V1\SmsStatusController;
use App\Http\Controllers\Api\V1\SoldeController;
use App\Models\Groupe;
use App\Models\Sms;
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
        Route::any('active_account', 'AccountActivation');
        Route::any('password/update', 'UpdatePassword');
        Route::any('users/{id}', 'RetrieveUser');
        Route::any('user/update', 'UpdateUser');

        Route::any('password/demand_reinitialize', 'DemandReinitializePassword');
        Route::any('password/reinitialize', 'ReinitializePassword');
        Route::any('{id}/delete', 'DeleteUser');
        Route::any('attach-user', 'AttachRightToUser'); #Attacher un droit au user 
        Route::any('desattach-user', 'DesAttachRightToUser'); #Attacher un droit au user 
    });
    Route::any('authorization', [Authorization::class, 'Authorization'])->name('authorization');

    ###========== SMS STATUS ROUTINGS ========###
    Route::controller(SmsStatusController::class)->group(function () {
        Route::prefix('sms/status')->group(function () {
            Route::any('all', 'SmsStatus'); #RECUPERATION DE TOUT LES STATUS D'SMS
            Route::any('{id}/retrieve', 'RetrieveSmsStatus'); #RECUPERATION D'UN STATUS D'SMS
        });
    });
    ###========== SMS ROUTINGS ========###

    Route::controller(SmsController::class)->group(function () {
        Route::prefix('sms')->group(function () {
            Route::any('send', 'Send'); #ENVOIE D'SMS UNITAIRE
            Route::any('send_sms_from_other_plateforme', '_Send_Sms_From_Other_Plateforme'); #ENVOIE FROM OTHER PLATEFORME
            Route::any('send-ocean-sms', 'SendViaOceanic'); #ENVOIE D'SMS UNITAIRE
            Route::any('{id}/retrieve', 'getSms'); #RECUPERATION D'UN SMS
            Route::any('all', 'GetAllSms'); #RECUPERATION DE TOUT LES SMS
            Route::any('reports', 'SmsRapports'); #RAPPORTS D'UN SMS
            Route::any('send-to-groupe', 'SmsGroupe'); #ENVOIE D'SMS GROUPE

            ###========== SMS DIFFERES ROUTINGS ========###
            Route::prefix('differed')->group(function () {
                Route::controller(DifferedSmsController::class)->group(function () {
                    Route::any('group', 'CreateDiferedSmsGroupe');
                    Route::any('contact', 'CreateDiferedSmsContact');
                    Route::any('all', '_AllSms');
                });
            });

            ###___MODULES D'ENVOIE D'SMS _____####
            Route::prefix("formule")->group(function () {
                Route::controller(SmsModelController::class)->group(function () {
                    Route::any('all', 'SmsModel');
                    Route::any('{id}/retrieve', '_retrieveSmsModel');
                    Route::any('{id}/activate', 'ActivateSmsModel');
                });
            });
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
            Route::any('retrieve-from-groupe', 'RetrieveContactFromGroupe'); #RETIRER UN CONTACT A UN GROUPE
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

    ###========== PROFILS ROUTINGS ========###
    Route::controller(ProfilController::class)->group(function () {
        Route::prefix('profil')->group(function () {
            Route::any('add', 'CreateProfil'); #AJOUT DE PROFIL
            Route::any('all', 'Profils'); #RECUPERATION DE TOUT LES PROFILS
            Route::any('{id}/retrieve', 'RetrieveProfil'); #RECUPERATION D'UN PROFIL
            Route::any('{id}/update', 'UpdateProfil'); #MODIFICATION D'UN PROFIL
            Route::any('{id}/delete', 'DeleteProfil'); #SUPPRESSION D'UN PROFIL
        });
    });

    ###========== RANG ROUTINGS ========###
    Route::controller(RangController::class)->group(function () {
        Route::prefix('rang')->group(function () {
            Route::any('add', 'CreateRang'); #AJOUT DE RANG
            Route::any('all', 'Rangs'); #RECUPERATION DE TOUT LES RANGS
            Route::any('{id}/retrieve', 'RetrieveRang'); #RECUPERATION D'UN RANG
            Route::any('{id}/delete', 'DeleteRang'); #SUPPRESSION D'UN RANG
            Route::any('{id}/update', 'UpdateRang'); #MODIFICATION D'UN RANG'
        });
    });

    ###========== ACTION ROUTINGS ========###
    Route::controller(ActionController::class)->group(function () {
        Route::prefix('action')->group(function () {
            Route::any('add', 'CreateAction'); #AJOUT D'UNE ACTION'
            Route::any('all', 'Actions'); #GET ALL ACTIONS
            Route::any('{id}/retrieve', 'RetrieveAction'); #RECUPERATION D'UNE ACTION
            Route::any('{id}/delete', 'DeleteAction'); #SUPPRESSION D'UNE ACTION
            Route::any('{id}/update', 'UpdateAction'); #MODIFICATION D'UNE ACTION
        });
    });

    ###========== RIGHTS ROUTINGS ========###
    Route::controller(RightController::class)->group(function () {
        Route::prefix('right')->group(function () {
            Route::any('add', 'CreateRight'); #AJOUT D'UN DROIT'
            Route::any('all', 'Rights'); #GET ALL RIGHTS
            Route::any('{id}/retrieve', 'RetrieveRight'); #RECUPERATION D'UN DROIT
            Route::any('{id}/delete', 'DeleteRight'); #SUPPRESSION D'UN DROIT
        });
    });

    ###========== EXPEDITOR STATUS ROUTINGS ========###
    Route::controller(ExpeditorStatusController::class)->group(function () {
        Route::prefix('expeditor/status')->group(function () {
            Route::any('all', 'ExpeditorStatus'); #RECUPERATION DE TOUT LES STATUS D'EXPEDITEUR
            Route::any('{id}/retrieve', 'RetrieveExpeditorStatus'); #RECUPERATION D'UN STATUS D'EXPEDITEUR
        });
    });

    ###========== EXPEDITOR  ROUTINGS ========###
    Route::controller(ExpeditorController::class)->group(function () {
        Route::prefix('expeditor')->group(function () {
            Route::any('all', 'Expeditors'); #RECUPERATION DE TOUT LES EXPEDITEURS D'EXPEDITEUR
            Route::any('add', 'AddExpeditor'); #RECUPERATION DE TOUT LES EXPEDITEURS D'EXPEDITEUR
            Route::any('{id}/retrieve', '_RetrieveExpeditor'); #RECUPERATION D'UN EXPEDITEUR
            Route::any('{id}/delete', 'DeleteExpeditor'); #DELETE D'UN EXPEDITEUR
            Route::any('{id}/update_status', 'UpdateExpeditorStatus'); #UPDATE DU STATUT D'UN EXPEDITEUR
        });
    });

    ###========== CAMPAGNE STATUS ROUTINGS ========###
    Route::controller(CampagneStatusController::class)->group(function () {
        Route::prefix('campagne/status')->group(function () {
            Route::any('all', 'CampagneStatus'); #RECUPERATION DE TOUT LES STATUS DE CAMPAGNE
            Route::any('{id}/retrieve', 'RetrieveCampagneStatus'); #RECUPERATION D'UN STATUS DE CAMPAGNE
        });
    });

    ###========== SMS MODEL ROUTINGS ========###
    Route::controller(SmsModelController::class)->group(function () {
        Route::prefix('sms-model/status')->group(function () {
            Route::any('all', 'SmsModel'); #RECUPERATION DE TOUT LES MODELS D'SMS
            Route::any('{id}/retrieve', 'RetrieveSmsModel'); #RECUPERATION D'UN MODEL D'SMS
        });
    });

    ###========== CAMPAGNES  ROUTINGS ========###
    Route::controller(CampagneController::class)->group(function () {
        Route::prefix('campagne')->group(function () {
            Route::any('all', 'Campagnes'); #RECUPERATION DE TOUTES LES CAMPAGNES
            Route::any('add', 'CampagneCreate'); #RECUPERATION DE TOUTES LES CAMPAGNES
            Route::any('{id}/retrieve', 'CampagneRetrieve'); #RECUPERATION D'UNE CAMPAGNE
            Route::any('{id}/update', 'UpdateCampagne'); #UPDATE D'UNE CAMAPAGNE
            Route::any('{id}/delete', 'DeleteCampagne'); #DELETE D'UNE CAMPAGNE
            Route::any('{id}/initiate', '_InitiateCampagne'); #
            Route::any('{id}/stop', 'StopCampagne'); #
        });
    });

    ###========== DEVELOPER  ROUTINGS ========###
    Route::controller(DeveloperController::class)->group(function () {
        Route::prefix('developer')->group(function () {
            Route::prefix('key')->group(function () {
                // Route::any('generate', 'GenerateDeveloperKey');
                Route::any('{id}/retrieve', 'RetrieveDeveloperKey');
                Route::any('{id}/regenerate', 'RegenerateDeveloperKey');
                // Route::any('{id}/delete', 'DeleteCampagne');
            });

            Route::prefix('sms')->group(function () {
                Route::any('send', 'Send');
                Route::any('{id}/retrieve', 'getSms'); #RECUPERATION D'UN SMS
                Route::any('all', 'GetAllSms'); #RECUPERATION DE TOUT LES SMS
            });
        });
    });

    ###========== SOLDES  ROUTINGS ========###
    Route::controller(SoldeController::class)->group(function () {
        Route::prefix('sold')->group(function () {
            Route::any('credite', 'CredidateSold');
            Route::any('all', 'Soldes');
            Route::any('{id}/retrieve', 'RetrieveSold');
        });
    });

    Route::get('contacts_groupe8', function () {
        $groupe8 = Groupe::find(5);

        $data["count"] = count($groupe8->contacts);
        $data["contacts"] = $groupe8->contacts;
        return $data;
    });

    Route::get('sms_sended_by_abatoir', function () {
        $sms = Sms::where("owner", 8)->get();

        $data["count"] = count($sms);
        $data["sms"] = $sms;
        return $data;
    });
});
