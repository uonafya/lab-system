<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecycleBin extends Model
{

    public static function page_links($base, $page=NULL, $last_page=NULL, $date_start=NULL, $date_end=NULL)
    {
        $str = "";
        $datestring = "";

        if($date_start){
            $datestring .= '/' . $date_start;
            if($date_end){
                $datestring .= '/' . $date_end;
            }
        }
        $next = $page+1;
        $previous = $page-1;

        if($page != 1){
            $str .= "<a href='" . url($base . '/1' . $datestring) . "'>First Page</a> |";
            $str .= "<a href='" . url($base . '/' . $previous . $datestring) . "'>Prev</a> |";
        }

        $str .= "<a href='" . url($base . '/' . $page . $datestring) . "'>{$page}</a> |";

        if($page < $last_page ){
            $str .= "<a href='" . url($base . '/' . $next . $datestring) . "'>Next</a> | ";
            $str .= "<a href='" . url($base . '/' . $last_page . $datestring) . "'>Last Page</a>";
        }
        return $str;
    }



    public static function batch_status($batch_id, $batch_complete, $pre='', $approval=false){

    	if($approval){
    		$url = "<td><a href='" . url($pre . 'batch/site_approval/' . $batch_id) . "'>View Samples For Approve</a></td>";
    	}
    	else{
    		$url = "<td><a href='" . url($pre . 'batch/' . $batch_id) . "'>View</a>";

    		if($batch_complete==1){
    			$url .= "| <a href='" . url($pre . 'batch/summary/' . $batch_id) . "'><i class='fa fa-print'></i> Summary</a> | 
    			<a href='" . url($pre . 'batch/individual/' . $batch_id) . "'><i class='fa fa-print'></i> Individual </a> |
    			 <a href='" . url($pre . 'batch/email/' . $batch_id) . "'><i class='fa fa-print'></i> Email </a>"; 
    		}

    		$url .= "</td>";
    	}

        if($batch_complete == 0){
            return "<td>In Process</td>" . $url;
        }
        else{
            return "<td>Complete</td>" . $url;
        }
    }

    

	public static function synch_vl_batches()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		$my = new MiscViral;
		$my->save_tat(ViralsampleView::class, Viralsample::class);

		while (true) {
			$batches = Viralbatch::with(['sample.patient:id,national_patient_id'])->where('synched', 0)->limit(10)->get();
			if($batches->isEmpty()) break;

			$response = $client->request('post', 'synch/viralbatches', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'form_params' => [
					'batches' => $batches->toJson(),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->batches as $key => $value) {
				$update_data = ['national_batch_id' => $value->national_batch_id, 'synched' => 1, 'datesynched' => $today,];
				Viralbatch::where('id', $value->original_id)->update($update_data);
			}

			foreach ($body->samples as $key => $value) {
				$update_data = ['national_sample_id' => $value->national_sample_id, 'synched' => 1, 'datesynched' => $today,];
				Viralsample::where('id', $value->original_id)->update($update_data);
			}
		}
	}

	public static function synch_eid_batches()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		$my = new Misc;
		$my->save_tat(SampleView::class, Sample::class);

		while (true) {
			$batches = Batch::with(['sample.patient:id,national_patient_id'])->where('synched', 0)->limit(10)->get();
			if($batches->isEmpty()) break;

			$response = $client->request('post', 'synch/batches', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'form_params' => [
					'batches' => $batches->toJson(),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->batches as $key => $value) {
				$update_data = ['national_batch_id' => $value->national_batch_id, 'synched' => 1, 'datesynched' => $today,];
				Batch::where('id', $value->original_id)->update($update_data);
			}

			foreach ($body->samples as $key => $value) {
				$update_data = ['national_sample_id' => $value->national_sample_id, 'synched' => 1, 'datesynched' => $today,];
				Sample::where('id', $value->original_id)->update($update_data);
			}
		}
	}
}
