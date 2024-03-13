<?php

namespace App\Imports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;


class ContactsImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $contact)
    {
        $contact = Contact::create([
            'firstname'    => isset($contact['lastname']) ? $contact['lastname'] : null,
            'lastname'     => isset($contact['lastname']) ? $contact['lastname'] : null,
            'phone'    => isset($contact['phone']) ? $contact['phone'] : null,
            'detail'    => isset($contact['detail']) ? $contact['detail'] : null,
        ]);

        $contact->owner = request()->user()->id;
        $contact->save();

        return $contact;
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
