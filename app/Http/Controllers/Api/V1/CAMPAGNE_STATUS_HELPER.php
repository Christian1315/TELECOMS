<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CampagneStatus;

class CAMPAGNE_STATUS_HELPER extends BASE_HELPER
{
    static function allCampagneStatus()
    {
        $Campagne_status =  CampagneStatus::orderBy("id", "desc")->get();
        return self::sendResponse($Campagne_status, 'Tout les status d\'expéditeur récupérés avec succès!!');
    }

    static function _retrieveCampagneStatus($id)
    {
        $Campagne_status = CampagneStatus::where('id', $id)->get();
        if ($Campagne_status->count() == 0) {
            return self::sendError("Ce status d'expéditeur n'existe pas!", 404);
        }
        return self::sendResponse($Campagne_status, "Status d'expéditeur récupéré avec succès:!!");
    }
}
