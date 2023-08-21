<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ExpeditorStatus;

class EXPEDITOR_STATUS_HELPER extends BASE_HELPER
{
    static function allExpeditorStatus()
    {
        $expeditor_status =  ExpeditorStatus::orderBy("id", "desc")->get();
        return self::sendResponse($expeditor_status, 'Tout les status d\'expéditeur récupérés avec succès!!');
    }

    static function _retrieveExpeditorStatus($id)
    {
        $expeditor_status = ExpeditorStatus::where('id', $id)->get();
        if ($expeditor_status->count() == 0) {
            return self::sendError("Ce status d'expéditeur n'existe pas!", 404);
        }
        return self::sendResponse($expeditor_status, "Status d'expéditeur récupéré avec succès:!!");
    }
}
