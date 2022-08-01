<?php

namespace App\Classes\Services;

use App\Classes\ZohoSignClient;
use App\Models\Signature;
use App\Models\SignatureHistory;

class SignService
{

    public function __construct()
    {
        $this->ZohoSignClient = new ZohoSignClient();
    }

    public function getactionid($template_id) {
        $ZohoSignClient = $this->ZohoSignClient->GetAction($template_id);
        return $ZohoSignClient;
    }


    public function sign($template, $data)
    {
        $actions['action_id'] = $this->getactionid($template);
        $actions['action_type'] = "SIGN";
        $actions['recipient_name'] = $data['recipient_name'];
        $actions['recipient_email'] = $data['recipient_email'];
        $actions['recipient_phonenumber'] = str_replace('+33', '', $data['recipient_phonenumber']);
        $actions['recipient_phonenumber'] = str_replace('+ 33', '', $actions['recipient_phonenumber']);
        $actions['recipient_phonenumber'] = str_replace(' ', '', $actions['recipient_phonenumber']);
        $actions['recipient_phonenumber'] = str_replace('.', '', $actions['recipient_phonenumber']);
        $actions['recipient_phonenumber'] = str_replace('+', '', $actions['recipient_phonenumber']);

        $actions['recipient_countrycode'] = "+33";
        $actions['private_notes'] = "";
        $actions['verify_recipient'] = false;
        $actions['language'] = "fr";
        $field_data['field_text_data'] = [
            
            'Nom complet' => $data['name'],
            'address' => $data['address'],
            'city' => $data['city'],
            'postal_code' => $data['postal_code'],
            'total_hour' => $data['hours'],
            'income' => $data['rate'],
            'date_start' => $data['date_start'],
            'date_end' => $data['date_end'],
            'mission_description' => $data['mission_description']
        ];
        $data = [
            'templates' => [
                'field_data' => $field_data,
                'actions' => [$actions],
                'notes' => "",
                'request_name' => $data['request_name']
            ],

        ];
        $data = json_encode($data);
        $ZohoSignClient = $this->ZohoSignClient->SendDocForSign($template, ["data" => $data]);
        return $ZohoSignClient;
    }

    public function recallsign($request_id)
    {
        $ZohoSignClient = $this->ZohoSignClient->DeleteSign($request_id);
    }
}
