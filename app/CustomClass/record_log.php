<?php
namespace App\CustomClass;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\t_log;

class record_log
{
    public static function  save_log($sample_id,$patient_id,$batch_id,$action)
    {
        try{
            $user = auth()->user();
            $date=date('Y-m-d h:i:s');
            DB::table('sample_log_trail')->insert(
                [
                    'id'=>Str::uuid()->toString(),
                    'sample_id'=>$sample_id,
                    'user_id'=>$user->id,
                    'action'=>$action,
                    'patient_id'=>$patient_id,
                    'batch_id'=>$batch_id,
                    'created_at'=>$date,
                    'updated_at'=>$date
                ]
            );


        } catch(\Illuminate\Database\QueryException $ex){
           Log::error($ex->getMessage());
        }

    }
}