<?php

namespace App\Http\Controllers;

use App\CovidPatient;
use App\CovidSample;
use App\CovidSampleView;
use App\CovidTravel;
use App\City;
use App\Facility;
use App\Lookup;
use App\MiscCovid;
use Excel;
use DB;
use App\Mail\CovidDispatch;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class CovidSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type=1, $date_start=NULL, $date_end=NULL, $facility_id=NULL, $quarantine_site_id=NULL)
    {
        // 0 - not received
        // 1 - all
        // 2 - dispatched
        $user = auth()->user();
        $date_column = "covid_sample_view.created_at";
        if($type == 2) $date_column = "covid_sample_view.datedispatched";

        $samples = CovidSampleView::select(['covid_sample_view.*', 'u.surname', 'u.oname', 'r.surname as rsurname', 'r.oname as roname'])
            ->leftJoin('users as u', 'u.id', '=', 'covid_sample_view.user_id')
            ->leftJoin('users as r', 'r.id', '=', 'covid_sample_view.received_by')
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('covid_sample_view.facility_id', $facility_id);
            })
            ->when($quarantine_site_id, function($query) use ($quarantine_site_id){
                return $query->where('quarantine_site_id', $quarantine_site_id);
            })
            ->when($date_start, function($query) use ($date_column, $date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate($date_column, '>=', $date_start)
                    ->whereDate($date_column, '<=', $date_end);
                }
                return $query->whereDate($date_column, $date_start);
            })
            ->when(true, function($query) use ($type){
                if($type == 0) return $query->whereNull('datereceived');
                else if($type == 2) return $query->whereNotNull('datedispatched');
            })
            ->when(($type == 2), function($query) use ($date_column){
                return $query->orderBy($date_column, 'desc');
            })
            ->orderBy('covid_sample_view.id', 'desc')
            ->when(($user->user_type_id == 5), function($query) use ($user){
                return $query->whereRaw("(user_id='{$user->id}' OR covid_sample_view.facility_id='{$user->facility_id}')");
            })
            ->paginate();
        $myurl = url('/covid_sample/index/' . $type);
        $myurl2 = url('/covid_sample/index/');        
        $quarantine_sites = DB::table('quarantine_sites')->get();
        $data = compact('samples', 'myurl', 'myurl2', 'type', 'quarantine_sites', 'quarantine_site_id');
        $data['results'] = DB::table('results')->get();
        return view('tables.covidsamples', $data);
    }

    public function sample_search(Request $request)
    {
        // dd($request->all());
        $type = $request->input('type', 1);
        $submit_type = $request->input('submit_type');
        if($submit_type == 'excel') return $this->download_excel($request);
        if($submit_type == 'email') return $this->email_multiple($request);
        $to_print = $request->input('to_print');
        $date_start = $request->input('from_date', 0);
        if($submit_type == 'submit_date') $date_start = $request->input('filter_date', 0);
        $date_end = $request->input('to_date', 0);


        if($date_start == '') $date_start = 0;
        if($date_end == '') $date_end = 0;

        $quarantine_site_id = $request->input('quarantine_site_id', 0);
        $facility_id = $request->input('facility_id', 0);

        if(!$quarantine_site_id) $quarantine_site_id = 0;
        if(!$facility_id) $facility_id = 0;

        return redirect("covid_sample/index/{$type}/{$date_start}/{$date_end}/{$facility_id}/{$quarantine_site_id}");
    }

    public function download_excel($request)
    {
        $quarantine_site_id = $request->input('quarantine_site_id', 0);
        $facility_id = $request->input('facility_id', 0);
        $type = $request->input('type', 1);

        $date_start = $request->input('from_date', 0);
        $date_end = $request->input('to_date', 0);

        $date_column = "covid_sample_view.created_at";
        if($type == 2) $date_column = "covid_sample_view.datedispatched";

        $samples = CovidSampleView::where('repeatt', 0)
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('covid_sample_view.facility_id', $facility_id);
            })
            ->when($quarantine_site_id, function($query) use ($quarantine_site_id){
                return $query->where('quarantine_site_id', $quarantine_site_id);
            })
            ->when($date_start, function($query) use ($date_column, $date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate($date_column, '>=', $date_start)
                    ->whereDate($date_column, '<=', $date_end);
                }
                return $query->whereDate($date_column, $date_start);
            })
            ->when(true, function($query) use ($type){
                if($type == 0) return $query->whereNull('datereceived');
                else if($type == 2) return $query->whereNotNull('datedispatched');
            })
            ->when(($type == 2), function($query) use ($date_column){
                return $query->orderBy($date_column, 'desc');
            })
            ->get();

        extract(Lookup::covid_form());

        $data = [];

        foreach ($samples as $key => $sample) {
            $data[] = [
                'Lab ID' => $sample->id,
                'Identifier' => $sample->identifier,
                'Patient Name' => $sample->patient_name,
                'Age' => $sample->age,
                'Age' => $sample->age,
                'Gender' => $sample->get_prop_name($gender, 'sex', 'gender_description'),
                'Quarantine Site' => $sample->get_prop_name($quarantine_sites, 'quarantine_site_id'),
                'Date Collected' => $sample->datecollected,
                'Date Tested' => $sample->datetested,
                'Received Status' => $sample->get_prop_name($receivedstatus, 'receivedstatus'),
                'Result' => $sample->get_prop_name($results, 'result'),
            ];
        }
        if(!$data) return back();
        return MiscCovid::csv_download($data);
    }

    public function email_multiple($request)
    {
        $quarantine_site_id = $request->input('quarantine_site_id', 0);
        if(!$quarantine_site_id){
            session(['toast_error' => 1, 'toast_message' => 'Kindly select a quarantine site.']);
            return back();
        }
        $quarantine_site = DB::table('quarantine_sites')->where('id', $quarantine_site_id)->first();
        if($quarantine_site->email == ''){
            session(['toast_error' => 1, 'toast_message' => 'The quarantine site does not have an email address set.']);
            return back();            
        }

        $facility_id = $request->input('facility_id', 0);
        $type = 2;

        $date_start = $request->input('from_date', 0);
        $date_end = $request->input('to_date', 0);

        $date_column = "covid_samples.datedispatched";

        $samples = CovidSample::select('covid_samples.*')
            ->join('covid_patients', 'covid_samples.patient_id', '=', 'covid_patients.id')
            ->where('repeatt', 0)
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('facility_id', $facility_id);
            })
            ->when($quarantine_site_id, function($query) use ($quarantine_site_id){
                return $query->where('quarantine_site_id', $quarantine_site_id);
            })
            ->when($date_start, function($query) use ($date_column, $date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate($date_column, '>=', $date_start)
                    ->whereDate($date_column, '<=', $date_end);
                }
                return $query->whereDate($date_column, $date_start);
            })
            ->whereNotNull('datedispatched')
            ->orderBy($date_column, 'desc')
            ->get();

        if(!$samples->count()){
            session(['toast_error' => 1, 'toast_message' => 'No samples found']);
            return back(); 
        }

        $mail_array = explode(',', $quarantine_site->email);
        // Mail::to($mail_array)->send(new CovidDispatch($samples, $quarantine_site));
        Mail::to(['joelkith@gmail.com'])->send(new CovidDispatch($samples, $quarantine_site));
        session(['toast_message' => 'The results have been sent to the quarantine site.']);
        return back();            
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Lookup::covid_form();
        return view('forms.covidsamples', $data)->with('pageTitle', 'Add Sample');        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = Lookup::covid_arrays();

        $patient = new CovidPatient;
        $patient->fill($request->only($data['patient']));
        $patient->current_health_status = $request->input('health_status');
        $patient->save();

        $sample = new CovidSample;
        $sample->fill($request->only($data['sample']));
        $sample->patient_id = $patient->id;
        $sample->save();

        $travels = $request->input('travel');
        if($travels){
            $count = count($travels['travel_date']);

            for ($i=0; $i < $count; $i++) {
                $travel = new CovidTravel;
                $travel->travel_date = $travels['travel_date'][$i];
                $travel->city_id = $travels['city_id'][$i];
                // $travel->city_visited = $travels['city_visited'][$i];
                $travel->duration_visited = $travels['duration_visited'][$i];
                $travel->patient_id = $patient->id;
                $travel->save();
            }
        }
        session(['toast_message' => "The sample has been created."]);
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CovidSample  $covidSample
     * @return \Illuminate\Http\Response
     */
    public function show(CovidSample $covidSample)
    {
        $user = auth()->user();
        $type=1;

        $samples = CovidSampleView::select(['covid_sample_view.*', 'u.surname', 'u.oname', 'r.surname as rsurname', 'r.oname as roname'])
            ->leftJoin('users as u', 'u.id', '=', 'covid_sample_view.user_id')
            ->leftJoin('users as r', 'r.id', '=', 'covid_sample_view.received_by')
            ->when(($user->user_type_id == 5), function($query) use ($user){
                return $query->whereRaw("(user_id='{$user->id}' OR covid_sample_view.facility_id='{$user->facility_id}')");
            })
            ->when(true, function($query) use ($covidSample){
                if($covidSample->parentid){
                    return $query->whereRaw(" (covid_sample_view.id = {$covidSample->parentid} OR parentid = {$covidSample->parentid})");
                }else{
                    return $query->whereRaw(" (covid_sample_view.id = {$covidSample->id} OR parentid = {$covidSample->id})");
                }
            })            
            ->orderBy('run', 'desc')
            ->paginate();
        $myurl = url('/covid_sample/index/' . $type);
        $myurl2 = url('/covid_sample/index/');        
        $p = Lookup::get_partners();
        $data = array_merge($p, compact('samples', 'myurl', 'myurl2', 'type'));
        $data['results'] = DB::table('results')->get();
        return view('tables.covidsamples', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CovidSample  $covidSample
     * @return \Illuminate\Http\Response
     */
    public function edit(CovidSample $covidSample)
    {
        $data = Lookup::covid_form();
        $covidSample->load(['patient.facility']);
        $data['sample'] = $covidSample;
        return view('forms.covidsamples', $data)->with('pageTitle', 'Edit Sample');      
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CovidSample  $covidSample
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CovidSample $covidSample)
    {
        $data = Lookup::covid_arrays();

        $covidSample->fill($request->only($data['sample']));
        $covidSample->pre_update();


        $patient = $covidSample->patient;
        $patient->fill($request->only($data['patient']));
        $patient->current_health_status = $request->input('health_status');
        $patient->pre_update();

        $travels = $request->input('travel');
        if($travels){
            $count = count($travels['travel_date']);

            for ($i=0; $i < $count; $i++) {
                if(isset($travels['travel_id'][$i])) $travel = CovidTravel::find($travels['travel_id'][$i]);
                else{
                    $travel = new CovidTravel;
                }
                $travel->travel_date = $travels['travel_date'][$i];
                $travel->city_id = $travels['city_id'][$i];
                $travel->duration_visited = $travels['duration_visited'][$i];
                $travel->patient_id = $patient->id;
                $travel->pre_update();
            }
        }
        session(['toast_message' => "The sample has been updated."]);
        return redirect('/covid_sample');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CovidSample  $covidSample
     * @return \Illuminate\Http\Response
     */
    public function destroy(CovidSample $covidSample)
    {
        if($covidSample->worksheet_id || $covidSample->receivedstatus == 2){
            session(['toast_error' => 1, 'toast_message' => 'The sample cannot be deleted.']);
            return back();
        }
        // $covidSample->travel()->delete();
        $covidSample->delete();
        session(['toast_message' => 'The sample has been deleted.']);
        return back();
    }




    public function upload(Request $request)
    {
        $file = $request->upload->path();
        // config(['excel.import.heading' => false]);
        $data = Excel::load($file, function($reader){
            $reader->toArray();
        })->get();

        foreach ($data as $key => $row) {
            if(!$key) continue;

            $f = Facility::locate($row[2])->first();
            $s = CovidSample::create([
                'facility_id' => $f->id,
                'patient_name' => $row[4],
                'patient' => $row[5],
                'dob' => $row[6],
                'age' => $row[7],
                'sex' => $row[8],
                'residence' => $row[9],
                'phone_no' => $row[10],
            ]);
        }
    }

    public function result(CovidSample $covidSample)
    {
        $data = Lookup::covid_form();
        $data['samples'] = [$covidSample];
        return view('exports.mpdf_covid_samples', $data);
    }

    public function print_multiple(Request $request)
    {
        $ids = $request->input('sample_ids');
        $data = Lookup::covid_form();
        $data['samples'] = CovidSample::whereIn('id', $ids)->get();
        return view('exports.mpdf_covid_samples', $data);
    }


    public function cities(Request $request)
    {
        $search = $request->input('search');
        $cities = City::whereRaw("(name like '%" . $search . "%')")
            ->paginate(10);
        return $cities;
    }



    public function search(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(covid_patients.facility_id='{$user->facility_id}' OR covid_patients.user_id='{$user->id}')";

        $samples = CovidSample::select('covid_samples.id')
            ->whereRaw("covid_samples.id like '" . $search . "%'")
            ->when($facility_user, function($query) use ($string){
                return $query->join('covid_patients', 'covid_samples.batch_id', '=', 'covid_patients.id')->whereRaw($string);
            })
            ->paginate(10);

        $samples->setPath(url()->current());
        return $samples;
    }
}
