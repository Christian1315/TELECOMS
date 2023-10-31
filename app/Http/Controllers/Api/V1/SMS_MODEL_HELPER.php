<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SmsModel;

class SMS_MODEL_HELPER extends BASE_HELPER
{
    static function allSmsModel()
    {
        $Sms_Model =  SmsModel::orderBy("id", "desc")->get();
        return self::sendResponse($Sms_Model, 'Tout les Model d\'sms récupérés avec succès!!');
    }

    static function _retrieveSmsModel($id)
    {
        $Sms_Model = SmsModel::find($id);
        if (!$Sms_Model) {
            return self::sendError("Ce Model d'sms n'existe pas!", 404);
        }
        return self::sendResponse($Sms_Model, "Model d'sms récupéré avec succès:!!");
    }

    static function _activateSmsModel($id)
    {
        $Sms_Model = SmsModel::find($id);
        if (!$Sms_Model) {
            return self::sendError("Ce Model d'sms n'existe pas!", 404);
        }
        ###____ VERIFIONS SI CE MODULE EST DEJA ACTIVE ____###
        // return $Sms_Model;
        if ($Sms_Model->active == 1) {
            return self::sendError("Ce Model d'sms est déjà activé!!",505);
        }
        ###____DESACTIVATION DE TOUT LES MODELS(formule)
        $allModels = SmsModel::all();
        foreach ($allModels as $allModel) {
            $allModel->active = 0;
            $allModel->save();
        }
        ###____ACTIVATION DU MODEL EN QUESTION
        $Sms_Model->active = true;
        $Sms_Model->save();

        ##__
        return self::sendResponse($Sms_Model, "Model d'sms activé avec succès:!!");
    }
}
