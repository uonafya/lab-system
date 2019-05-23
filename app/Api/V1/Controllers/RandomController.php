<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Exception;

class RandomController extends Controller
{
    public function protected_route()
    {
        return response()->json([
                    'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
                ]);
    }  

    public function refresh_route()
    {
        return response()->json([
                        'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                    ]);
    }  

    public function hello()
    {
        return response()->json([
                'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
            ]);
    } 

    public function hello_nascop()
    {
        try {
            \App\Synch::test_nascop();
        } catch (Exception $e) {
            // return $this->response->errorBadRequest("NASCOP cannot be reached.");
            return response()->json([
                    'message' => 'NASCOP cannot be reached.'
                ], 500);
        }
        return response()->json([
                'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
            ], 200);
    }
}
