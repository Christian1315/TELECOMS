<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SmsStatus;

class SMS_STATUS_HELPER extends BASE_HELPER
{
    static function allSmsStatus()
    {
        $Sms_status =  SmsStatus::with("status")->orderBy("id", "desc")->get();
        return self::sendResponse($Sms_status, 'Tout les status d\'sms récupérés avec succès!!');
    }

    static function _retrieveSmsStatus($id)
    {
        $Sms_status = SmsStatus::with("status")->where(['id', $id])->get();
        if ($Sms_status->count() == 0) {
            return self::sendError("Ce status d'sms n'existe pas!", 404);
        }
        return self::sendResponse($Sms_status, "Status d'sms récupéré avec succès:!!");
    }
}
