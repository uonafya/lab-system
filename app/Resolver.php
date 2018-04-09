<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Lookup;

class Resolver extends Model
{

    public static function my_date_format($value)
    {
        if($value) return date('d-M-Y', strtotime($value));
        return '';
    }

    public static function get_gender($value)
    {
        $value = trim($value);
        if($value == 'M' || $value == 'm'){
            return 1;
        }
        else if($value == 'F' || $value == 'f'){
            return 2;
        }
        else if($value == 'No Data' || $value == 'No data'){
            return 3;
        }
        else if (is_int($value)){
            return $value;
        }
        else{
            return 3;
        }
    }


    public static function facility_mfl($mfl)
    {
        self::cacher(); 
        $fac = Cache::get('facilities');       
        return $fac->where('facilitycode', $mfl)->first()->id;
    }


	public function viral_regimen($value)
	{
		Lookup::cacher();		
        $my_array = Cache::get('prophylaxis');       
        return $my_array->where('category', $val)->first()->id ?? 16;
	}    

	public function justification($value)
	{
		Lookup::cacher();		
        $my_array = Cache::get('justifications');       
        return $my_array->where('rank', $val)->first()->id ?? 8;
	}    

	public function sample_type($value)
	{
		Lookup::cacher();		
        $my_array = Cache::get('sample_types');       
        return $my_array->where('alias', $val)->first()->id;
	}


