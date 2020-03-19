<?php

namespace App\Http\Controllers;

use App\CovidSample;
use App\CovidTravel;
use App\Lookup;
use Illuminate\Http\Request;

class CovidSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type=1, $date_start=NULL, $date_end=NULL, $facility_id=NULL, $subcounty_id=NULL, $partner_id=NULL)
    {
        // 0 - not received
        // 1 - all
        // 2 - dispatched
        $user = auth()->user();
        $date_column = "covid_samples.created_at";
        if($type == 2) $date_column = "covid_samples.datedispatched";

        $samples = CovidSample::select(['covid_samples.*', 'facilitys.name', 'u.surname', 'u.oname', 'r.surname as rsurname', 'r.oname as roname'])
            ->leftJoin('facilitys', 'facilitys.id', '=', 'covid_samples.facility_id')
            ->leftJoin('users as u', 'u.id', '=', 'covid_samples.user_id')
            ->leftJoin('users as r', 'r.id', '=', 'covid_samples.received_by')
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('covid_samples.facility_id', $facility_id);
            })
            ->when($subcounty_id, function($query) use ($subcounty_id){
                return $query->where('facilitys.district', $subcounty_id);
            })
            ->when($partner_id, function($query) use ($partner_id){
                return $query->where('facilitys.partner', $partner_id);
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
                else if($type == 2) return $query->whereNotNull('datedispatched', 1);
            })
            ->when(true, function($query) use ($type, $date_column){
                if($type == 2) return $query->orderBy($date_column, 'desc');
                else if($type == 0) return $query->orderBy($date_column, 'asc');
                else{
                    return $query->orderBy($date_column, 'desc');
                }
            })
            ->when(($user->user_type_id == 5), function($query) use ($user){
                return $query->whereRaw("(user_id='{$user->id}' OR covid_samples.facility_id='{$user->facility_id}')");
            })
            ->paginate();
        $myurl = url('/covid_sample/index/' . $type);
        $myurl2 = url('/covid_sample/index/');        
        $p = Lookup::get_partners();
        $data = array_merge($p, compact('samples', 'myurl', 'myurl2', 'index'));
        return view('tables.covidsamples', $data);
    }

    public function sample_search(Request $request)
    {
        dd($request->all());
        $type = $request->input('type', 1);
        $submit_type = $request->input('submit_type');
        $to_print = $request->input('to_print');
        $date_start = $request->input('from_date', 0);
        if($submit_type == 'submit_date') $date_start = $request->input('filter_date', 0);
        $date_end = $request->input('to_date', 0);

        if($date_start == '') $date_start = 0;
        if($date_end == '') $date_end = 0;

        $partner_id = $request->input('partner_id', 0);
        $subcounty_id = $request->input('subcounty_id', 0);
        $facility_id = $request->input('facility_id', 0);

        if($partner_id == '') $partner_id = 0;
        if($subcounty_id == '') $subcounty_id = 0;
        if($facility_id == '') $facility_id = 0;

        return redirect("covid_sample/index/{$type}/{$date_start}/{$date_end}/{$facility_id}/{$subcounty_id}/{$partner_id}");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Lookup::viralsample_form();
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
        // dd($request->all());

        $sample = new CovidSample;
        $sample->fill($request->except(['_token', 'method', 'travel']));
        $sample->calc_age();
        $sample->user_id = auth()->user()->id;
        if($sample->datereceived) $sample->received_by = auth()->user()->id;
        $sample->save();

        $travels = $request->input('travel');
        if($travels){
            $count = count($travels['travel_date']);

            for ($i=0; $i < $count; $i++) {
                $travel = new CovidTravel;
                $travel->travel_date = $travels['travel_date'][$i];
                $travel->city_visited = $travels['city_visited'][$i];
                $travel->duration_visited = $travels['duration_visited'][$i];
                $travel->sample_id = $sample->id;
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

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CovidSample  $covidSample
     * @return \Illuminate\Http\Response
     */
    public function edit(CovidSample $covidSample)
    {
        dd($covidSample);
        $data = Lookup::viralsample_form();
        $covidSample->load(['facility']);
        $data['sample'] = $covidSample;
        dd($data);
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
        $covidSample->fill($request->except(['_token', 'method', 'travel']));
        $covidSample->calc_age();
        if($sample->isDirty('datereceived') && !$sample->received_by) $sample->received_by = auth()->user()->id;
        $covidSample->pre_update();

        $travels = $request->input('travel');
        if($travels){
            $count = count($travels['travel_date']);

            for ($i=0; $i < $count; $i++) {
                if(isset($travels['travel_id'][$i])) $travel = CovidTravel::find($travels['travel_id'][$i]);
                else{
                    $travel = new CovidTravel;
                }
                $travel->travel_date = $travels['travel_date'][$i];
                $travel->city_visited = $travels['city_visited'][$i];
                $travel->duration_visited = $travels['duration_visited'][$i];
                $travel->sample_id = $covidSample->id;
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
        $covidSample->travel()->delete();
        $covidSample->delete();
        session(['toast_message' => 'The sample has been deleted.']);
        return back();
    }
}
