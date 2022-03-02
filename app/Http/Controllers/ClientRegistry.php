<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Patient;

Use App\Helpers\Http;

class ClientRegistry extends Controller
{
    public function updatePatientList()
    {
        return null;
    }
    
    public function generateCCC_NO($mfl_code, $serial_no)
    {
        //mfl code + serial no
        $ccc_no = $mfl_code.'-'.$serial_no;
        
        return $ccc_no;       
        
    }
    
    public function getPatients()
    {
        $data = Http::get('http://localhost:3000/patient');
        $patients = json_decode($data->getBody()->getContents());
        dd($patients);
    }
    
    public function getCCC_No()
    {
        $data = Http::get('http://localhost:3000/ccc_no');
        $ccc_no = json_decode($data->getBody()->getContents());
        dd($ccc_no);
        
    }    
        
    public function search(Patient $patient, Request $request)
    {
        
    }
    


    // public function addPatients()
    // {
    //     $data = Http::post('https://jsonplaceholder.typicode.com/posts', [
    //         'title' => 'foo',
    //         'body' => 'bar',
    //         'userId' => 1
    //     ]);
    //     $post = json_decode($data->getBody()->getContents());
    //     dd($post);
    // }
    
    
}
