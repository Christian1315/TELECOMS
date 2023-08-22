<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expeditor;
use App\Models\ExpeditorStatus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EXPEDITOR_HELPER extends BASE_HELPER
{

    static function Expeditor_rules(): array
    {
        return [
            'name' => ['required', Rule::unique("expeditors")],
        ];
    }

    static function Expeditor_messages(): array
    {
        return [
            'name.required' => 'Le nom de  l\'expéditeur est réquis!',
            'name.unique' => ' Ce  expéditeur n\'est pas disponible! Veuillez mettre un autre',
        ];
    }

    static function Expeditor_Validator($formDatas)
    {
        $rules = self::Expeditor_rules();
        $messages = self::Expeditor_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    #####UPDATE D'UN EXPEDITEUR ##########
    static function UPDATE_Expeditor_rules(): array
    {
        return [
            'status' => ['required', "integer"],
        ];
    }

    static function UPDATE_Expeditor_messages(): array
    {
        return [
            'status.required' => 'Le status de  l\'expéditeur est réquis!',
            'status.integer' => 'Le status doit etre un entier',
        ];
    }

    static function UPDATE_Expeditor_Validator($formDatas)
    {
        $rules = self::UPDATE_Expeditor_rules();
        $messages = self::UPDATE_Expeditor_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function _createExpeditor($formData)
    {
        $Expeditor = Expeditor::create($formData);
        $Expeditor->status = 1;
        $Expeditor->save();
        return self::sendResponse($Expeditor, 'Expediteur enregistré avec succès!!');
    }

    static function retrieveExpeditor($id, $innerCall = false)
    {
        $Expeditor = Expeditor::with(["status"])->where('id', $id)->get();
        if ($Expeditor->count() == 0) {
            return self::sendError("Ce Expeditor n'existe pas!!", 404);
        }
        #$innerCall: Cette variable determine si la function **retrieveExpeditor** est appéle de l'intérieur
        if ($innerCall) {
            return $Expeditor;
        }
        return self::sendResponse($Expeditor, 'Expeditor récupré avec succès!!');
    }

    static function allExpeditors()
    {
        $Expeditors = Expeditor::with(["status"])->orderBy("id", "desc")->get();
        return self::sendResponse($Expeditors, 'Expeditors récupérés avec succès!!');
    }

    static function _deleteExpeditor($id)
    {
        $Expeditor = Expeditor::find($id);

        if (!$Expeditor) { #QUAND **$Expeditor** n'existe pas
            return self::sendError('Ce Expeditor n\'existe pas!', 404);
        };

        $Expeditor->delete(); #SUPPRESSION De Expeditor;
        return self::sendResponse($Expeditor, "Ce Expediteur a été supprimé avec succès!!");
    }

    static function _updateExpeditorStatus($request, $id)
    {
        $Expeditor = Expeditor::find($id);
        if (!$Expeditor) { #QUAND **$Expeditor** n'existe pas
            return self::sendError('Ce Expeditor n\'existe pas!', 404);
        };

        $ExpeditorSatatus = ExpeditorStatus::find($request->status);
        if (!$ExpeditorSatatus) { #QUAND **$Expeditor status** n'existe pas
            return self::sendError('Ce status d\'Expediteur n\'existe pas!', 404);
        };

        $Expeditor->status = $request->get("status");
        $Expeditor->save();

        // $data = $Expeditor->update(["status" => $request->get("status")]); #UPDATE DU STATUS De L'Expeditor;
        return self::sendResponse($Expeditor, "Le status de cet Expediteur a été modifié avec succès!!");
    }
}