    /*public function index($batch_complete=4, $page=NULL, $date_start=NULL, $date_end=NULL)
    {
        $myurl = url('batch/index/' . $batch_complete . '/' . $page . '/');
        $user = auth()->user();
        $facility_user = false;
        if($user->user_type_id == 5) $facility_user=true;

        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";

        $my = new Misc;
        $b = Batch::selectRaw('count(id) as mycount')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('batches.datereceived', '>=', $date_start)
                    ->whereDate('batches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('batches.datereceived', $date_start);
            })
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->when(true, function($query) use ($batch_complete){
                if($batch_complete < 4) return $query->where('batch_complete', $batch_complete);
            })
            ->get()
            ->first();

        $page_limit = env('PAGE_LIMIT', 10);

        if($page == NULL || $page == 'null'){
            $page=1;
        }

        $last_page = ceil($b->mycount / $page_limit);
        $last_page = (int) $last_page;

        $offset = ($page-1) * $page_limit;

        $batches = Batch::select(['batches.*', 'facilitys.name'])
            ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('batches.datereceived', '>=', $date_start)
                    ->whereDate('batches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('batches.datereceived', $date_start);
            })
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->when(true, function($query) use ($batch_complete){
                if($batch_complete < 4) return $query->where('batch_complete', $batch_complete);
            })
            ->orderBy('datereceived', 'desc')
            ->limit($page_limit)
            ->offset($offset)
            ->get();

        if($batches->isEmpty()){
            return view('tables.batches', ['rows' => null, 'links' => null, 'myurl' => $myurl, 'pre' => '']);
        }

        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = $my->get_subtotals($batch_ids, false);
        $rejected = $my->get_rejected($batch_ids, false);
        $currentdate=date('d-m-Y');

        $table_rows = "";

        foreach ($batches as $key => $batch) {

            $neg = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 1));
            $pos = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 2));
            $failed = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 3));
            $redraw = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 5));
            $noresult = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 0));

            $rej = $this->checknull($rejected->where('batch_id', $batch->id));
            $total = $neg + $pos + $failed + $redraw + $noresult + $rej;

            $result = $pos + $neg + $redraw + $failed;

            $datereceived=date("d-M-Y",strtotime($batch->datereceived));

            if($batch->batch_complete == 0){
                $max = $currentdate;
            }
            else{
                $max=date("d-M-Y",strtotime($batch->datedispatched));
            }

            $delays = $my->working_days($datereceived, $max);

            $table_rows .= "<tr> 
            <td>{$batch->id}</td>
            <td>{$batch->name}</td>
            <td>{$batch->datereceived}</td>
            <td>" . $batch->created_at->toDateString() . "</td>
            <td>{$delays}</td>
            <td></td>
            <td>{$total}</td>
            <td>{$rej}</td>
            <td>{$result}</td>
            <td>{$noresult}</td>" . $my->batch_status($batch->id, $batch->batch_complete) . "
            </tr>";
        }
        $base = '/batch/index/' . $batch_complete;

        $links = $my->page_links($base, $page, $last_page, $date_start, $date_end);

        return view('tables.batches', ['rows' => $table_rows, 'links' => $links, 'myurl' => $myurl, 'pre' => '']);
    }*/

    
    /*public function index($batch_complete=4, $page=NULL, $date_start=NULL, $date_end=NULL)
    {
        $myurl = url('viralbatch/index/' . $batch_complete . '/' . $page . '/');
        $user = auth()->user();
        $my = new MiscViral;
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";
        
        $b = Viralbatch::selectRaw('count(id) as mycount')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('viralbatches.datereceived', '>=', $date_start)
                    ->whereDate('viralbatches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('viralbatches.datereceived', $date_start);
            })
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->when(true, function($query) use ($batch_complete){
                if($batch_complete < 4) return $query->where('batch_complete', $batch_complete);
            })
            ->get()
            ->first();

        $page_limit = env('PAGE_LIMIT', 10);

        if($page == NULL || $page == 'null'){
            $page=1;
        }

        $last_page = ceil($b->mycount / $page_limit);
        $last_page = (int) $last_page;

        $offset = ($page-1) * $page_limit;

        $batches = Viralbatch::select('viralbatches.*', 'facilitys.name')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('viralbatches.datereceived', '>=', $date_start)
                    ->whereDate('viralbatches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('viralbatches.datereceived', $date_start);
            })
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->when(true, function($query) use ($batch_complete){
                if($batch_complete < 4) return $query->where('batch_complete', $batch_complete);
            })
            ->orderBy('datereceived', 'desc')
            ->limit($page_limit)
            ->offset($offset)
            ->get();

        if($batches->isEmpty()){
            return view('tables.batches', ['rows' => null, 'links' => null, 'myurl' => $myurl, 'pre' => 'viral']);
        }

        $batch_ids = $batches->pluck(['id'])->toArray();

        $noresult_a = $my->get_totals(0, $batch_ids, false);
        $redraw_a = $my->get_totals(5, $batch_ids, false);
        $failed_a = $my->get_totals(3, $batch_ids, false);
        $detected_a = $my->get_totals(2, $batch_ids, false);
        $undetected_a = $my->get_totals(1, $batch_ids, false);

        $rejected = $my->get_rejected($batch_ids, false);
        $currentdate=date('d-m-Y');

        $table_rows = "";

        foreach ($batches as $key => $batch) {

            $undetected = $this->checknull($undetected_a->where('batch_id', $batch->id));
            $detected = $this->checknull($detected_a->where('batch_id', $batch->id));
            $failed = $this->checknull($failed_a->where('batch_id', $batch->id));
            $redraw = $this->checknull($redraw_a->where('batch_id', $batch->id));
            $noresult = $this->checknull($noresult_a->where('batch_id', $batch->id));

            $rej = $this->checknull($rejected->where('batch_id', $batch->id));
            $total = $undetected + $detected + $failed + $redraw + $noresult + $rej;

            $result = $detected + $undetected + $redraw + $failed;

            $datereceived=date("d-M-Y",strtotime($batch->datereceived));

            if($batch->batch_complete == 0){
                $max = $currentdate;
            }
            else{
                $max=date("d-M-Y",strtotime($batch->datedispatched));
            }

            $delays = $my->working_days($datereceived, $max);

            $table_rows .= "<tr> 
            <td>{$batch->id}</td>
            <td>{$batch->name}</td>
            <td>{$batch->datereceived}</td>
            <td>" . $batch->created_at->toDateString() . "</td>
            <td>{$delays}</td>
            <td></td>
            <td>{$total}</td>
            <td>{$rej}</td>
            <td>{$result}</td>
            <td>{$noresult}</td>" . $my->batch_status($batch->id, $batch->batch_complete) . "
            </tr>";
        }

        $links = $my->page_links($page, $last_page, $date_start, $date_end);

        return view('tables.batches', ['rows' => $table_rows, 'links' => $links, 'myurl' => $myurl, 'pre' => 'viral']);
    }*/
}
