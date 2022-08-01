<?php

namespace App\Classes;

use GuzzleHttp\Exception\ClientException;
use Todoist\Todoist;

class TodoistClient extends Todoist
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
    private $token = NULL;

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
        $this->client = new \GuzzleHttp\Client(array(
            'base_uri' => 'https://todoist.com/api/v8/',
            'timeout' => 10,
            'verify' => TRUE,
        ));
        $this->token = $token;
    }

    public function request($type, $args)
    {
        // Build the command.
        $command = [
            'type' => $type,
            'uuid' => uuid_create(),
            'temp_id' => uuid_create(),
            'args' => (array)$args,
        ];

        // Stop quickly if the command should be queued.
        if ($this->useCommandQueue()) {
            $this->command_queue[] = $command;
            return $this;
        }

        // Build the API call
        $method = 'sync';
        $commands = [];
        $commands[] = $command;

        $response = $this->getApiResponse($method, ['commands' => $commands]);
        // Validate the response.
        if (isset($response['sync_status'][$command['uuid']])) {
            $data = $response['sync_status'][$command['uuid']];
            // Handle the simple response.
            if ($data === 'ok') {
                return $response['temp_id_mapping'][$command['temp_id']] ?? true;
            }
            // Handle the error response.
            if (isset($data['error'])) {
                throw new \ErrorException($data['error'], $data['error_code']);
            }
            // Handle the complex response (multiple return values).
            foreach ($data as $d) {
                if (isset($d['error'])) {
                    throw new \ErrorException($d['error'], $d['error_code']);
                }
            }
            return true;
        }
    }

    public function getApiResponse($method, $params)
    {
        // Make the request.
        $params['token'] = $this->token;
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                $params[$k] = json_encode($v);
            }
        }

        try {
            $response = $this->client->post($method, array(
                'form_params' => $params,
            ));
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $responseBodyAsObject = json_decode($responseBodyAsString);
            throw new \ErrorException($responseBodyAsObject->error, $responseBodyAsObject->http_code);
        }

        // Parse the body.
        $body = $response->getBody();
        $data = json_decode($body, true);

        // Return when everything is OK.
        if ($response->getStatusCode() == 200 && is_array($data)) {
            return $data;
        }

        /** @link https://developer.todoist.com/#errors */
        switch ($response->getStatusCode()) {
            case 400:
                throw new \ErrorException('The request was incorrect.');
            case 404:
                throw new \ErrorException('The requested resource could not be found.');
            case 401:
                throw new \ErrorException('Authentication is required, and has failed, or has not yet been provided.');
            case 403:
                throw new \ErrorException('Unauthorized request to Todoist.');
            case 429:
                throw new \ErrorException('You have exceeded your API limit with Todoist.');
            case 500:
                throw new \ErrorException('The request failed due to a server error.');
            case 503:
                throw new \ErrorException('Todoist is unreachable.');
            default:
                throw new \ErrorException('Error making API request to Todoist.');
        }

        // Look for an error message in JSON.
        if (is_array($data) && isset($data['error'])) {
            throw new \ErrorException($data['error'], $data['error_code']);
        }
    }

    public function register($email, $fullName, $password)
    {
        $response = $this->getApiResponse('user/register', [
            'email' => $email,
            'full_name' => $fullName,
            'password' => $password
        ]);

        return $response;
    }
}