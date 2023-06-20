<?php

namespace App\Http\Controllers\Api\V1;

// require 'vendor/autoload.php';
use \Mailjet\Resources;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class NOTIFICATIONS_HELPER extends BASE_HELPER
{
    ##======== NOTIFICATION VALIDATION =======##
    static function notification_rules() : array {
        return [
            'sender_id'=>'required|integer',
            'receiver_id'=>'required|integer',
            'message'=>'required',
        ];
    }

    static function notification_messages() : array {
        return [
            'sender_id.required'=>'Veuillez precisez l\'id de l\'expéditeur du message!',
            'receiver_id.required'=>'Veuillez precisez l\'id du destinataire!',

            'sender_id.integer'=>'Ce champ requiert un entier',
            'receiver_id.integer'=>'Ce champ requiert un entier',
        ];
    }

    static function Notification_Validator($formDatas){
        #
        $rules = self::notification_rules();
        $messages = self::notification_messages();

        $validator = Validator::make($formDatas,$rules,$messages);
        return $validator;
    }

    static function sendEmail($SENDER_MAIL,$RECIPIENT_MAIL=''){
        $apikey = env('MAILJET_APIKEY');
        $apisecret = env('MAILJET_APISECRET');

        $mj = new \Mailjet\Client($apikey, $apisecret,true);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "agbande@gmaiul.com",
                        'Name' => "Me"
                    ],
                    'To' => [
                        [
                            'Email' => $RECIPIENT_MAIL,
                            'Name' => "GOGO Christian"
                        ]
                    ],
                    "TemplateID"=> 4647179,
                    "TemplateLanguage"=> True,
                    'Subject' => "My first Mailjet Email!",
                    'TextPart' => "Greetings from Mailjet!",
                    'HTMLPart' => "<h3>Dear passenger 1, welcome to <a href=\"https://www.mailjet.com/\">Mailjet</a>!</h3>
                    <br />May the delivery force be with you!"
                ]
            ]
        ];

        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success(); #&& 
        // return $response->getData();
    }

    #RECUPERATION DE TOUTES LES NOTIFICATIONS
    static function allNotifications(){
        $notifications  = Notification::with(['sender','receiver'])->orderBy('id','desc')->get();
        return self::sendResponse($notifications,'Listes de toutes les notifications');
    }

    #RECHERCHER UNE NOTIFICATION
    static function findNotification($id){
        $NOTIFICATION = Notification::with(['receiver','sender'])->find($id);
        #QUAND L'ID NE CORRESPOND A AUCUNE NOTIFICATION
        if(!$NOTIFICATION){
            return false;
        }
        #AUTREMENT
        return $NOTIFICATION;
    }


    static function retrieveNotification($notification){
        
        if(!$notification){#QUAND **$notification** RETOURNE **FALSE**
            return self::sendError('Cette notification n\'existe pas!',404);
        };

        return self::sendResponse($notification,'Notification récupéré avec succès!!');
    }

    #CREATION D'UNE NOTIFIFCATION
    static function createNotification($formData){
        $user = User::find($formData['sender_id']);
        #QUAND L'ID NE CORRESPOND A AUCUN EXPEDITEUR
        if(!$user){
            return self::sendError('Ce sender_id ne corresponds à aucun EXPEDITEUR',404);
        }

        $receiver = User::find($formData['receiver_id']);
        
        if (!$receiver) {
            return self::sendError('Ce receiver_id ne corresponds à aucun DESTINATAIRE',404);
        }

        $notification = Notification::create($formData);#ENREGISTREMENT DE LA NOTIFICATION DANS LA DB
        return self::sendResponse($notification,'Notification envoyée avec succès!!');
    }

    #TOUTES LES NOTIFICATIONS RECU PAR UN UTILISATEUR
    static function myNotificationsReceived($receiver_id){
        $receiver = User::find($receiver_id);
        #QUAND L'ID NE CORRESPOND A AUCUN DESTINATAIRE
        if(!$receiver){
            return self::sendError("Cet ID ne corresponds à aucun destinataire!",404);
        }
        
        #RECUPERATION DE TOUTES LES NOTIFICATIONS D'UN USER
        $notifications = Notification::with(['sender'])->where('receiver_id','=',$receiver_id)->orderBy('id','desc')->get();

        return self::sendResponse($notifications,'Listes des notifications récupérés avec succès!!');
    }

    static function updateNotification($notification,$formData){
        $notification->update($formData);
        $resul = Notification::find($notification->id);
        return self::sendResponse($resul,"Notification modifiée avec succès!!");
    }

    #SUPPRESSION D'UNE NOTIFICATION
    static function deleteNotification($notification){
        if(!$notification){#QUAND **$notification** RETOURNE **FALSE**
            return self::sendError('Cette notification n\'existe pas!',404);
        };

        $notification->delete();#SUPPRESSION DE LA NOTIFICATION;
        return self::sendResponse($notification,"Cette notification a été supprimée avec succès!!");
    }
}
