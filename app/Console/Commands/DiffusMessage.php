<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\SMS_HELPER;
use App\Models\Contact;
use App\Models\DifferedSms;
use App\Models\Groupe;
use App\Models\User;
use Illuminate\Console\Command;

class DiffusMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:diffus-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diffusion des messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $differedSmss = DifferedSms::where(["sended" => 0])->get();

        foreach ($differedSmss as $differedSms) {
            $send_time = strtotime($differedSms->send_date);
            $now = Custom_Timestamp();

            if ($send_time == $now || $now > $send_time) {
                $owner = User::find($differedSms->owner);

                if ($differedSms->contact) { ##Il s'agit d'un contact
                    $_contact = Contact::find($differedSms->contact);

                    SMS_HELPER::_sendSms(
                        $_contact->phone,
                        $differedSms->message,
                        $differedSms->expediteur,
                        true,
                        $owner
                    );

                    $differedSms->sended = 1;
                    $differedSms->save();
                } else { ##IL s'agit d'un groupe
                    $_groupe = Groupe::find($differedSms->group);
                    $contacts = $_groupe->contacts;

                    foreach ($contacts as $contact) {
                        SMS_HELPER::_sendSms(
                            $contact->phone,
                            $differedSms->message,
                            $differedSms->expediteur,
                            true,
                            $owner
                        );
                    }

                    $differedSms->sended = 1;
                    $differedSms->save();
                }
            }
        }
    }
}
