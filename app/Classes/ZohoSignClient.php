<?php

namespace App\Classes;

use App\Models\Document;
use App\Models\TemplateSign;
use App\Models\Setting;
use App\Models\Signature;
use GuzzleHttp\Client;



use GuzzleHttp\Exception\ClientException;

use Illuminate\Support\Facades\DB;

class ZohoSignClient
{
    /**
     * Maintain a Guzzle client.
     * @var \GuzzleHttp\Client
     */
    private $client = NULL;

    /**
     * The authentication token.
     * @var string
     */
    private $access_token = NULL;

    private $refresh_token =  NULL;

    private $client_id = NULL;

    private $secret_id = NULL;

    private $redirect_url = NULL;

    /**
     * Track the command queue for this engine.
     * @var array
     */
    private $command_queue = [];

    /**
     * @see Todoist::setToken
     * @param string $token
     */
    public function __construct($token = NULL)
    {
        $this->client_id = env('ZOHO_CLIENT_ID');
        $this->secret_id = env('ZOHO_SECRET_ID');
        $this->refresh_token = env('ZOHO_REFRESH_TOKEN');
        $this->redirect_url = 'https://api.smice.com/zohocallback';
        $this->client = new Client(array(
            'base_uri' => 'https://sign.zoho.com/api/v1/',
            'timeout' => 10,
            'verify' => TRUE,
        ));
        $this->CheckToken();
       
        $this->access_token = Setting::get('access_token');
    }

    public function getApiResponse($type, $method, $params)
    {
        $i = $this->access_token;
        try {
            $response = $this->client->request($type, $method, array(
                'headers' => ['Authorization' => "Zoho-oauthtoken " . $this->access_token],
                'form_params' => $params,
            ));
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $responseBodyAsObject = json_decode($responseBodyAsString);
            
            
           // throw new \ErrorException($responseBodyAsObject);
        }

        // Parse the body.
        $body = $response->getBody();
        $data = json_decode($body, true);

        // Return when everything is OK.
        if ($response->getStatusCode() == 200 && is_array($data)) {
            return $data;
        }

        switch ($response->getStatusCode()) {
            case 400:
                throw new \ErrorException('Erreur création de contrat : ' . $responseBodyAsString);
            case 401:
                throw new \ErrorException('Unauthorized access or invalid auth token');
            case 404:
                throw new \ErrorException('URL not found');
            case 405:
                throw new \ErrorException('method not allowed or method you have called is not supported for the invoked API');
            case 500:
                throw new \ErrorException('internal error');
            default:
                throw new \ErrorException('Error making API request to Zoho Sign.');
        }

        // Look for an error message in JSON.
        if (is_array($data) && isset($data['error'])) {
            throw new \ErrorException($data['error'], $data['error_code']);
        }
    }

    public function CheckToken()
    {
        //check if access_token is not expired
        $access_token_timestamp = Setting::get('access_token_timestamp');
        if (time() - $access_token_timestamp >= 3600) {
            $this->RefreshToken();
        }
    }
    //https://accounts.zoho.com/oauth/v2/auth?scope=ZohoSign.documents.ALL,ZohoSign.templates.ALL&client_id=1000.7LQOV7JJMNU6FXW49EGTWM425YXC8H&response_type=code&redirect_uri=https://api.smice.com/zohocallback
    public function GetRefreshToken()
    {
        $access_token = Setting::get('access_token');
        $response = $this->getApiResponse('POST', 'https://accounts.zoho.com/oauth/v2/token', [
            'code' => '1000.f627579bcbb3ad114f2eeca73531d032.853df24f63382b2e27bdfdf33a25d019',
            'client_id' => $this->client_id,
            'client_secret' => $this->secret_id,
            'redirect_uri' => $this->redirect_url,
            'grant_type' => 'authorization_code'
        ]);

        if (!isset($response['error'])) {
        }

        return $response;
    }

    public function RefreshToken()
    {
        $response = $this->getApiResponse('POST', 'https://accounts.zoho.com/oauth/v2/token', [
            'refresh_token' => $this->refresh_token,
            'client_id' => $this->client_id,
            'client_secret' => $this->secret_id,
            'redirect_uri' => $this->redirect_url,
            'grant_type' => 'refresh_token'
        ]);

        if (!isset($response['error'])) {
            $access_token = $response['access_token'];
            Setting::add('access_token', $access_token);
            Setting::add('access_token_timestamp', time());
        }

        return $response;
    }

    public function GetRequests($id) {
        $response = $this->getApiResponse('GET', 'requests/' . $id, []);
        return $response;
    }

    public function GetTemplate()
    {
        $response = $this->getApiResponse('GET', 'templates', []);
        foreach ($response['templates'] as $t) {
                //get infos about template
                $template = TemplateSign::where('template_id', $t['template_id'])->first();
                if (!$template) {
                    $template = new TemplateSign();
                }
                unset($t['modified_time']);
                $template->fill($t);
                $template->save();

                //get infos about document
                foreach ($t['document_ids'] as $d) {
                    $document = Document::where('document_id', $d['document_id'])->first();
                    if (!$document) {
                        $document = new Document();
                    }
                    $document->templatesign_id = $t['template_id'];
                    $document->fill($d);
                    $document->save();
                }
        }
        return $response;
    }

    public function DeleteSign($request_id)
    {
        $response = $this->getApiResponse('POST', 'requests/' . $request_id . '/recall', null);
        Signature::where('request_id', $request_id)->delete();;
        return $response;
    }

    public function GetAction($template_id)
    {
        $response = $this->getApiResponse('GET', 'templates/' . $template_id, null);
        foreach ($response['templates']['actions'] as $action) {
            if ($action['action_type'] === 'SIGN') {
                return $action['action_id'];
            }
        }
        
    }

    public function SendDocForSign($template, $data)
    {
        $response = $this->getApiResponse('POST', 'templates/' . $template . '/createdocument', $data);
        //get docu_id
        //add recode in signature table
        $signature = new Signature();
        $signature->request_status = $response['requests']['request_status'];
        $signature->request_id = $response['requests']['request_id'];
        //$signature->action_time = $response['requests']['action_time'];
        //$signature->modified_time = $response['requests']['modified_time'];
        $signature->is_delete = $response['requests']['is_deleted'];
        $signature->expiration_day = $response['requests']['expiration_days'];
        //$signature->sign_submitted_time = $response['requests']['sign_submitted_time'];
        $signature->owner_first_name = $response['requests']['owner_first_name'];
        //$signature->sign_percentage = $response['requests']['sign_percentage'];
        //$signature->expire_by = $response['requests']['expire_by'];
        //$signature->created_time = $response['requests']['created_time'];
        $signature->email_reminders = $response['requests']['email_reminders'];
        $signature->document_id = $response['requests']['document_ids'][0]['document_id'];
        $signature->save();
        return $response;
        
    }

    
}
