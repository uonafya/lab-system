<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\UlizaMail;
use App\Notifications\UlizaNotification;
use App\UlizaClinicalForm;
use App\UlizaClinicalVisit;
use App\UlizaAdditionalInfo;
use App\UlizaTwg;
use App\County;
use DB;
use Illuminate\Http\Request;

class UlizaClinicalFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $statuses = DB::table('uliza_case_statuses')->get();
        if(!$user) return redirect('uliza/uliza');
        $forms = UlizaClinicalForm::with(['facility', 'twg'])
        ->when(true, function($query) use ($user){
            if($user->uliza_secretariat) return $query->where('twg_id', $user->twg_id);
            if($user->uliza_reviewer) return $query->where('reviewer_id', $user->id);
        })
        ->where('draft', false)
        ->orderBy('id', 'desc')
        ->get();
        return view('uliza.tables.cases', compact('forms', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $reasons = DB::table('uliza_reasons')->where('public', 1)->get();
        $regimens = DB::table('viralregimen')->get();
        return view('uliza.clinicalform', compact('reasons', 'regimens'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        $form = null;
        if($request->input('id')) $form = UlizaClinicalForm::find($request->input('id'));
        if(!$form) $form = new UlizaClinicalForm;
        $form->fill($request->except('clinical_visits'));
        $f = $form->view_facility;
        $county = County::find($f->county_id);
        $twg = UlizaTwg::where('default_twg', 1)->first();
        $form->twg_id = $county->twg_id ?? $twg->id ?? null;
        $form->save();

        $visits = $request->input('clinical_visits');

        foreach ($visits as $key => $value) {            
            $visit = new UlizaClinicalVisit;
            if(is_array($value)) $visit->fill($value);
            else{
                $visit->fill(get_object_vars($value));
            }
            // $visit->uliza_clinical_form_id = $form->id;
            // $visit->save();
            $form->visit()->save($visit);
        }

        if($form->draft){
            Mail::to([$form->facility_email])->send(new UlizaMail($form, 'draft_mail', 'Draft Clinical Summary Form ' . $form->subject_identifier));
            // $user = \App\User::where('email', 'like', 'joel%')->first();
            // $user->facility_email = $form->facility_email;
            // $user->notify(new UlizaNotification('uliza-form/' . $form->id . '/edit'));
        }else{
            Mail::to([$form->facility_email])->send(new UlizaMail($form, 'received_clinical_form', 'Clinical Summary Form Notification ' . $form->subject_identifier));

            if($twg) Mail::to($twg->email_array)->send(new UlizaMail($form, 'new_clinical_form', 'Clinical Summary Form Notification ' . $form->subject_identifier));
        }

        return response()->json(['status' => 'ok', 'form' => $form], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UlizaClinicalForm  $ulizaClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $ulizaClinicalForm->entry_pdf(null, true);
        $ulizaClinicalForm = UlizaClinicalForm::find($id);
        // \App\UlizaPage::entry_pdf($ulizaClinicalForm, null, true);
        $ulizaClinicalForm->entry_pdf(null, true);

        // $reasons = DB::table('uliza_reasons')->where('public', 1)->get();
        // $regimens = DB::table('viralregimen')->get();
        // return view('uliza.exports.clinical_form', compact('reasons', 'regimens', 'ulizaClinicalForm'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UlizaClinicalForm  $ulizaClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $reasons = DB::table('uliza_reasons')->get();
        $regimens = DB::table('viralregimen')->get();
        $ulizaClinicalForm = UlizaClinicalForm::find($id);
        return view('uliza.clinicalform', compact('reasons', 'regimens', 'ulizaClinicalForm'));      
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UlizaClinicalForm  $ulizaClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $form = UlizaClinicalForm::find($id);
        $form->fill($request->except('clinical_visits'));
        $form->save();
        return response()->json(['status' => 'ok'], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UlizaClinicalForm  $ulizaClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function destroy(UlizaClinicalForm $ulizaClinicalForm)
    {
        //
    }
}
