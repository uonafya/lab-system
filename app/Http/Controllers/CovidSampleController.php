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
use Mpdf\Mpdf;
use DB;
use App\Mail\CovidDispatch;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\CovidRequest;
use Illuminate\Http\Request;


class CovidSampleController extends Controller
{

    public function __construct()
    {
        $this->middleware('covid_allowed');   
    }

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
        // 3 - from cif
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
                else if($type == 3) return $query->whereNull('datereceived')->where('u.email', 'joelkith@gmail.com');
            })
            ->when(($type == 2), function($query) use ($date_column){
                return $query->orderBy($date_column, 'desc');
            })
            ->when(!$user->facility_user, function($query) use ($user){
                return $query->where('covid_sample_view.lab_id', $user->lab_id);
            })
            ->when($user->quarantine_site, function($query) use ($user){
                return $query->where('quarantine_site_id', $user->facility_id);
            })
            ->when($user->facility_user, function($query) use ($user){
                return $query->whereRaw("(user_id='{$user->id}' OR covid_sample_view.facility_id='{$user->facility_id}')");
            })
            ->orderBy('covid_sample_view.id', 'desc')
            ->paginate();

        $samples->setPath(url()->current());

        $facility = Facility::find($facility_id);
        
        $myurl = url('/covid_sample/index/' . $type);
        $myurl2 = url('/covid_sample/index/');        
        $quarantine_sites = DB::table('quarantine_sites')->get();
        $justifications = DB::table('covid_justifications')->get();
        $counties = DB::table('countys')->get();
        $subcounties = DB::table('districts')->get();
        $results = DB::table('results')->get();
        $data = compact('samples', 'myurl', 'myurl2', 'type', 'quarantine_sites', 'justifications', 'facility', 'quarantine_site_id', 'counties', 'subcounties', 'results');
        if($type == 3) $data['labs'] = DB::table('labs')->get();
        return view('tables.covidsamples', $data);
    }

    public function sample_search(Request $request)
    {
        $user = auth()->user();

        $type = $request->input('type', 1);
        $submit_type = $request->input('submit_type');
        if($submit_type == 'excel') return $this->download_excel($request);
        if($submit_type == 'email') return $this->email_multiple($request);
        if($submit_type == 'multiple_results') return $this->multiple_results($request);
        $to_print = $request->input('to_print');
        $date_start = $request->input('date_start', 0);
        if($submit_type == 'submit_date') $date_start = $request->input('filter_date', 0);
        $date_end = $request->input('date_end', 0);


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
        $user = auth()->user();
        extract($request->all());

        $type = $request->input('type', 1);

        $date_column = "covid_sample_view.created_at";
        if($type == 2) $date_column = "covid_sample_view.datedispatched";

        $samples = CovidSampleView::where('repeatt', 0)
            ->when($justification_id, function($query) use ($justification_id){
                return $query->where('justification', $justification_id);
            })
            ->when($result, function($query) use ($result){
                return $query->where('result', $result);
            })
            ->when($worksheet_id, function($query) use ($worksheet_id){
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->when($county_id, function($query) use ($county_id){
                return $query->where('county_id', $county_id);
            })
            ->when($subcounty_id, function($query) use ($subcounty_id){
                return $query->where('subcounty_id', $subcounty_id);
            })
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('covid_sample_view.facility_id', $facility_id);
            })
            ->when($quarantine_site_id, function($query) use ($quarantine_site_id){
                return $query->where('quarantine_site_id', $quarantine_site_id);
            })
            ->when($identifier, function($query) use ($identifier){
                return $query->where('identifier', 'like', $identifier . '%');
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
            ->when(!$user->facility_user, function($query) use ($user){
                return $query->where('covid_sample_view.lab_id', $user->lab_id);
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
                'Quarantine Site / Facility' => $sample->quarantine_site ?? $sample->facilityname,
                'Date Collected' => $sample->my_date_format('datecollected'),
                'Date Received' => $sample->my_date_format('datereceived'),
                'Date Tested' => $sample->datetested,
                'Received Status' => $sample->get_prop_name($receivedstatus, 'receivedstatus'),
                'Result' => $sample->get_prop_name($results, 'result'),
                'Entered By' => $sample->creator->full_name,
                'Date Entered' => $sample->my_date_format('created_at'),
            ];
        }
        if(!$data) return back();
        return MiscCovid::csv_download($data);
    }

    public function email_multiple($request)
    {
        $user = auth()->user();
        extract($request->all());
        if(!$quarantine_site_id && !in_array(env('APP_LAB'), [3,5])){
            session(['toast_error' => 1, 'toast_message' => 'Kindly select a quarantine site.']);
            return back();
        }
        $quarantine_site = DB::table('quarantine_sites')->where('id', $quarantine_site_id)->first();
        if($quarantine_site && !$quarantine_site->email && !in_array(env('APP_LAB'), [1, 3, 5])){
            session(['toast_error' => 1, 'toast_message' => 'The quarantine site does not have an email address set.']);
            return back();            
        }


        $facility = Facility::find($facility_id);
        $type = 2;

        $date_column = "covid_samples.datedispatched";

        $samples = CovidSample::select('covid_samples.*')
            ->join('covid_patients', 'covid_samples.patient_id', '=', 'covid_patients.id')
            ->where('repeatt', 0)
            ->when($justification_id, function($query) use ($justification_id){
                return $query->where('justification', $justification_id);
            })
            ->when($result, function($query) use ($result){
                return $query->where('result', $result);
            })
            ->when($worksheet_id, function($query) use ($worksheet_id){
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->when($county_id, function($query) use ($county_id){
                return $query->where('county_id', $county_id);
            })
            ->when($subcounty_id, function($query) use ($subcounty_id){
                return $query->where('subcounty_id', $subcounty_id);
            })
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('facility_id', $facility_id);
            })
            ->when($identifier, function($query) use ($identifier){
                return $query->where('identifier', 'like', $identifier . '%');
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
            ->when(!$user->facility_user, function($query) use ($user){
                return $query->where('covid_samples.lab_id', $user->lab_id);
            })
            ->whereNotNull('datedispatched')
            ->orderBy($date_column, 'desc')
            ->get();

        if(!$samples->count()){
            session(['toast_error' => 1, 'toast_message' => 'No samples found']);
            return back(); 
        }
        $lab = \App\Lab::find(env('APP_LAB'));

        if($lab->cc_emails) $cc_array = explode(',', $lab->cc_emails);
        else{
            $cc_array = [];
        }

        $mail_array = [];
        if($quarantine_site && $quarantine_site->email) $mail_array = explode(',', $quarantine_site->email);
        else if($facility && $facility->covid_email) $mail_array = explode(',', $facility->covid_email);

        if(!$mail_array){
            Mail::to($cc_array)->send(new CovidDispatch($samples));
        }else{             
            if($quarantine_site){                
                Mail::to($mail_array)->cc($cc_array)->send(new CovidDispatch($samples, $quarantine_site));
            }else if($facility){                
                Mail::to($mail_array)->cc($cc_array)->send(new CovidDispatch($samples, $facility));
            }
            // else{
            //     Mail::to($mail_array)->send(new CovidDispatch($samples, $quarantine_site));
            // }
        }
        session(['toast_message' => 'The results have been sent to the quarantine site / facility.']);
        return back();            
    }

    public function multiple_results($request)
    {
        ini_set("memory_limit", "-1");
        $user = auth()->user();

        extract($request->all());

        $date_column = "covid_samples.datedispatched";

        $samples = CovidSample::select('covid_samples.*')
            ->join('covid_patients', 'covid_samples.patient_id', '=', 'covid_patients.id')
            ->where('repeatt', 0)
            ->when($justification_id, function($query) use ($justification_id){
                return $query->where('justification', $justification_id);
            })
            ->when($result, function($query) use ($result){
                return $query->where('result', $result);
            })
            ->when($worksheet_id, function($query) use ($worksheet_id){
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->when($county_id, function($query) use ($county_id){
                return $query->where('county_id', $county_id);
            })
            ->when($subcounty_id, function($query) use ($subcounty_id){
                return $query->where('subcounty_id', $subcounty_id);
            })
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('facility_id', $facility_id);
            })
            ->when($identifier, function($query) use ($identifier){
                return $query->where('identifier', 'like', $identifier . '%');
            })
            // ->where('identifier', 'like', 'tnz%')
            ->when($quarantine_site_id, function($query) use ($quarantine_site_id){
                return $query->where('quarantine_site_id', $quarantine_site_id);
            })            
            // ->whereNull('quarantine_site_id')
            // ->whereNull('facility_id')
            ->when($date_start, function($query) use ($date_column, $date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate($date_column, '>=', $date_start)
                    ->whereDate($date_column, '<=', $date_end);
                }
                return $query->whereDate($date_column, $date_start);
            })
            ->when(!$user->facility_user, function($query) use ($user){
                return $query->where('covid_samples.lab_id', $user->lab_id);
            })
            ->whereNotNull('datedispatched')
            ->orderBy($date_column, 'desc')
            ->get();

        if(!$samples->count()){
            session(['toast_error' => 1, 'toast_message' => 'No samples found']);
            return back();
        }

        $mpdf = new Mpdf();
        $data = Lookup::covid_form();
        $data['samples'] = $samples;
        $view_data = view('exports.mpdf_covid_samples', $data)->render();
        ini_set("pcre.backtrack_limit", "500000000");
        $mpdf->WriteHTML($view_data);
        $mpdf->Output('results.pdf', \Mpdf\Output\Destination::DOWNLOAD);

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
            ->when(true, function($query) use ($covidSample){
                if($covidSample->parentid){
                    return $query->whereRaw(" (covid_sample_view.id = {$covidSample->parentid} OR parentid = {$covidSample->parentid})");
                }else{
                    return $query->whereRaw(" (covid_sample_view.id = {$covidSample->id} OR parentid = {$covidSample->id})");
                }
            }) 
            ->when($user->facility_user, function($query) use ($user){
                return $query->whereRaw("(user_id='{$user->id}' OR covid_sample_view.facility_id='{$user->facility_id}')");
            }) 
            ->when($user->quarantine_site, function($query) use ($user){
                return $query->where('quarantine_site_id', $user->facility_id);
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

    public function cif_samples()
    {
        $samples = \App\Synch::get_covid_samples();
        return view('tables.cif_covid_samples', compact('samples'));
    }

    public function set_cif_samples(Request $request)
    {
        $samples = $request->input('sample_ids');
        if(!$samples){
            session(['toast_error' => 1, 'toast_message' => 'No samples selected.']);
            return back();            
        }
        \App\Synch::set_covid_samples($samples);
        session(['toast_message' => 'The sample have been set to come to the lab.']);
        return redirect('covid_sample');        
    }


    public function site_sample_page()
    {
        return view('forms.upload_site_samples', ['url' => 'covid_sample'])->with('pageTitle', 'Upload Facility Samples');
    }

    public function upload_site_samples(Request $request)
    {
        $file = $request->upload->path();
        // $path = $request->upload->store('public/site_samples/covid');

        $problem_rows = 0;
        $created_rows = 0;

        $handle = fopen($file, "r");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE){
            if(starts_with($data[0], ['S', 's'])) continue;
            
            $quarantine_site = null;

            $facility = Facility::locate($data[2])->first();
            if(!$facility && !is_numeric($data[2])){
                $quarantine_site = \App\QuarantineSite::where(['name' => $data[2]])->first();
            }

            $p = CovidPatient::create([
                'identifier' => $data[3],
                'national_id' => $data[19],
                'phone_no' => $data[15],
                'county' => $data[4],
                'subcounty' => $data[16],
                'ward' => $data[17],
                'residence' => $data[18],
                'facility_id' => $facility->id ?? 3475,
                'quarantine_site_id' => $quarantine_site->id ?? null,
                'patient_name' => $data[5],
                'sex' => $data[7],
                'justification' => $data[8],
            ]);

            $s = CovidSample::create([
                'patient_id' => $p->id,
                'site_entry' => 1,
                'age' => $data[6],
                'test_type' => $data[9],
                'sample_type' => $data[10],
                'datecollected' => $data[11],
                'datereceived' => $data[12] ?? date('Y-m-d'),
                'receivedstatus' => $data[13] ?? 1,
                'received_by' => auth()->user()->id,
            ]);
            $created_rows++;
        }
        session(['toast_message' => "{$created_rows} samples have been created."]);
        return redirect('/home');        
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

    // Transfer Between Remote Labs
    public function transfer_samples_form($facility_id=null)
    {
        $samples = CovidSampleView::where('site_entry', '!=', 2)
                    ->when($facility_id, function($query) use($facility_id){
                        return $query->where('facility_id', $facility_id);
                    })
                    ->whereNull('datetested')
                    ->where(['repeatt' => 0])
                    ->where('created_at', '>', date('Y-m-d', strtotime("-3 months")))
                    ->paginate(500);

        $samples->setPath(url()->current());

        if($facility_id) $facility = \App\Facility::find($facility_id);

        $data = [
            'samples' => $samples,
            'labs' => \App\Lab::all(),
            'facility' => $facility ?? null,
            'pre' => 'covid_',
        ];

        return view('forms.transfer_samples', $data);
    }

    public function transfer_samples(Request $request)
    {
        $samples = $request->input('samples');
        $lab = $request->input('lab');
        // dd($samples);
        \App\Synch::transfer_sample('covid', $lab, $samples);
        return back();
    }


    public function transfer(Request $request)
    {
        $lab_id = $request->input('lab_id');
        $sample_ids = $request->input('sample_ids');
        if(!$lab_id){            
            session(['toast_message' => "Select a lab.", 'toast_error' => 1]);
            return back();
        }
        CovidSample::whereIn('id', $sample_ids)->update(['lab_id' => $lab_id]);         
        session(['toast_message' => "The samples have been transferred."]);
        return back();
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
        $string = "(covid_patients.facility_id='{$user->facility_id}' OR covid_samples.user_id='{$user->id}')";

        $samples = CovidSample::select('covid_samples.id')
            ->whereRaw("covid_samples.id like '" . $search . "%'")
            ->when($user->facility_user, function($query) use ($string){
                return $query->join('covid_patients', 'covid_samples.patient_id', '=', 'covid_patients.id')->whereRaw($string);
            })
            ->when($user->quarantine_site, function($query) use ($user){
                return $query->join('covid_patients', 'covid_samples.patient_id', '=', 'covid_patients.id')
                    ->where('quarantine_site_id', $user->facility_id);
            })
            ->paginate(10);

        $samples->setPath(url()->current());
        return $samples;
    }
}
