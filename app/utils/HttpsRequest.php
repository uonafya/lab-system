<?php


namespace App\utils;


use Illuminate\Http\Request;

class HttpsRequest
{

    public static function generateAccessToken()
    {
        $username = $_ENV['REGISTRY_USER'];
        $password = $_ENV['REGISTRY_PASSWORD'];
        $client_id = $_ENV['REGISTRY_CLIENT_ID'];
        $secret_id = $_ENV['REGISTRY_CLIENT_SECRET'];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://api.kmhfl.health.go.ke/o/token/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=password&username=' . $username . '&password=' .$password . '&scope=read&client_id=' . $client_id . '&client_secret=' . $secret_id,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));


        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }
//identifierType and upn value to validate
    public static function getRegistryClient($access_token, $identifierType, $identifierValue)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://api.kmhfl.health.go.ke/api/' . $identifierType . '/' . $identifierValue . '?format=json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $access_token
            ),
        ));

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        $results = $response['results'];

        //return results  with patient upi number
    }
}