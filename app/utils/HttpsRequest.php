<?php


namespace App\utils;


use Illuminate\Http\Request;

class HttpsRequest
{

    public static function generateAccessToken()
    {
//        $grant_type = $_ENV['REGISTRY_GRANT_TYPE'];
//        $scope = $_ENV['REGISTRY_SCOPE'];
//        $client_id = $_ENV['REGISTRY_CLIENT_ID'];
//        $client_secret = $_ENV['REGISTRY_CLIENT_SECRET'];

        $client_id = 'partner.test.client';
        $client_secret = 'partnerTestPwd';
        $grant_type = 'client_credentials';
        $scope = 'DHP.Gateway DHP.Visitation';

        $curl = curl_init();
        // Check if initialization had gone wrong*
        if ($curl === false) {
            throw new Exception('failed to initialize');
        }
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_URL => 'token_endpoint_url',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=' . $grant_type . '&scope=' . $scope . '&client_id=' . $client_id . '&client_secret=' . $client_secret,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));


        $response = curl_exec($curl);

        curl_close($curl);
        echo "Result = " . $response;
        return $response;

    }

//identifierType and upn value to validate
    public static function getRegistryClient($access_token, $identifierType, $identifierValue)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'endpoint_url' . $identifierType . '/' . $identifierValue . '?format=json',
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