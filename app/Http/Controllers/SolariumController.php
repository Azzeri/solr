<?php

namespace App\Http\Controllers;

use \Solarium\Client;

class SolariumController extends Controller
{
    public function __construct(protected Client $client)
    {
    }

    public function ping()
    {
        // create a ping query
        $ping = $this->client->createPing();

        // execute the ping query
        try {
            $this->client->ping($ping);
            return response()->json('OK');
        } catch (\Solarium\Exception $e) {
            return response()->json('ERROR', 500);
        }
    }
}
