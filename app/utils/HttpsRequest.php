<?php


namespace App\utils;


use Illuminate\Http\Request;
use function MongoDB\BSON\toJSON;

class HttpsRequest
{
    public static function search_client_upi($client_upi)

    {
        $token = json_decode(self::generateAccessToken(), true);
        //dd (self::getRegistryClient($token['access_token'], 'MOH202205001'));
        return self::getRegistryClient($token['access_token'], $client_upi);
    }

    public static function generateAccessToken()
    {
//        $grant_type = $_ENV['REGISTRY_GRANT_TYPE'];
//        $scope = $_ENV['REGISTRY_SCOPE'];
//        $client_id = $_ENV['REGISTRY_CLIENT_ID'];
//        $client_secret = $_ENV['REGISTRY_CLIENT_SECRET'];
//        $token_url = $_ENV['REGISTRY_CLIENT_TOKEN_URL'];

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
            CURLOPT_URL => '',
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

        return $response;

    }

//identifierType and upn value to validate
    public static function getRegistryClient($access_token, $client_upi)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_URL => '' . $client_upi . '?format=json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $access_token),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response, true);

        return $response['client'];

    }
}