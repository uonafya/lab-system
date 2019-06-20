<?php

namespace App;
use Excel;
use DB;
use App\Facility;
use App\Lookup;

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class Random
{
	protected static $taqmanKits = [
		['EIDname'=>"Ampliprep, HIV-1 Qualitative Test kits HIVQCAP", 'VLname'=>"Ampliprep, HIV-1 Quantitative Test kits HIVQCAP", 'alias'=>'qualkit', 'unit'=>'48 Tests' ,'factor'=>1, 'testFactor' => ['EID'=>44,'VL'=>42]],
		['name'=>"Ampliprep Specimen Pre-Extraction Reagent", 'alias'=>'spexagent', 'unit'=>'350 Tests' ,'factor'=>0.15, 'testFactor' => 0.15],
		['name'=>"Ampliprep Input S-tube", 'alias'=>'ampinput', 'unit'=>'12 * 24' ,'factor'=>0.2, 'testFactor' => 0.2],
		['name'=>"Ampliprep SPU", 'alias'=>'ampflapless', 'unit'=>'12 * 24' ,'factor'=>0.2, 'testFactor' => 0.2],
		['name'=>"Ampliprep K-Tips", 'alias'=>'ampktips', 'unit'=>'5.1L' ,'factor'=>0.15, 'testFactor' => 0.15],
		['name'=>"Ampliprep Wash Reagent", 'alias'=>'ampwash', 'unit'=>'1.2mm, 12 * 36' ,'factor'=>0.5, 'testFactor' => 0.5],
		['name'=>"TAQMAN K-Tubes", 'alias'=>'ktubes', 'unit'=>'12 * 96Pcs' ,'factor'=>0.05, 'testFactor' => 0.05],
		['name'=>"CAP/CTM Consumable Bundles", 'alias'=>'consumables', 'unit'=>'2 * 2.5ml' ,'factor'=>0.5, 'testFactor' => 0.5]
					];
	protected static $abbottKits = [
		['EIDname'=>"ABBOTT RealTime HIV-1 Qualitative Amplification Reagent Kit", 'VLname'=>"ABBOTT RealTime HIV-1 Quantitative Amplification Reagent Kit", 'alias'=>'qualkit','factor'=>['EID'=>1,'VL'=>1], 'testFactor' => ['EID'=>94,'VL'=>93]],
		['name'=>"ABBOTT m2000rt Optical Calibration Kit", 'alias'=>'calibration','factor'=>['EID'=>0,'VL'=>0], 'testFactor' => ['EID'=>0,'VL'=>0]],
		['name'=>"ABBOTT RealTime HIV-1 Quantitative Control Kit", 'alias'=>'control', 'factor'=>['EID'=>(2*(2/24)),'VL'=>(3/24)], 'testFactor' => ['EID'=>(2*(2/24)),'VL'=>(3/24)]],
		['name'=>"Bulk mLysisDNA Buffer (for DBS processing only)", 'alias'=>'buffer','factor'=>['EID'=>1,'VL'=>1], 'testFactor' => ['EID'=>1,'VL'=>1]],
		['name'=>"ABBOTT mSample Preparation System RNA", 'alias'=>'preparation','factor'=>['EID'=>1,'VL'=>1], 'testFactor' => ['EID'=>1,'VL'=>1]],
		['name'=>"ABBOTT Optical Adhesive Covers", 'alias'=>'adhesive','factor'=>['EID'=>(2/100),'VL'=>(1/100)], 'testFactor' => ['EID'=>(2/100),'VL'=>(1/100)]],
		['name'=>"ABBOTT 96-Deep-Well Plate", 'alias'=>'deepplate','factor'=>['EID'=>(2*(2/4)),'VL'=>(3/4)], 'testFactor' => ['EID'=>(2*(2/4)),'VL'=>(3/4)]],
		['name'=>"Saarstet Master Mix Tube", 'alias'=>'mixtube','factor'=>['EID'=>(2*(1/25)),'VL'=>(1/25)], 'testFactor' => ['EID'=>(2*(1/25)),'VL'=>(1/25)]],
		['name'=>"Saarstet 5ml Reaction Vessels", 'alias'=>'reactionvessels','factor'=>['EID'=>(192/500),'VL'=>(192/500)], 'testFactor' => ['EID'=>(192/500),'VL'=>(192/500)]],
		['name'=>"200mL Reagent Vessels", 'alias'=>'reagent','factor'=>['EID'=>(2*(5/6)),'VL'=>(6/6)], 'testFactor' => ['EID'=>(2*(5/6)),'VL'=>(6/6)]],
		['name'=>"ABBOTT 96-Well Optical Reaction Plate", 'alias'=>'reactionplate','factor'=>['EID'=>(192/500),'VL'=>(1/20)], 'testFactor' => ['EID'=>(192/500),'VL'=>(1/20)]],
		['name'=>"1000 uL Eppendorf (Tecan) Disposable Tips (for 1000 tests)", 'alias'=>'1000disposable','factor'=>['EID'=>(2*(421/192)),'VL'=>(841/192)], 'testFactor' => ['EID'=>(2*(421/192)),'VL'=>(841/192)]],
		['name'=>"200 ML Eppendorf (Tecan) Disposable Tips", 'alias'=>'200disposable','factor'=>['EID'=>(2*(48/192)),'VL'=>(96/192)], 'testFactor' => ['EID'=>(2*(48/192)),'VL'=>(96/192)]]
					];
	public static function site_entry_samples($type)
	{
		$classes = \App\Synch::$synch_arrays[$type];

		$sample_class = $classes['sampleview_class'];
		$table = 'samples_view';
		if($type == 'vl') $table = 'viralsamples_view';

		$data = $sample_class::join('users', 'users.id', '=', "{$table}.user_id")
			->join('view_facilitys', 'view_facilitys.id', '=', "users.facility_id")
			->selectRaw("view_facilitys.facilitycode AS `MFL Code`, county AS `County`, Subcounty AS `Subcounty`, view_facilitys.name AS `Facility`, COUNT(DISTINCT {$table}.facility_id) AS `Facilities Supported`,  COUNT({$table}.id) AS `Samples Entered` ")
			->where(['site_entry' => 1, 'parentid' => 0, 'user_type_id' => 5, ])
			->groupBy("{$table}.user_id")
			->get();

		$file = $type . '_facilities_doing_remote_entry';

		$rows = [];

		foreach ($data as $key => $value) {
			$rows[] = $value->toArray();
			// dd($value->toArray());
		}

		Excel::create($file, function($excel) use($rows){
			$excel->sheet('Sheetname', function($sheet) use($rows) {
				$sheet->fromArray($rows);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		Mail::to(['joelkith@gmail.com'])->send(new TestMail($data));
	}


    public static function delete_site_entry()
    {
        $min_time = date('Y-m-d', strtotime("-28 days"));

        $batches = \App\Viralbatch::selectRaw("viralbatches.*, COUNT(viralsamples.id) AS sample_count ")
            ->leftJoin('viralsamples', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->whereNull('receivedstatus')
            ->where('site_entry', 1)
            ->where('viralbatches.created_at', '<', $min_time)
            ->groupBy('viralbatches.id')
            ->get();

        foreach ($batches as $key => $batch) {
        	$sample = \App\Viralsample::where('batch_id', $batch->id)->whereNotNull('receivedstatus')->first();
        	if(!$sample){
        		$batch->datereceived = null;
        		$batch->save();
        		$batch->batch_delete();
        	}
        }
    }

    public static function enter_samples()
    {
    	$file = public_path('machakos.csv');
    	$handle = fopen($file, "r");
    	while (($row = fgetcsv($handle, 1000, ",")) !== FALSE){

            $facility = Facility::locate($row[3])->get()->first();
            if(!$facility) continue;
            $datecollected = Lookup::other_date($row[8]);
            $datereceived = Lookup::other_date($row[15]);
            if(!$datereceived) $datereceived = date('Y-m-d');
            $existing = \App\ViralsampleView::existing(['facility_id' => $facility->id, 'patient' => $row[1], 'datecollected' => $datecollected])->first();

            if($existing){
                $sampletype = (int) $row[7];
                if(in_array($existing->sampletype, [3, 4]) && in_array($sampletype, [1,2,5])){
                    $s = \App\Viralsample::find($existing->id);
                    $s->delete();
                }
                else{
                	if($existing->received_by != 7939){
	                    $b = \App\Viralbatch::find($existing->batch_id);
	                    $b->received_by = 7939;
	                    $b->save();
                	}
                    continue;                        
                }
            }

            $site_entry = Lookup::get_site_entry($row[14]);

            $batch = \App\Viralbatch::withCount(['sample'])
                                    ->where('received_by', 7939)
                                    ->where('datereceived', $datereceived)
                                    ->where('input_complete', 0)
                                    ->where('site_entry', $site_entry)
                                    ->where('facility_id', $facility->id)
                                    ->get()->first();

            if($batch){
                if($batch->sample_count > 9){
                    unset($batch->sample_count);
                    $batch->full_batch();
                    $batch = null;
                }
            }

            if(!$batch){
                $batch = new \App\Viralbatch;
                $batch->user_id = $facility->facility_user->id;
                $batch->facility_id = $facility->id;
                $batch->received_by = 7939;
                $batch->time_received = date('Y-m-d H:i:s');
                $batch->lab_id = env('APP_LAB');
                $batch->datereceived = $datereceived;
                $batch->site_entry = $site_entry;
                $batch->save();
            }

            $patient = \App\Viralpatient::existing($facility->id, $row[1])->get()->first();
            if(!$patient){
                $patient = new \App\Viralpatient;
            }
            $dob = Lookup::other_date($row[5]);
            if (!$dob) {
                if(strlen($row[5]) == 4) $dob = $row[5] . '-01-01';
            }
            if($dob) $patient->dob = $dob;            
            $patient->facility_id = $facility->id;
            $patient->patient = $row[1];
            $patient->sex = Lookup::get_gender($row[4]);
            $patient->initiation_date = Lookup::other_date($row[9]);
            if(!$patient->dob && $row[6]) $patient->dob = Lookup::calculate_dob($datecollected, $row[6]); 
            $patient->pre_update();

            $sample = new \App\Viralsample;
            $sample->batch_id = $batch->id;
            $sample->patient_id = $patient->id;
            $sample->datecollected = $datecollected;
            $sample->age = $row[6];
            if(!$sample->age) $sample->age = Lookup::calculate_viralage($datecollected, $patient->dob);
            $sample->prophylaxis = Lookup::viral_regimen($row[10]);
            $sample->dateinitiatedonregimen = Lookup::other_date($row[11]);
            $sample->justification = Lookup::justification($row[12]);
            $sample->sampletype = (int) $row[7];
            if($sample->sampletype == 5) $sample->sampletype = 1;
            $sample->pmtct = $row[13];
            $sample->receivedstatus = $row[16];
            if(is_numeric($row[17])) $sample->rejectedreason = $row[17];
            $sample->save();

    	}
    }

    public static function oldest($type)
    {
		$sampleview_class = \App\Synch::$synch_arrays[$type]['sampleview_class'];

		$m = $sampleview_class::selectRaw('MIN(datereceived) as mindate')
								->where('datereceived', '>', date('Y-m-d', strtotime("-1 year")))
								->whereNull('worksheet_id')
								->whereNull('approvedby')
								->whereNull('datedispatched')
								// ->where('receivedstatus', '!=', 2)
								->where('site_entry', '!=', 2)
								->whereRaw("(result is null or result=0)")
								->where(['receivedstatus' => 1, 'flag' => 1, 'input_complete' => 1, 'lab_id' => env('APP_LAB', null)])
								->get()->first();
		return $m;
    }


	public static function switch_amrs()
	{
		ini_set("memory_limit", "-1");
		$samples = \App\ViralsampleView::whereBetween('datereceived', ['2018-09-01', '2018-10-24'])->whereRaw("(amrs_location is not null and amrs_location != 0)")->get();
		foreach ($samples as $sample) {
			$s = \App\Viralsample::find($sample->id);
			$s->amrs_location = Lookup::get_mrslocation($s->amrs_location);
			$s->pre_update();
		}
	}

	public static function facilitys()
	{
		self::alter_facilitys();
		self::poc_sites();
		self::mlab_sites();
	}

	public static function alter_facilitys()
	{
		DB::statement('ALTER TABLE facilitys ADD COLUMN `poc` TINYINT UNSIGNED DEFAULT 0 after latitude;');
		Facility::where('id', '>', 0)->update(['smsprinter' => 0]);
	}

	public static function correct_faces_data()
	{
		ini_set("memory_limit", "-1");
        config(['excel.import.heading' => true]);
		$path = public_path('vl_records.csv');

		$data = Excel::load($path, function($reader){})->get();

		foreach ($data as $row) {
			$s = \App\Viralsample::find($row->labid);
			$p = $s->patient;
			if($p->patient != $row->patientid) continue;
			$p->sex = 1;
			$p->pre_update();

			$s->pmtct = 3;
			$s->pre_update();
		}
	}

	public static function poc_sites()
	{
		ini_set("memory_limit", "-1");
        config(['excel.import.heading' => true]);
		$path = public_path('poc_hubs_list.csv');
		$data = Excel::load($path, function($reader){

		})->get();

		foreach ($data as $row) {
			Facility::where(['facilitycode' => $row->code])->update(['poc' => 1]);
		}
	} 

	public static function mlab_sites()
	{
		ini_set("memory_limit", "-1");
        config(['excel.import.heading' => true]);
		$path = public_path('mlab_facilities.csv');
		$data = Excel::load($path, function($reader){

		})->get();

		foreach ($data as $row) {
			Facility::where(['facilitycode' => $row->code])->update(['smsprinter' => 1]);
		}
	} 

	public static function locations()
	{
		$locations = '
			[
				{
					"location_id" : 1,
					"name" : "MTRH Module 1",
					"description" : "Moi Teaching and Referral Hospital - Module 1"
				},
				{
					"location_id" : 2,
					"name" : "Mosoriot",
					"description" : "Mosoriot Outpatient Center"
				},
				{
					"location_id" : 3,
					"name" : "Turbo",
					"description" : "Turbo heath center Clinic"
				},
				{
					"location_id" : 4,
					"name" : "Burnt Forest",
					"description" : "Burnt Forest RHDC Clinic"
				},
				{
					"location_id" : 5,
					"name" : "Amukura",
					"description" : "Amukura Health Center"
				},
				{
					"location_id" : 6,
					"name" : "Naitiri",
					"description" : "Naitiri Health center"
				},
				{
					"location_id" : 7,
					"name" : "Chulaimbo",
					"description" : "Chulaimbo Sub-district hospital (Clinic)"
				},
				{
					"location_id" : 8,
					"name" : "Webuye",
					"description" : "Webuye Hospital"
				},
				{
					"location_id" : 9,
					"name" : "Mt. Elgon",
					"description" : "Mount Elgon Clinic (Kapsokwony)"
				},
				{
					"location_id" : 10,
					"name" : "Kapenguria",
					"description" : "Kapenguria Clinic"
				},
				{
					"location_id" : 11,
					"name" : "Kitale",
					"description" : "Kitale Clinic"
				},
				{
					"location_id" : 12,
					"name" : "Teso",
					"description" : "Teso Clinic"
				},
				{
					"location_id" : 13,
					"name" : "MTRH Module 2",
					"description" : "Moi Teaching and Referral Hospital - Module 2"
				},
				{
					"location_id" : 14,
					"name" : "MTRH Module 3",
					"description" : "Moi Teaching and Referral Hospital - Module 3"
				},
				{
					"location_id" : 15,
					"name" : "MTRH Module 4",
					"description" : "Moi Teaching and Referral Hospital - Module 4"
				},
				{
					"location_id" : 16,
					"name" : "Unknown",
					"description" : "Unknown Location"
				},
				{
					"location_id" : 17,
					"name" : "Iten",
					"description" : "Iten Clinic"
				},
				{
					"location_id" : 18,
					"name" : "Kabarnet",
					"description" : "Kabarnet Clinic"
				},
				{
					"location_id" : 19,
					"name" : "Busia",
					"description" : "Busia Clinic"
				},
				{
					"location_id" : 20,
					"name" : "Port Victoria",
					"description" : "Port Victoria AMPATH clinic"
				},
				{
					"location_id" : 21,
					"name" : "Non AMPATH Site",
					"description" : "All clinical locations outside the AMPATH system."
				},
				{
					"location_id" : 22,
					"name" : "None",
					"description" : "No location."
				},
				{
					"location_id" : 23,
					"name" : "Khunyangu",
					"description" : "Khunyangu District Hospital"
				},
				{
					"location_id" : 24,
					"name" : "Chulaimbo Module 1",
					"description" : "Chulaimbo Adult Clinic"
				},
				{
					"location_id" : 25,
					"name" : "Chulaimbo Module 2",
					"description" : "Chulaimbo Pediatric Clinic"
				},
				{
					"location_id" : 26,
					"name" : "Busia Module 1",
					"description" : "Busia Module 1"
				},
				{
					"location_id" : 27,
					"name" : "Busia Module 2",
					"description" : "Busia Module 2"
				},
				{
					"location_id" : 28,
					"name" : "Ziwa",
					"description" : "Ziwa Clinic"
				},
				{
					"location_id" : 30,
					"name" : "Anderson",
					"description" : "Anderson Clinic"
				},
				{
					"location_id" : 31,
					"name" : "Uasin Gishu District Hospital",
					"description" : "Uasin Gishu District Hospital (DH)"
				},
				{
					"location_id" : 32,
					"name" : "Eldoret Catholic Church(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 33,
					"name" : "Eldoret Police Station(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 34,
					"name" : "Majengo (Our Lady) Church(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 35,
					"name" : "Turbo Police Station",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 36,
					"name" : "Nakuru(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 37,
					"name" : "Nairobi(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 38,
					"name" : "Eldoret Showground(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 39,
					"name" : "Yamumbi (IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 40,
					"name" : "Matharu Center(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 41,
					"name" : "Munyaka PCEA Church(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 42,
					"name" : "Maji Mazuri(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 43,
					"name" : "Kamara(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 44,
					"name" : "Eldamaravine Police Station(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 45,
					"name" : "Moisbridge(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 46,
					"name" : "Langas police station(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 47,
					"name" : "Timboroa Police Station",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 48,
					"name" : "Bishop Muge(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 49,
					"name" : "Kipkenyo(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 50,
					"name" : "Endebes(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 51,
					"name" : "Kachibora(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 52,
					"name" : "Cherangany(IDP)",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 53,
					"name" : "Nzioa Scheme",
					"description" : "Internally Displaced AMPATH Patients"
				},
				{
					"location_id" : 54,
					"name" : "Plateau Mission Hospital",
					"description" : "Burnt Forest Satellite Clinic"
				},
				{
					"location_id" : 55,
					"name" : "Bumala A",
					"description" : "Bumala \"A\" Health Center(Busia Satellite Clinic)"
				},
				{
					"location_id" : 56,
					"name" : "Eldoret Prison",
					"description" : "Satellite Clinic of MTRH Module 3"
				},
				{
					"location_id" : 57,
					"name" : "Kitale Prison",
					"description" : "Satellite Clinic of Kitale"
				},
				{
					"location_id" : 58,
					"name" : "Ngeria Prison",
					"description" : "Satellite Clinic of MTRH Module 3"
				},
				{
					"location_id" : 59,
					"name" : "Mautuma",
					"description" : "Satellite Clinic of Turbo"
				},
				{
					"location_id" : 60,
					"name" : "Chepsaita",
					"description" : "Chepsaita Dispensary(Turbo Satellite Clinic)"
				},
				{
					"location_id" : 61,
					"name" : "Kaptagat",
					"description" : "Satellite Clinic of Burnt Forest"
				},
				{
					"location_id" : 62,
					"name" : "Kesses",
					"description" : "Satellite Clinic of Burnt Forest"
				},
				{
					"location_id" : 63,
					"name" : "Lukolis",
					"description" : "Lukolis Dispensary(Amukura satellite clinic)"
				},
				{
					"location_id" : 64,
					"name" : "Bokoli",
					"description" : "Bokoli Hospital(Webuye satellite clinic)"
				},
				{
					"location_id" : 65,
					"name" : "Angurai",
					"description" : "Angurai Health Center(Teso satellite clinic)"
				},
				{
					"location_id" : 66,
					"name" : "Cheptais",
					"description" : "Cheptais Sub-District Hospital(Mt. Elgon Satellite Clinic)"
				},
				{
					"location_id" : 67,
					"name" : "Cheskaki",
					"description" : "Mt. Elgon Satellite Clinic"
				},
				{
					"location_id" : 68,
					"name" : "Marigat",
					"description" : "Satellite Clinic of Kabarnet"
				},
				{
					"location_id" : 69,
					"name" : "Huruma SDH",
					"description" : "Satellite Clinic of Uasin Gishu District Hospital"
				},
				{
					"location_id" : 70,
					"name" : "Pioneer Sub-District Hospital",
					"description" : "Satellite clinic for Mosoriot Health Centre"
				},
				{
					"location_id" : 71,
					"name" : "Moi\'s Bridge",
					"description" : "Moi\'s Bridge Clinic"
				},
				{
					"location_id" : 72,
					"name" : "Moi University",
					"description" : "Moi University  Main Campus clinic"
				},
				{
					"location_id" : 73,
					"name" : "Soy",
					"description" : "Soy Clinic"
				},
				{
					"location_id" : 74,
					"name" : "Mihuu",
					"description" : "Mihuu Dispensary(Webuye satellite clinic)"
				},
				{
					"location_id" : 75,
					"name" : "Sinoko",
					"description" : "Sinoko Dispensary(Bungoma East)"
				},
				{
					"location_id" : 76,
					"name" : "Milo",
					"description" : "Milo Health Center (Satellite clinic to Webuye)"
				},
				{
					"location_id" : 77,
					"name" : "Moiben",
					"description" : "Satellite Clinic of Ziwa"
				},
				{
					"location_id" : 78,
					"name" : "Mukhobola",
					"description" : "Mukhobola Clinic"
				},
				{
					"location_id" : 79,
					"name" : "Nambale",
					"description" : "Nambale Clinic"
				},
				{
					"location_id" : 80,
					"name" : "MOI BARRACKS",
					"description" : "Satellite Clinic of Module 3"
				},
				{
					"location_id" : 81,
					"name" : "Busia Prison",
					"description" : "Busia Satellite Clinic"
				},
				{
					"location_id" : 82,
					"name" : "Saboti",
					"description" : "Kitale satellite clinic"
				},
				{
					"location_id" : 83,
					"name" : "Bumala B",
					"description" : "Bumala \"B\" Health Center (Khunyangu Satellite clinic)"
				},
				{
					"location_id" : 84,
					"name" : "Moi Teaching and Referral Hospital",
					"description" : "Primary Health Care Clinic Location"
				},
				{
					"location_id" : 85,
					"name" : "Makutano",
					"description" : "Satellite Clinic Site for Naitiri"
				},
				{
					"location_id" : 86,
					"name" : "Kaptama ( Friends) Dispensary",
					"description" : "Satellite clinic of Mount Elgon(Kapsokwony)"
				},
				{
					"location_id" : 87,
					"name" : "Sio Port",
					"description" : "Sio port"
				},
				{
					"location_id" : 88,
					"name" : "Tulwet",
					"description" : "Satellite clinic of Kitale"
				},
				{
					"location_id" : 89,
					"name" : "Kopsiro",
					"description" : "Satellite Clinic of Mt. elgon."
				},
				{
					"location_id" : 90,
					"name" : "Changara",
					"description" : "Teso Satellte Clinic"
				},
				{
					"location_id" : 91,
					"name" : "Malaba",
					"description" : "Satellite clinic of Teso"
				},
				{
					"location_id" : 92,
					"name" : "Amase",
					"description" : "Amase Dispensary(Amukura satellite clinic)"
				},
				{
					"location_id" : 93,
					"name" : "Obekai",
					"description" : "Obekai Dispensary(Amukura satellite clinic)"
				},
				{
					"location_id" : 94,
					"name" : "Tambach",
					"description" : "Satellite Clinic to Iten"
				},
				{
					"location_id" : 95,
					"name" : "Tenges",
					"description" : "Satellite clinic to Kabarnet."
				},
				{
					"location_id" : 96,
					"name" : "Kibisi",
					"description" : "Satellite clinic to Naitiri"
				},
				{
					"location_id" : 97,
					"name" : "Sango",
					"description" : "Satellite clinic to Naitiri."
				},
				{
					"location_id" : 98,
					"name" : "AIC Diguna Royal Toto Children\'s Home,Ngechek",
					"description" : "Mosoriot satellite clinic"
				},
				{
					"location_id" : 99,
					"name" : "Lupida",
					"description" : "Nambale Satellite Clinic"
				},
				{
					"location_id" : 100,
					"name" : "Osieko",
					"description" : "A satellite to Port Victoria"
				},
				{
					"location_id" : 101,
					"name" : "Room 7",
					"description" : "Casualty"
				},
				{
					"location_id" : 102,
					"name" : "Elgeyo Border",
					"description" : "These is a health centre"
				},
				{
					"location_id" : 103,
					"name" : "Riat",
					"description" : "This is a dispensary and its Chulaimbo\'s Satellites"
				},
				{
					"location_id" : 104,
					"name" : "Sunga",
					"description" : "This is a dispensary and its Chulaimbo\'s Satellites"
				},
				{
					"location_id" : 105,
					"name" : "Siriba",
					"description" : "This is a dispensary and its Chulaimbo\'s Satellites"
				},
				{
					"location_id" : 106,
					"name" : "Kamolo",
					"description" : "satelite at Kamolo Dispensary,to be run by Teso, AMPATH clinic"
				},
				{
					"location_id" : 107,
					"name" : "Kapteren Health Center",
					"description" : "A satellite of Iten Clinic"
				},
				{
					"location_id" : 108,
					"name" : "Madende Health Center",
					"description" : "A satellite of Nambale"
				},
				{
					"location_id" : 109,
					"name" : "Rai Plywoods",
					"description" : "Satellite clinic to UGDH"
				},
				{
					"location_id" : 110,
					"name" : "Mogoget",
					"description" : "Dispensary in Kosirai Division"
				},
				{
					"location_id" : 111,
					"name" : "Birbiriet",
					"description" : "Dispensary in Kosirai Division"
				},
				{
					"location_id" : 112,
					"name" : "Itigo",
					"description" : "Dispensary in Kosirai Division"
				},
				{
					"location_id" : 113,
					"name" : "Lelmokwo",
					"description" : "Dispensary in Kosirai Division"
				},
				{
					"location_id" : 114,
					"name" : "Kokwet",
					"description" : "Dispensary in Kosirai Division"
				},
				{
					"location_id" : 115,
					"name" : "Ngechek",
					"description" : "Dispensary in Kosirai Division"
				},
				{
					"location_id" : 116,
					"name" : "Cheramei",
					"description" : "Dispensary in Turbo Division"
				},
				{
					"location_id" : 117,
					"name" : "Murgusi",
					"description" : "Dispensary in Turbo Division"
				},
				{
					"location_id" : 118,
					"name" : "Cheplaskei",
					"description" : "Dispensary in Turbo Division"
				},
				{
					"location_id" : 119,
					"name" : "Sigot",
					"description" : "Dispensary in Turbo Division"
				},
				{
					"location_id" : 120,
					"name" : "Sugoi A",
					"description" : "Dispensary in Turbo Division"
				},
				{
					"location_id" : 121,
					"name" : "Sugoi B",
					"description" : "Dispensary in Turbo Division"
				},
				{
					"location_id" : 122,
					"name" : "Chepkemel",
					"description" : "Dispensary in Turbo Division"
				},
				{
					"location_id" : 123,
					"name" : "Chepkemel",
					"description" : "Dispensary in Turbo Division"
				},
				{
					"location_id" : 124,
					"name" : "Akichelesit",
					"description" : "Dispensary in Teso Division"
				},
				{
					"location_id" : 125,
					"name" : "Aboloi",
					"description" : "Dispensary in Teso Division"
				},
				{
					"location_id" : 126,
					"name" : "Moding",
					"description" : "Dispensary in Teso Division"
				},
				{
					"location_id" : 127,
					"name" : "Sambut",
					"description" : "Sambut - Dispensary in Turbo division"
				},
				{
					"location_id" : 128,
					"name" : "Ngenyilel",
					"description" : "Dispensary in Turbo division"
				},
				{
					"location_id" : 129,
					"name" : "Sosiani",
					"description" : "Health Centre in Turbo division"
				},
				{
					"location_id" : 130,
					"name" : "Matayos Health Centre",
					"description" : "New site from Aphia"
				},
				{
					"location_id" : 131,
					"name" : "Chebaiywa",
					"description" : "Used by CDM team and their forms being entered to AMRS"
				},
				{
					"location_id" : 132,
					"name" : "Kapsara Sub-District Hospital",
					"description" : "New location in Kitale"
				},
				{
					"location_id" : 133,
					"name" : "Chepterit",
					"description" : "A dispensary in Mosoriot Division"
				},
				{
					"location_id" : 134,
					"name" : "Kapyemit",
					"description" : "A dispensary in Turbo division and Uasin Gishu county"
				},
				{
					"location_id" : 135,
					"name" : "Kaborom",
					"description" : "Dispensary - a satellite of Mt Elgon."
				},
				{
					"location_id" : 136,
					"name" : "Murgor Hills",
					"description" : "A dispensary in Turbo"
				},
				{
					"location_id" : 137,
					"name" : "Osorongai",
					"description" : "A dispensary in Turbo"
				},
				{
					"location_id" : 138,
					"name" : "Family Health Care Options Kenya - Eldoret",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 139,
					"name" : "Elgon View Hospital",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 140,
					"name" : "Cedar Clinical Associates",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 141,
					"name" : "Glory Health Centre and Chemists",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 142,
					"name" : "Amani Health Centre",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 143,
					"name" : "Gynocare Health Centre",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 144,
					"name" : "St. Marys Health Centre - Kapsoya",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 145,
					"name" : "SOS Medical Centre - Eldoret",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 146,
					"name" : "Imani Hospital",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 147,
					"name" : "Fountain Health Centre",
					"description" : "Private Hospital in Eldoret"
				},
				{
					"location_id" : 148,
					"name" : "St. Luke\'s",
					"description" : "A Private Hospital in Eldoret"
				},
				{
					"location_id" : 149,
					"name" : "Eldoret Hospital",
					"description" : "A private hospital in Eldoret."
				},
				{
					"location_id" : 150,
					"name" : "Sisenye Dispensary",
					"description" : "Dispensary in Bunyala sub-county"
				},
				{
					"location_id" : 151,
					"name" : "Rukala Dispensary",
					"description" : "Dispensary in Bunyala Sub-County."
				},
				{
					"location_id" : 152,
					"name" : "Budalangi Dispensary",
					"description" : "A dispensary in Bunyala Sub-county"
				},
				{
					"location_id" : 153,
					"name" : "Reale Hospital",
					"description" : "Private Hospital in Eldoret\r\nunder the Private Sector Engagement program\r\n(formally PPP)"
				},
				{
					"location_id" : 154,
					"name" : "Sokyot",
					"description" : "A community based in Turbo"
				},
				{
					"location_id" : 155,
					"name" : "Turbo/Kaptebee",
					"description" : "A community in Turbo"
				},
				{
					"location_id" : 156,
					"name" : "Ngechek",
					"description" : "A community In Kosirai Division"
				},
				{
					"location_id" : 157,
					"name" : "Tuigoin",
					"description" : "A community Unit in Turbo Division"
				},
				{
					"location_id" : 158,
					"name" : "Leseru",
					"description" : "A community Unit in Turbo Division"
				},
				{
					"location_id" : 159,
					"name" : "Kosirai",
					"description" : "A community Unit in Kosirai Division"
				},
				{
					"location_id" : 160,
					"name" : "Mutwot",
					"description" : "A Community unit in Kosirai Division"
				},
				{
					"location_id" : 161,
					"name" : "Laikipia",
					"description" : "An amrs site in Laikipia County"
				},
				{
					"location_id" : 162,
					"name" : "Sirimba Mission Hospital",
					"description" : "A health Facility in Busia County"
				},
				{
					"location_id" : 163,
					"name" : "Nasewa Health Centre",
					"description" : "A Health Facility in Nasewa"
				},
				{
					"location_id" : 164,
					"name" : "Mabunge",
					"description" : "A community unit in Nasewa"
				},
				{
					"location_id" : 165,
					"name" : "Buyama",
					"description" : "A community unit in Nasewa"
				},
				{
					"location_id" : 166,
					"name" : "Lung\'a",
					"description" : "A community unit in Nasewa"
				},
				{
					"location_id" : 167,
					"name" : "Nasewa",
					"description" : "A community unit in Nasewa"
				},
				{
					"location_id" : 168,
					"name" : "Sikarira Dispensary",
					"description" : "A health facility in Sikarira"
				},
				{
					"location_id" : 169,
					"name" : "Bulwani",
					"description" : "A Community Unit in Bwaliro"
				},
				{
					"location_id" : 170,
					"name" : "Kanjala",
					"description" : "A community unit in Sikarira"
				},
				{
					"location_id" : 171,
					"name" : "Sirimba Mission Hospital",
					"description" : "A health Facility in Busia County"
				},
				{
					"location_id" : 172,
					"name" : "Ruambwa",
					"description" : "A community unit in Sirimba"
				},
				{
					"location_id" : 173,
					"name" : "Ikonzo Dispensary",
					"description" : "A Health Facility in Busia"
				},
				{
					"location_id" : 174,
					"name" : "Namwitsula",
					"description" : "A community unit in Ikonzo"
				},
				{
					"location_id" : 175,
					"name" : "Ikonzo",
					"description" : "A community Unit in Ikonzo"
				},
				{
					"location_id" : 176,
					"name" : "West Clinic Health Centre",
					"description" : "A health facility in Uasin Gishu"
				},
				{
					"location_id" : 177,
					"name" : "Kibulgeng",
					"description" : "A community facility in Uasin Gishu"
				},
				{
					"location_id" : 178,
					"name" : "Bujumba Dispensary",
					"description" : "Is a health Facility in Bujumba"
				},
				{
					"location_id" : 179,
					"name" : "Bujumba",
					"description" : "Is a community Facility in Bujumba"
				},
				{
					"location_id" : 183,
					"name" : "Ikonzo Dispensary",
					"description" : "A dispensary in Busia"
				},
				{
					"location_id" : 184,
					"name" : "Ikonzo Dispensary",
					"description" : "A dispensary in Busia"
				},
				{
					"location_id" : 185,
					"name" : "Sikarira",
					"description" : "A community unit in Ikonzo"
				},
				{
					"location_id" : 186,
					"name" : "MTRH Memorial Hospital",
					"description" : "PPP Clinic"
				},
				{
					"location_id" : 187,
					"name" : "Chep\'ngoror Dispensary",
					"description" : "A dispensary in Burnt Forest"
				},
				{
					"location_id" : 188,
					"name" : "Matunda Health Centre",
					"description" : "A community unit in Matunda"
				},
				{
					"location_id" : 189,
					"name" : "Endebes Health Centre",
					"description" : "A Health Centre in Trans nzoia"
				},
				{
					"location_id" : 190,
					"name" : "Kwanza Health Centre",
					"description" : "A Health Facility in Trans Nzoia"
				},
				{
					"location_id" : 191,
					"name" : "Anderson",
					"description" : "A health centre in transzoia"
				},
				{
					"location_id" : 192,
					"name" : "Kapsoya Health Centre",
					"description" : "A health centre in Kapsoya"
				},
				{
					"location_id" : 193,
					"name" : "Sister Freda Medical Centre",
					"description" : "A Health facility in Trans Nzoia"
				},
				{
					"location_id" : 194,
					"name" : "St. Ladislaus Dispensary",
					"description" : "A health facility in Uasin Gishu County"
				},
				{
					"location_id" : 195,
					"name" : "Location Test",
					"description" : "This is a test location for POC Testers."
				},
				{
					"location_id" : 196,
					"name" : "Mediheal Hospital",
					"description" : "A ppp Clinic in Eldoret"
				},
				{
					"location_id" : 197,
					"name" : "MTRH MCH",
					"description" : "Used to collect PMTCT data."
				},
				{
					"location_id" : 198,
					"name" : "MTRH Adolescent Clinic",
					"description" : "Moi Teaching and Referral Hospital Adolescent Clinic."
				},
				{
					"location_id" : 199,
					"name" : "MTRH Nyayo Ward",
					"description" : "MTRH clinic Nyayo Ward"
				},
				{
					"location_id" : 200,
					"name" : "MTRH Mother & Baby Ward",
					"description" : "MTRH Mother & Baby Ward"
				},
				{
					"location_id" : 201,
					"name" : "MTRH Pediatric Ward",
					"description" : "MTRH Pediatric Ward"
				},
				{
					"location_id" : 202,
					"name" : "MTRH Other",
					"description" : "MTRH Other"
				},
				{
					"location_id" : 203,
					"name" : "Langas",
					"description" : "Facility"
				},
				{
					"location_id" : 204,
					"name" : "MTRH Oncology",
					"description" : "Moi Teaching and Referral Hospital - \r\nOncology"
				},
				{
					"location_id" : 205,
					"name" : "Busagwa Dispensary",
					"description" : "Busagwa Dispensary"
				},
				{
					"location_id" : 206,
					"name" : "MTRH ACTG",
					"description" : "MTRH ACTG"
				},
				{
					"location_id" : 207,
					"name" : "MTRH-Oncology",
					"description" : "Handles patients screened and treated with breast and cervical cancer"
				},
				{
					"location_id" : 208,
					"name" : "Huruma MCH",
					"description" : "Huruma MCH"
				},
				{
					"location_id" : 209,
					"name" : "Kakamega",
					"description" : "Is a Kakamega County Referral hospital"
				},
				{
					"location_id" : 210,
					"name" : "Homabay",
					"description" : "Oncology site"
				},
				{
					"location_id" : 211,
					"name" : "Alphima Medical Clinic",
					"description" : "A Private Hospital in Eldoret"
				},
				{
					"location_id" : 212,
					"name" : "Jaramogi Oginga Odinga TRH",
					"description" : "Jaramogi Oginga Odinga Training and Referral Hospital."
				},
				{
					"location_id" : 213,
					"name" : "Bomet",
					"description" : "Oncology clinic at Bomet"
				},
				{
					"location_id" : 214,
					"name" : "Kapenguria County Referral Hospital",
					"description" : "Referral hospital in Kapenguria."
				},
				{
					"location_id" : 215,
					"name" : "Hamisi Sub County Hospital",
					"description" : "A sub county hospital in Hamisi"
				},
				{
					"location_id" : 216,
					"name" : "BUTERE",
					"description" : "An Oncology Clinic"
				},
				{
					"location_id" : 217,
					"name" : "Turbo CCC",
					"description" : "A CDM comprehensive Care center"
				},
				{
					"location_id" : 218,
					"name" : "Huruma CCC",
					"description" : "A CDM Comprehensive Care center"
				},
				{
					"location_id" : 219,
					"name" : "St. Elizabeth Lwak Mission Health center",
					"description" : "A health Center in Siaya County"
				},
				{
					"location_id" : 220,
					"name" : "Madiany sub county hospital",
					"description" : "Madiany sub county in Siaya County- Oncology study"
				},
				{
					"location_id" : 221,
					"name" : "Bungoma County Referral Hospital",
					"description" : "A referral hospital in Bungoma county"
				},
				{
					"location_id" : 222,
					"name" : "Nyahururu District Hospital",
					"description" : "A district Hospital in Laikipia County"
				},
				{
					"location_id" : 223,
					"name" : "MTRH-TB",
					"description" : "TB Clinic at MTRH"
				},
				{
					"location_id" : 224,
					"name" : "Chemundu Dispensary",
					"description" : "A dispensary in Nandi County."
				},
				{
					"location_id" : 225,
					"name" : "AIC Kapsowar Mission Hospital",
					"description" : "Mission Hospital in Kapsowar."
				},
				{
					"location_id" : 226,
					"name" : "Vihiga County Referral Hospital.",
					"description" : "Referral Hospital in Vihiga county."
				},
				{
					"location_id" : 227,
					"name" : "Iten MCH",
					"description" : "An mch facility"
				},
				{
					"location_id" : 228,
					"name" : "Webuye Group 1",
					"description" : "This group is a GISHE group that meets in the Webuye Area."
				},
				{
					"location_id" : 229,
					"name" : "Kitale MCH",
					"description" : "mch clinic at kitale"
				},
				{
					"location_id" : 230,
					"name" : "Busia MCH",
					"description" : "mch clinic at busia."
				},
				{
					"location_id" : 231,
					"name" : "Chulaimbo MCH",
					"description" : "mch clinic at chulaimbo."
				}
			]';

		DB::statement("ALTER TABLE amrslocations MODIFY COLUMN id smallint UNSIGNED AUTO_INCREMENT;");
		DB::statement("ALTER TABLE samples MODIFY COLUMN amrs_location smallint UNSIGNED;");
		DB::statement("ALTER TABLE viralsamples MODIFY COLUMN amrs_location smallint UNSIGNED;");

		if(env('APP_LAB') == 5) DB::statement("ALTER TABLE cd4samples MODIFY COLUMN amrs_location smallint UNSIGNED;");
	
		$locations = json_decode($locations);

		foreach ($locations as $location) {
			$loc = DB::table('amrslocations')->where('identifier', $location->location_id)->first();
			if(!$loc){
				$loc2 = DB::table('amrslocations')->where('id', $location->location_id)->first();

				if(!$loc2){
					DB::table('amrslocations')->insert(['id' => $location->location_id, 'identifier' => $location->location_id, 'name' => $location->name]);
				}
			}
		}

		foreach ($locations as $location) {
			$loc = DB::table('amrslocations')->where('identifier', $location->location_id)->first();
			if(!$loc){
				DB::table('amrslocations')->insert(['identifier' => $location->location_id, 'name' => $location->name]);
			}
		}
	}

	public static function nyumbani()
	{
		ini_set("memory_limit", "-1");
		$file = public_path('vl_22-01-2019.csv');

		$handle = fopen($file, "r");

		$worksheet = \App\Viralworksheet::create([
			'machine_type' => 2,
			'lab_id' => env('APP_LAB'),
			'datereviewed' => '2019-01-22',
			'dateuploaded' => '2019-01-22',
			'daterun' => '2019-01-22',
			'status_id' => 3,
			'sampletype' => 2,
		]);

		$batches = [];

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE){
            $facility = Facility::locate($row[4])->get()->first();
            if(!$facility || !is_numeric($row[4])) continue;

            $datecollected = Lookup::other_date($row[9]);
            $datereceived = Lookup::other_date($row[13]);
            if(!$datereceived) $datereceived = date('Y-m-d');
            $patient_string = $row[2];
            $existing = \App\ViralsampleView::where(['facility_id' => $facility->id, 'patient' => $patient_string, 'datecollected' => $datecollected])->get()->first();

            if($existing){
                // $existing_rows[] = $existing->toArray();
                continue;
            }

            $batch = \App\Viralbatch::withCount(['sample'])
                                    // ->where('received_by', auth()->user()->id)
                                    ->where('datereceived', $datereceived)
                                    ->where('input_complete', 0)
                                    ->where('site_entry', 1)
                                    ->where('facility_id', $facility->id)
                                    ->get()->first();

            if($batch){
                if($batch->sample_count > 9){
                    unset($batch->sample_count);
                    $batch->full_batch();
                    $batch = null;
                }
            }

            if(!$batch){
                $batch = new \App\Viralbatch;
                // $batch->user_id = auth()->user()->id;
                $batch->facility_id = $facility->id;
                // $batch->received_by = auth()->user()->id;
                $batch->lab_id = env('APP_LAB');
                $batch->datereceived = $datereceived;
                $batch->site_entry = 1;
                $batch->save();

                $batches[] = $batch->id;
            }

            $patient = \App\Viralpatient::existing($facility->id, $patient_string)->first();
            if(!$patient) $patient = new \App\Viralpatient;

            $patient->patient = $patient_string;
            $patient->facility_id = $facility->id;
            $patient->dob = Lookup::calculate_dob($datecollected, $row[7]);
            $patient->sex = Lookup::get_gender($row[6]);
            $patient->initiation_date = Lookup::other_date($row[11]);
            $patient->save();


            $sample = new \App\Viralsample;
            $sample->batch_id = $batch->id;
            $sample->patient_id = $patient->id;
            $sample->datecollected = $datecollected;
            $sample->age = $row[7];
            if(str_contains(strtolower($row[8]), ['edta'])) $sample->sampletype = 2; 

            $sample->areaname = $row[5];
            $sample->label_id = $row[1];
            $sample->prophylaxis = Lookup::viral_regimen($row[10]);
            $sample->justification = Lookup::justification($row[12]);
            $sample->pmtct = 3;
            $sample->receivedstatus = 1;
            $sample->worksheet_id = $worksheet->id;
            $sample->datetested = $sample->dateapproved = '2019-01-22';
            $results = \App\MiscViral::sample_result($row[14]);
            $sample->fill($results);

            $sample->save();

            // $created_rows++;
        }
        \App\Viralbatch::whereIn('id', $batches)->update(['batch_complete' => 1, 'datedispatched' => '2019-01-22']);
	}

	public static function __getLablogsData($year, $month) {
		
		$performance = LabPerformanceTracker::where('year', $year)->where('month', $month)->get();
		$eidcount = Sample::selectRaw("count(*) as tests")->whereYear('datetested', $year)->whereMonth('datetested', $month)->where('flag', '=', 1)->first()->tests;
		$eidrejected = SampleView::selectRaw('distinct rejectedreasons.name')->join('rejectedreasons', 'rejectedreasons.id', '=', 'samples_view.rejectedreason')->where('receivedstatus', '=', 2)->whereYear('samples_view.datereceived', $year)->whereMonth('samples_view.datereceived', $month)->get();

		$vlplasmacount = Viralsample::selectRaw("count(*) as tests")->whereYear('datetested', $year)->whereMonth('datetested', $month)->where('flag', 1)->whereBetween('sampletype', [1,2])->first()->tests;
		$vlplasmarejected = ViralsampleView::selectRaw('distinct rejectedreasons.name')->join('rejectedreasons', 'rejectedreasons.id', '=', 'viralsamples_view.rejectedreason')->where('receivedstatus', '=', 2)->whereBetween('sampletype', [1,2])->whereYear('viralsamples_view.datereceived', $year)->whereMonth('viralsamples_view.datereceived', $month)->get();

		$vldbscount = Viralsample::selectRaw("count(*) as tests")->whereYear('datetested', $year)->whereMonth('datetested', $month)->where('flag', 1)->whereBetween('sampletype', [3,4])->first()->tests;
		$vldbsrejected = ViralsampleView::selectRaw('distinct rejectedreasons.name')->join('rejectedreasons', 'rejectedreasons.id', '=', 'viralsamples_view.rejectedreason')->where('receivedstatus', '=', 2)->whereBetween('sampletype', [3,4])->whereYear('viralsamples_view.datereceived', $year)->whereMonth('viralsamples_view.datereceived', $month)->get();
		
		$equipment = LabEquipmentTracker::where('year', $year)->where('month', $month)->get();
		return (object)['performance' => $performance, 'equipments' => $equipment, 'year' => $year, 'month' => $month, 'eidcount' => $eidcount, 'vlplasmacount' => $vlplasmacount, 'vldbscount' => $vldbscount, 'eidrejected' => $eidrejected, 'vlplasmarejected' => $vlplasmarejected, 'vldbsrejected' => $vldbsrejected];
	}

	public static function adjust_deliveries($plartform, $id, $quantity, $damaged) {
		if ($plartform == 1) {
			$deliveries = Taqmandeliveries::class;
			$kits = (object)self::$taqmanKits;
		} else if ($plartform == 2) {
			$deliveries = Abbotdeliveries::class;
			$kits = (object)self::$abbottKits;
		}

		$getdeliveries = $deliveries::where('id', '=', $id)->first();
		foreach ($kits as $key => $kit) {
			$alias = $kit['alias'];
			$received = $alias.'received';
			$columndamaged = $alias.'damaged';
			if ($kit['alias'] == 'qualkit'){
				$getdeliveries->$received = $quantity;
				$getdeliveries->$columndamaged = $damaged;
			} else {
				if ($plartform == 1) {
					$insertquantity = (is_nan(@((int)$quantity * $kit['factor']))) ? 0 : @((int)$quantity * $kit['factor']);
					$insertdamaged = (is_nan(@((int)$damaged * $kit['factor']))) ? 0 : @((int)$damaged * $kit['factor']);
					$getdeliveries->$received = $insertquantity;
					$getdeliveries->$columndamaged = $insertdamaged;
				} else if ($plartform == 2) {
					if ($getdeliveries->testtype == 1)
						$factor = $kit['factor']['EID'];
					else 
						$factor = $kit['factor']['VL'];
					$insertquantity = (is_nan((int)$quantity * $factor)) ? 0 : $quantity * $factor;
					$insertdamaged = (is_nan((int)$damaged * $factor)) ? 0 : $damaged * $factor;
					$getdeliveries->$received = $insertquantity;
					$getdeliveries->$columndamaged = $insertdamaged;
				}
			}
		}
		$getdeliveries->save();
	}


	public static function tat5()
	{
		DB::statement("ALTER TABLE `batches` ADD `tat5` tinyint unsigned NULL AFTER `datedispatched`");
		DB::statement("ALTER TABLE `viralbatches` ADD `tat5` tinyint unsigned NULL AFTER `datedispatched`");

        DB::statement("
        CREATE OR REPLACE VIEW samples_view AS
        (
          SELECT s.*, b.national_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.tat5, b.site_entry, b.batch_complete, b.lab_id, b.user_id, b.received_by, b.entered_by, f.facilitycode, f.name as facilityname, b.facility_id, b.input_complete,  p.national_patient_id, p.patient, p.sex, p.dob, p.mother_id, p.entry_point, p.patient_name, p.patient_phone_no, p.preferred_language, p.dateinitiatedontreatment,
          p.hei_validation, p.enrollment_ccc_no, p.enrollment_status, p.referredfromsite, p.otherreason

          FROM samples s
            JOIN batches b ON b.id=s.batch_id
            JOIN patients p ON p.id=s.patient_id
            LEFT JOIN facilitys f ON f.id=b.facility_id
        );
        ");

        DB::statement("
        CREATE OR REPLACE VIEW viralsamples_view AS
        (
          SELECT s.*, b.national_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.tat5, b.site_entry, b.batch_complete, b.lab_id, b.user_id, b.received_by, b.entered_by, f.facilitycode, f.name as facilityname, b.facility_id, b.input_complete,
          p.national_patient_id, p.patient, p.initiation_date, p.sex, p.dob, p.patient_name, p.patient_phone_no, p.preferred_language

          FROM viralsamples s
            JOIN viralbatches b ON b.id=s.batch_id
            JOIN viralpatients p ON p.id=s.patient_id
            LEFT JOIN facilitys f ON f.id=b.facility_id
        );
        ");

        \App\Common::save_tat5('eid');
        \App\Common::save_tat5('vl');
	}


	public static function time_received()
	{
		\App\Batch::where('id', '>', 1000)->update(['time_received' => null]);
		\App\Viralbatch::where('id', '>', 10000)->update(['time_received' => null]);

		DB::statement("ALTER TABLE `batches` MODIFY COLUMN `time_received` datetime NULL");
		DB::statement("ALTER TABLE `viralbatches` MODIFY COLUMN `time_received` datetime NULL");

        DB::statement("
        CREATE OR REPLACE VIEW samples_view AS
        (
          SELECT s.*, b.national_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.tat5, b.time_received, b.site_entry, b.batch_complete, b.lab_id, b.user_id, b.received_by, b.entered_by, f.facilitycode, f.name as facilityname, b.facility_id, b.input_complete,  p.national_patient_id, p.patient, p.sex, p.dob, p.mother_id, p.entry_point, p.patient_name, p.patient_phone_no, p.preferred_language, p.dateinitiatedontreatment,
          p.hei_validation, p.enrollment_ccc_no, p.enrollment_status, p.referredfromsite, p.otherreason

          FROM samples s
            JOIN batches b ON b.id=s.batch_id
            JOIN patients p ON p.id=s.patient_id
            LEFT JOIN facilitys f ON f.id=b.facility_id
        );
        ");

        DB::statement("
        CREATE OR REPLACE VIEW viralsamples_view AS
        (
          SELECT s.*, b.national_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.tat5, b.time_received, b.site_entry, b.batch_complete, b.lab_id, b.user_id, b.received_by, b.entered_by, f.facilitycode, f.name as facilityname, b.facility_id, b.input_complete,
          p.national_patient_id, p.patient, p.initiation_date, p.sex, p.dob, p.patient_name, p.patient_phone_no, p.preferred_language

          FROM viralsamples s
            JOIN viralbatches b ON b.id=s.batch_id
            JOIN viralpatients p ON p.id=s.patient_id
            LEFT JOIN facilitys f ON f.id=b.facility_id
        );
        ");
	}

	public static function clean_batches()
	{
		$batches = DB::select("select b.id, b.created_at, count(s.id) as s_count from viralbatches b left join viralsamples s on b.id=s.batch_id where s.repeatt=1 and b.id IN (select b.id from viralbatches b left join viralsamples s on b.id=s.batch_id group by b.id having count(s.id)=1 ) and date(b.created_at) > '2019-02-01' group by b.id having s_count=1;");

		foreach ($batches as $key => $batch) {
			$b = \App\Viralbatch::find($batch->id);
			$s = $b->sample->first();
			$c = $s->child->first();
			$c->batch_id = $b->id;
			$c->save();

			echo "Cleaned batch {$b->id} \n";
		}
	}


	public static function facility_tables()
	{
		DB::statement("
			CREATE TABLE IF NOT EXISTS `facility_changes` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `old_facility_id` int(10) unsigned NOT NULL,
			  `new_facility_id` int(10) unsigned NOT NULL,
			  `temp_facility_id` int(10) unsigned NOT NULL,
			  `implemented` tinyint unsigned NOT NULL DEFAULT 0,
			  `created_at` timestamp NULL DEFAULT NULL,
			  `updated_at` timestamp NULL DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `old_facility_id` (`old_facility_id`),
			  KEY `new_facility_id` (`new_facility_id`),
			  KEY `temp_facility_id` (`temp_facility_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		");
	}

	public static function eid_worksheets()
	{
		$data = \App\SampleView::selectRaw("year(daterun) as year, month(daterun) as month, machine_type, result, count(*) as tests ")
			->join('worksheets', 'worksheets.id', '=', 'samples_view.worksheet_id')
			->where('site_entry', '!=', 2)
			->whereYear('daterun', 2018)
			->where(['samples_view.lab_id' => env('APP_LAB')])
			->groupBy('year', 'month', 'machine_type', 'result')
			->orderBy('year', 'month', 'machine_type', 'result')
			->get();

		$results = [1 => 'Negative', 2 => 'Positive', 3 => 'Failed', 4 => 'Unknown', 5 => 'Collect New Sample'];
		$machines = [1 => 'Roche', 2 => 'Abbott'];

		$rows = [];

		for ($i=1; $i < 13; $i++) { 
			foreach ($machines as $mkey => $mvalue) {
				$row = ['Year of Testing' => 2018, 'Month of Testing' => date('F', strtotime("2018-{$i}-1")), ];
				$row['Machine'] = $mvalue;
				$total = 0;

				foreach ($results as $rkey => $rvalue) {
					$row[$rvalue] = $data->where('result', $rkey)->where('machine_type', $mkey)->where('month', $i)->first()->tests ?? 0;
					$total += $row[$rvalue];
				}

				$row['Total'] = $total;
				$rows[] = $row;
			}
		}

		$file = 'eid_worksheets_data';

		Excel::create($file, function($excel) use($rows){
			$excel->sheet('Sheetname', function($sheet) use($rows) {
				$sheet->fromArray($rows);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		// Mail::to(['joelkith@gmail.com'])->send(new TestMail($data));
	}

	public static function vl_worksheets()
	{
		$data = \App\ViralsampleView::selectRaw("year(daterun) as year, month(daterun) as month, machine_type, rcategory, count(*) as tests ")
			->join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples_view.worksheet_id')
			->where('site_entry', '!=', 2)
			->whereYear('daterun', 2018)
			->where(['viralsamples_view.lab_id' => env('APP_LAB')])
			->groupBy('year', 'month', 'machine_type', 'rcategory')
			->orderBy('year', 'month', 'machine_type', 'rcategory')
			->get();

		$results = [1 => 'LDL', 2 => '<= 1000', 3 => '> 1000 & <= 5000', 4 => '> 5000', 5 => 'Collect New Sample'];
		$machines = [1 => 'Roche', 2 => 'Abbott', 3 => 'C8800'];

		$rows = [];

		for ($i=1; $i < 13; $i++) { 
			foreach ($machines as $mkey => $mvalue) {
				$row = ['Year of Testing' => 2018, 'Month of Testing' => date('F', strtotime("2018-{$i}-1")), ];
				$row['Machine'] = $mvalue;
				$total = 0;

				foreach ($results as $rkey => $rvalue) {
					$row[$rvalue] = $data->where('rcategory', $rkey)->where('machine_type', $mkey)->where('month', $i)->first()->tests ?? 0;
					$total += $row[$rvalue];
				}

				$row['Total'] = $total;
				$rows[] = $row;
			}
		}

		$file = 'vl_worksheets_data';

		Excel::create($file, function($excel) use($rows){
			$excel->sheet('Sheetname', function($sheet) use($rows) {
				$sheet->fromArray($rows);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		// Mail::to(['joelkith@gmail.com'])->send(new TestMail($data));
	}

	public static function adjust_procurement($plartform, $id, $ending, $wasted, $issued, $request, $pos) {
		if ($plartform == 1) {
			$consumption = Taqmanprocurement::class;
			$kits = (object)self::$taqmanKits;
		} else if ($plartform == 2) {
			$consumption = Abbotprocurement::class;
			$kits = (object)self::$abbottKits;
		}
		$consumptions = $consumption::findOrFail($id);
		if ((int)$ending > 0) 
			self::adjust_procurement_numbers($consumptions, $ending, $kits, 'ending');
		if ((int)$wasted > 0)
			self::adjust_procurement_numbers($consumptions, $wasted, $kits, 'wasted');
		if ((int)$issued > 0) 
			self::adjust_procurement_numbers($consumptions, $issued, $kits, 'issued');
		if ((int)$request > 0)
			self::adjust_procurement_numbers($consumptions, $request, $kits, 'request');
		if ((int)$pos > 0)
			self::adjust_procurement_numbers($consumptions, $pos, $kits, 'pos');
		if($consumptions->isDirty());
			$consumptions->pre_update();
	}

	protected static function adjust_procurement_numbers(&$model, $qualquantity, $kits, $type) {
		$qualkitvalue = 0;
		foreach($kits as $kit) {
			$kit = (object)$kit;
			$column = $type.$kit->alias;
			if ($kit->alias == 'qualkit')
				$qualkitvalue = $qualquantity;

			$factor = $kit->factor;
			$classname = get_class($model);
			if ($classname == "App\Abbotprocurement"){
				if (!($factor = $kit->factor['EID']))
					$factor = $kit->factor['VL'];
			}
			$model->$column = $qualkitvalue * $factor;
		}
	}


	public static function create_attachments_table()
	{
		DB::statement("
			CREATE TABLE `attachments` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `email_id` int(10) unsigned NOT NULL ,
		  `attachment_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `download_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `created_at` timestamp NULL DEFAULT NULL,
		  `updated_at` timestamp NULL DEFAULT NULL,
		  `deleted_at` timestamp NULL DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `attachments_email_id_index` (`email_id`)
		) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		");
	}

	public static function temp_correct_repeats() {
		$new_array = [];
		$samples_id = [
						// ['patient' => '17190-2019-0001', 'batch_id' => 33516],
						// ['patient' => '16152-2018-025', 'batch_id' => 33903],
						// ['patient' => '16066/2019/0008', 'batch_id' => 33702],
						// ['patient' => '16061-2019-0002', 'batch_id' => 33823],
						// ['patient' => '16027-2019-0003', 'batch_id' => 32807],
						// ['patient' => '15969-2019-0012', 'batch_id' => 33577],
						// ['patient' => '15969-2019-0008', 'batch_id' => 33413],
						// ['patient' => '15940-2019-0006', 'batch_id' => 33976],
						// ['patient' => '15874/2019/007', 'batch_id' => 33804],
						// ['patient' => '15833/2018/0027', 'batch_id' => 32673],
						// ['patient' => '15823-2019-0013', 'batch_id' => 33933],
						// ['patient' => '15795/2018/0057', 'batch_id' => 33054],
						['patient' => '14512-2019-0003', 'batch_id' => 30774] //Belongs to AMPATH
						// ['patient' => '1793120180031', 'batch_id' => 33064],
						// ['patient' => '1585020180006', 'batch_id' => 33961],
						// ['patient' => '1582620180006', 'batch_id' => 32064],
						// ['patient' => '158712018015', 'batch_id' => 33411]
					];

		foreach ($samples_id as $key => $sample) {
			$db_sample = SampleView::where($sample)->get();
			if(!$db_sample->isEmpty()){
				$update_sample = Sample::find($db_sample->first()->id);
				$update_sample->pcrtype = 2;
				$update_sample->pre_update();
			} else {
				$new_array[] = $sample;
			}
		}
		echo "<pre>";print_r($new_array);
	}

	public static function import_edarp_samples_excel($received_by) {
		$nofacility = [];
		$dataArray = [];
        echo "==>Upload Begin\n";
        $file = 'public/docs/knh-28-2-2019.xlsx';
		// $file = 'public/docs/knh-28-2-2019.xlsx';
        $batch = null;
        $lookups = Lookup::get_viral_lookups();
        // dd($lookups);
        echo "\t Fetching excel data\n";
        $excelData = Excel::load($file, function($reader){
            $reader->toArray();
        })->get();
        $excelsheetvalue = collect($excelData->values()->all());
        echo "\t Inserting sample data\n";
        $dataArray = [];
        $dataArray = ['Viral Batches'];
        $countItem = $excelsheetvalue->count();
        $counter = 0;
        $loop = 1;
        if (!$excelsheetvalue->isEmpty()){
            foreach ($excelsheetvalue as $samplekey => $samplevalue) {
            	$counter++;
            	echo $loop . "   ";
            	$loop++;
                $facility = Facility::where('facilitycode', '=', $samplevalue[5])->first();
                if (!isset($facility)){
                    $nofacility[] = $samplevalue;
                    continue;
                }
                $existing = Viralpatient::existing($facility->id, $samplevalue[3])->first();
                
                if ($existing)
                    $patient = $existing;
                else {
                    $patient = new Viralpatient();
                    $patient->patient = $samplevalue[3];
                    $patient->facility_id = $facility->id;
                    $patient->sex = $lookups['genders']->where('gender', $samplevalue[6])->first()->id;
                    $patient->dob = $samplevalue[9];
                    // $patient->initiation_date = $samplevalue[14];
                    $patient->save();
                }
                

                $existingSample = ViralsampleView::existing(['facility_id' => $facility->id, 'patient' => $patient->patient, 'datecollected' => $samplevalue[11]])->first();
                
                if ($existingSample) {
                	$batch = Viralbatch::find($existingSample->batch_id);
                	if ($batch->count() == 10)
                		$counter = 0;
                    continue;
                }
                if ($counter == 1) {                    
                    $batch = new Viralbatch();
                    $batch->user_id = $received_by;
                    $batch->lab_id = $samplevalue[2];
                    $batch->received_by = $received_by;
                    $batch->site_entry = 0;
                    $batch->entered_by = $received_by;
                    $batch->datereceived = $samplevalue[16];
                    $batch->facility_id = $facility->id;
                    $batch->save();
                }
                $existingSampleCheck = ViralsampleView::existing(['facility_id' => $facility->id, 'patient' => $patient->patient, 'datecollected' => $samplevalue[11]])->first();
                if ($existingSampleCheck) {
                	$dataArray[] = $existingSampleCheck->batch_id;
                	continue;
                }
                $sample = new Viralsample();
                $sample->batch_id = $batch->id;
                $sample->receivedstatus = $samplevalue[18];
                $sample->age = $samplevalue[8];
                $sample->patient_id = $patient->id;
                $sample->pmtct = $samplevalue[7];
                $sample->dateinitiatedonregimen = $samplevalue[14];
                $sample->datecollected = $samplevalue[11];
                $sample->regimenline = $samplevalue[13];
                $sample->prophylaxis = $lookups['prophylaxis']->where('category', $samplevalue[12])->first()->id ?? 15;
                $sample->justification = $lookups['justifications']->where('rank', $samplevalue[15])->first()->id ?? 8;
                $sample->sampletype = $samplevalue[10];
                $sample->save();

                $sample_count = $batch->sample->count();
                echo ".";
                $countItem -= 1;
                if($counter == 10) {
                    $dataArray[] = $batch->id;
                    $batch->full_batch();
                    $batch = null;
                    $counter = 0;
                } 

                if ($countItem == 1) {
                    $sample_count = $batch->sample->count();
                    if ($sample_count != 10) {
                        $batch->premature();
                        $dataArray[] = $batch->id;
                    }
                }
                // echo "<pre>";print_r("Close Batch {$batch}");echo "</pre>"; // Close batch
            }
            echo "\n\t Creating uploaded batches and missing facilities excel\n";
            $file = 'EDARP Samples uploaded to ' . Lab::find(env('APP_LAB'))->labdesc . date('Y_m_d H_i_s');

            Excel::create($file, function($excel) use($dataArray, $file){
                $excel->setTitle($file);
                $excel->setCreator('Joshua Bakasa')->setCompany($file);
                $excel->setDescription($file);

                $excel->sheet('Sheetname', function($sheet) use($dataArray) {
                    $sheet->fromArray($dataArray);
                });
            })->store('csv');

            $file2 = 'EDARP Samples uploaded to ' . Lab::find(env('APP_LAB'))->labdesc . ' without facilitycode'.date('Y_m_d H_i_s');

            Excel::create($file2, function($excel) use($nofacility, $file2){
                $excel->setTitle($file2);
                $excel->setCreator('Joshua Bakasa')->setCompany($file2);
                $excel->setDescription($file2);

                $excel->sheet('Sheetname', function($sheet) use($nofacility) {
                    $sheet->fromArray($nofacility);
                });
            })->store('csv');
            echo "\t Emailing uploaded batches and missing facilities excel\n";
            $data = [storage_path("exports/" . $file . ".csv"), storage_path("exports/" . $file2 . ".csv")];

            Mail::to(['bakasajoshua09@gmail.com'])->send(new TestMail($data));

            // $title = "EDARP Samples uploaded to KEMRI";
            // Excel::create($title, function($excel) use ($dataArray, $title) {
            //     $excel->setTitle($title);
            //     $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('WJ Gilmore, LLC');
            //     $excel->setDescription($title);

            //     $excel->sheet('Sheet1', function($sheet) use ($dataArray) {
            //         $sheet->fromArray($dataArray, null, 'A1', false, false);
            //     });

            // })->download('csv');
            echo "==>Upload Complete";
        }
	}

	public static function export_edarp_results() {
        echo "==> Retrival Begin \n";
		$file = 'public/docs/EDARP_samples_on_KEMRI_752019.xlsx';
        // $batch = null;
        // $lookups = Lookup::get_viral_lookups();
        // dd($lookups);
		// $file = 'public/docs/EDARP_samples_on_KEMRI_752019.xlsx';// KEMRI
		// $file = 'public/docs/knh-28-2-2019.xlsx';// KNH

		/***  KEMRI Results File ***/
		// $rfiles = ['public/docs/15722.CSV',
		// 			'public/docs/15723.CSV',
		// 			'public/docs/15724.CSV',
		// 			'public/docs/15725.CSV',
		// 			'public/docs/15726.CSV'];
		$rfiles = ['public/docs/Edarp1.xlsx',
					'public/docs/Edarp2.xlsx',
					'public/docs/Edarp3.xlsx'];

        echo "==> Fetching Excel Data \n";
		$rdata = [];
		foreach ($rfiles as $key => $value) {
			$rexcelData = Excel::load($value, function($reader){
				$reader->toArray();
			})->get();
			foreach ($rexcelData as $datakey => $datavalue) {
				$rdata[] = $datavalue;
			}
		}
		$rdata = collect($rdata);
		/***  KEMRI Results File ***/

        $excelData = Excel::load($file, function($reader){
            $reader->toArray();
        })->get();
        $data = $excelData;
        $newData = [];
        $newData[] = ['Test Type','TestingLab','SpecimenLabelID','SpecimenClientCode','FacilityName','MFLCode','Sex','PMTCT','Age','DOB','SampleType','DateCollected','CurrentRegimen','regimenLine','ART Init Date','Justification','DateReceived','loginDate','ReceivedStatus','RejectedReason','ReasonforRepeat','LabComment','Datetested','DateDispatched','Results','Edited'];
        // dd($data);
        echo "==> Getting Results \n";
        $count = 0;
        $availablecount = 0;
        $worksheet = [];
        $today = $datetested = date("Y-m-d");
        $my = new MiscViral;
        $sample_array = $doubles = [];
        // Loop through the worksheet data
        // foreach ($rdata as $key => $excelworksheet) {
        // 	# code...
        // }
        foreach ($data as $key => $sample) {
            $dbsamples = ViralsampleView::where('patient', '=', $sample[3])->where('datecollected', '=', $sample[11])->get();
            if ($dbsamples){
            	foreach ($dbsamples as $key => $samplefound) {
			        $nc = $nc_int = $lpc = $lpc_int = $hpc = $hpc_int = $nc_units = $hpc_units = $lpc_units =  NULL;
            		$samplefound = Viralsample::find($samplefound->id);
            		$excelResult = $rdata->where(2, $samplefound->id)->first();
            		// Update worksheet data if found
            		if ($excelResult){
            			$wsheet = $samplefound->worksheet;
            			$date_tested=date("Y-m-d", strtotime($excelResult[9]));
		                $datetested = MiscViral::worksheet_date($date_tested, $wsheet->created_at);

		                $interpretation = $excelResult[5];
		                $error = $excelResult[6];

			            MiscViral::dup_worksheet_rows($doubles, $sample_array, $samplefound->id, $interpretation);

			            $result_array = MiscViral::sample_result($interpretation, $error);

		                $sample_type = $excelResult[0];

		                if($sample_type == "NC"){
		                    $nc = $result_array['result'];
		                    $nc_int = $result_array['interpretation']; 
		                    $nc_units = $result_array['units']; 
		                }
		                else if($sample_type == "HPC"){
		                    $hpc = $result_array['result'];
		                    $hpc_int = $result_array['interpretation'];
		                    $hpc_units = $result_array['units'];
		                }
		                else if($sample_type == "LPC"){
		                    $lpc = $result_array['result'];
		                    $lpc_int = $result_array['interpretation'];
		                    $lpc_units = $result_array['units'];
		                }

		                $data_array = array_merge(['datemodified' => $today, 'datetested' => $datetested], $result_array);

		                $samplefound->fill($data_array);
		                if ($samplefound->national_sample_id && $samplefound->synched == 1){
                			$samplefound->synched = 2;
                		}
                		$samplefound->save();
                		$batch = $samplefound->batch;
                		if ($batch->national_batch_id && $batch->synched == 1){
                			$batch->synched = 2;
                		}
                		$batch->lab_id = 10;
                		$batch->datedispatched = date('Y-m-d', strtotime("- 25 days"));
                		$batch->save();

                		Viralsample::where(['worksheet_id' => $wsheet->id])->where('run', 0)->update(['run' => 1]);
				        Viralsample::where(['worksheet_id' => $wsheet->id])->whereNull('repeatt')->update(['repeatt' => 0]);
				        Viralsample::where(['worksheet_id' => $wsheet->id])->whereNull('result')->update(['repeatt' => 1]);

				        if (!in_array($wsheet->id, $worksheet)){
				        	$wsheet->status_id = 3;
	            			$wsheet->lab_id = 10;
					        $wsheet->neg_units = $nc_units;
					        $wsheet->neg_control_interpretation = $nc_int;
					        $wsheet->neg_control_result = $nc;

					        $wsheet->hpc_units = $hpc_units;
					        $wsheet->highpos_control_interpretation = $hpc_int;
					        $wsheet->highpos_control_result = $hpc;

					        $wsheet->lpc_units = $lpc_units;
					        $wsheet->lowpos_control_interpretation = $lpc_int;
					        $wsheet->lowpos_control_result = $lpc;

					        $wsheet->daterun = $datetested;
					        $wsheet->uploadedby = 41;

					        $wsheet->save();

					        $worksheet[] = $wsheet->id;
				        }

					    MiscViral::requeue($wsheet->id);       			
            		}
            		
            	}
            }
            // $dbsample = $dbsamples->last();
            
            // if(empty($worksheet) || !in_array($dbsample->worksheet_id, $worksheet) )
            // 	$worksheet[] = $dbsample->worksheet_id;

            // /* File worksheet reagion */
            // $excelResult = $rdata->where(5, 'S')->where(4, $dbsample->id)->first();
            // // dd($excelResult);
            // if (!$excelResult)
            // 	continue;
            // $excelResult = $excelResult->toArray();
            // /* File worksheet reagion */

            // if ($dbsample)
            // 	$availablecount++;
            // else
            // 	$count++;
            // $sample[19] = $dbsample->rejectedreason ?? null;
            // $sample[20] = $dbsample->reason_for_repeat ?? null;
            // $sample[21] = $dbsample->labcomment ?? $excelResult[12] ?? null;
            // $sample[22] = (isset($dbsample->datetested)) ? date('m/d/Y', strtotime($dbsample->datetested)) : $excelResult[11] ?? null;
            // $sample[23] = (isset($dbsample->datedispatched)) ? date('m/d/Y', strtotime($dbsample->datedispatched)) : $excelResult[11] ?? null;
            // // $sample[22] = $dbsample->datetested;
            // // $sample[23] = $dbsample->datedispatched;
            // $sample[24] = $dbsample->result ?? $excelResult[8] ?? null;
            // $newData[] = $sample->toArray();
        }
        // echo "\t";
        // print_r($worksheet);
        // echo "\n";
        echo "==> Available Results - " . $availablecount . "; Unavailable - " . $count;
        // echo "\n==> Building excel results \n";

        // $file = 'KEMRI2EDARP'.date('Y_m_d H_i_s');

        // Excel::create($file, function($excel) use($newData, $file){
        //     $excel->setTitle($file);
        //     $excel->setCreator('Joshua Bakasa')->setCompany($file);
        //     $excel->setDescription($file);

        //     $excel->sheet('Sheetname', function($sheet) use($newData) {
        //         $sheet->fromArray($newData);
        //     });
        // })->store('csv');

        // $data = [storage_path("exports/" . $file . ".csv")];

        // Mail::to(['bakasajoshua09@gmail.com'])->send(new TestMail($data));

        echo "==>Retrival Complete";
	}

	public static function delete_edarp_imported_batches() {
		echo "==> Deleting Samples\n";
		$batches = [];
		// $batches = collect($excelData->toArray())->first();
        Viralsample::whereIn('batch_id', $batches)->delete();
        Viralbatch::whereIn('id', $batches)->delete();
        echo "==> Deletion Complete\n";
	}

	public static function confirm_edarp_upload($received_by) {
		echo "==>Check Begin\n";
		$file = 'public/docs/EDARP_samples_being_referred_to _KNH_CCC_laboratory.xlsx';
        // $batch = null;
        // $lookups = Lookup::get_viral_lookups();
        // // dd($lookups);
        $addedcount = 0;
        $missingcount = 0;
        echo "\t Fetching excel data\n";
        $excelData = Excel::load($file, function($reader){
            $reader->toArray();
        })->get();
        $excelsheetvalue = collect($excelData->values()->all());
        $countItem = $excelsheetvalue->count();
        $counter = 0;
        echo "\t Beginning Count\n";
        if (!$excelsheetvalue->isEmpty()){
            foreach ($excelsheetvalue as $samplekey => $samplevalue) {
            	$facility = Facility::where('facilitycode', '=', $samplevalue[5])->first();
				$patient = Viralpatient::existing($facility->id, $samplevalue[3])->first();
            	$existingSampleCheck = ViralsampleView::existing(['facility_id' => $facility->id, 'patient' => $patient->patient, 'datecollected' => $samplevalue[11]])->first();
                if ($existingSampleCheck) {
                	$addedcount++;
                	$countItem -= 1;
                } else {
                	print_r($samplevalue);
     //            	$counter++;
					// $nofacility = [];
					// $dataArray = [];

			  //       $lookups = Lookup::get_viral_lookups();
     //            	if ($counter == 1) {                    
	    //                 $batch = new Viralbatch();
	    //                 $batch->user_id = $received_by;
	    //                 $batch->lab_id = env('APP_LAB');
	    //                 $batch->received_by = $received_by;
	    //                 $batch->site_entry = 0;
	    //                 $batch->entered_by = $received_by;
	    //                 $batch->datereceived = $samplevalue[16];
	    //                 $batch->facility_id = $facility->id;
	    //                 $batch->save();
	    //             }
	    //             $existingSampleCheck = ViralsampleView::existing(['facility_id' => $facility->id, 'patient' => $patient->patient, 'datecollected' => $samplevalue[11]])->first();
	    //             if ($existingSampleCheck) {
	    //             	$dataArray[] = $existingSampleCheck->batch_id;
	    //             	continue;
	    //             }
	    //             $sample = new Viralsample();
	    //             $sample->batch_id = $batch->id;
	    //             $sample->receivedstatus = $samplevalue[18];
	    //             $sample->age = $samplevalue[8];
	    //             $sample->patient_id = $patient->id;
	    //             $sample->pmtct = $samplevalue[7];
	    //             $sample->dateinitiatedonregimen = $samplevalue[14];
	    //             $sample->datecollected = $samplevalue[11];
	    //             $sample->regimenline = $samplevalue[13];
	    //             $sample->prophylaxis = $lookups['prophylaxis']->where('category', $samplevalue[12])->first()->id ?? 15;
	    //             $sample->justification = $lookups['justifications']->where('rank', $samplevalue[15])->first()->id ?? 8;
	    //             $sample->sampletype = $samplevalue[10];
	    //             $sample->save();

	    //             $sample_count = $batch->sample->count();
	    //             echo ".";
	    //             $countItem -= 1;
	    //             if($counter == 10) {
	    //                 $dataArray[] = $batch->id;
	    //                 $batch->full_batch();
	    //                 $batch = null;
	    //                 $counter = 0;
	    //             } 

	    //             if ($countItem == 1) {
	    //                 $sample_count = $batch->sample->count();
	    //                 if ($sample_count != 10) {
	    //                     $batch->premature();
	    //                     $dataArray[] = $batch->id;
	    //                 }
	    //             }
                	$missingcount++;
                }
            }
        }
        echo "\t Count complete data\n";
        echo "==> Check complete with available " . $addedcount . " and missing " . $missingcount;
	}
	public static function delete_duplicates () {
		echo "==>Check Begin\n";
		$file = 'public/docs/EDARP_samples_being_referred_to _KNH_CCC_laboratory.xlsx';
        echo "\t Fetching excel data\n";
        $excelData = Excel::load($file, function($reader){
            $reader->toArray();
        })->get();
        $excelsheetvalue = collect($excelData->values()->all());
        echo "\t Checking duplicates Count\n";
        if (!$excelsheetvalue->isEmpty()){
            foreach ($excelsheetvalue as $samplekey => $samplevalue) {
            	$facility = Facility::where('facilitycode', '=', $samplevalue[5])->first();
            	$patient = Viralpatient::existing($facility->id, $samplevalue[3])->first();
            	$samples = $patient->sample;
            	if ($samples->count() > 1) {
            		$firstSample = $samples->first();
            		foreach ($samples as $key => $sample) {
            			if (($firstSample->id != $sample->id) && ($sample->facility_id == $firstSample->facility_id && $sample->datecollected == $firstSample->datecollected)) {
            				if ($sample->batch->count() == 0)
            					$sample->batch->delete();
            				$sample->delete();
            			}
            		}
            	}
            }
        }
        echo "==> Complete";
	}

	public static function delete_uploads(){
		$file = 'public/docs/KemriToBeRemoved.csv';
		$excelData = Excel::load($file, function($reader){
            $reader->toArray();
        })->get();
        foreach ($excelData as $key => $sample) {
        	$dbsample = ViralsampleView::where('patient', '=',  $sample[3])->whereNull('result')->get()->last();
        	$sample = Viralsample::find($dbsample->id);
        	$batch = $sample->batch;
        	dd($batch->refresh('samples'));
        	$sample->delete();
        }
	}

    public static function checkMbNo(){
    	$files = [['file' =>'public/docs/NHRL_MBNo/eid_data_Exsting.csv', 'name' => 'eid data Exsting First'],
    			['file' =>'public/docs/NHRL_MBNo/eid_data_Exsting_part2.csv', 'name' => 'eid data Exsting First Part 2'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataSecondPart1.csv', 'name' => 'eid data Exsting Second Part 1'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataSecondPart2.csv', 'name' => 'eid data Exsting Second Part 2'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataThirdPart1.csv', 'name' => 'eid data Exsting Third Part 1'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataThirdPart2.csv', 'name' => 'eid data Exsting Third Part 2'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataFourthPart1.csv', 'name' => 'eid data Exsting Fourth Part 1'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataFourthPart2.csv', 'name' => 'eid data Exsting Fourth Part 2'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataFifthPart1.csv', 'name' => 'eid data Exsting Fifth Part 1'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataFifthPart2.csv', 'name' => 'eid data Exsting Fifth Part 2'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataSixthPart1.csv', 'name' => 'eid data Exsting Sixth Part 1'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataSixthPart2.csv', 'name' => 'eid data Exsting Sixth Part 2'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataSeventhPart1.csv', 'name' => 'eid data Exsting Seventh Part1'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataSeventhPart2.csv', 'name' => 'eid data Exsting Seventh Part2'],
    			['file' =>'public/docs/NHRL_MBNo/eidDataEighthPart1.csv', 'name' => 'eid data Exsting Eighth']
    		];
    	// $files = [['file' => 'public/docs/eidTest.xlsx', 'name' => 'EID Test Data']];
    	echo "==> Fetching Excel Data (". date('Y-m-d H:i:s') . ") \n";
    	ini_set("memory_limit", "-1");
    	foreach ($files as $key => $file) {
    		echo "====> Getting Excel Data (". date('Y-m-d H:i:s') . " - " . $file['name'] . ") \n";
    		$excelData = Excel::load($file['file'], function($reader){
	            $reader->toArray();
	        })->get();
    		// dd($excelData->toArray());
	        foreach ($excelData as $key => $value) {
	        	$dbData[] = [
	        		'c_posted' => $value[0],
	        		'label_id' => $value[1],
	        		'login_date' => $value[2],
	        	];
	        }
	        echo "====> Saving Excel Data (". date('Y-m-d H:i:s') . " - " . $file['name'] . ") \n";
	        Nhrl::insert($dbData);
	        echo "====> Saved Excel Data (". date('Y-m-d H:i:s') . " - " . $file['name'] . ") \n";
    	}
    	echo "==> All Files completed(". date('Y-m-d H:i:s') . ")";
        // $excelData = Excel::import($file, function($reader){
        //     $reader->toArray();
        // })->get();
        // $data = $excelData;
        // echo "==> Getting MB No \n";
        // dd($data);
        // foreach ($data as $key => $sample) {
        // 	$dbsample = Sample::where('comment', '=', $sample[3])->get()->last();
        // }
    }

    public static function consolidate() {
    	$missing = 'public/docs/MISSING RESULTS.xlsx';
    	$result = 'public/docs/kemri-28-2-2019.xlsx';
    	echo "==> Reading Excel files\n";
    	$missingData = Excel::load($missing, function($reader){
            $reader->toArray();
        })->get();
        $resultData = Excel::load($result, function($reader){
            $reader->toArray();
        })->get();
        $newData[] = ['Test Type','TestingLab','SpecimenLabelID','SpecimenClientCode','FacilityName','MFLCode','Sex','PMTCT','Age','DOB','SampleType','DateCollected','CurrentRegimen','regimenLine','ART Init Date','Justification','DateReceived','loginDate','ReceivedStatus','RejectedReason','ReasonforRepeat','LabComment','Datetested','DateDispatched','Results','Edited'];
        echo "==> Matching Excel results\n";
        foreach ($missingData as $key => $missing) {
        	$result = $resultData->where(2, $missing[2])->first();
        	$missing[21] = $result[21] ?? null;
    		$missing[22] = $result[22] ?? null;
    		$missing[23] = $result[23] ?? null;
    		$missing[24] = $result[24] ?? null;
    		$newData[] = $missing->toArray();
        }
        echo "==> Writing data to csv\n";
        $file = 'KEMRI2EDARP'.date('Y_m_d H_i_s');
        // dd($newData);
        Excel::create($file, function($excel) use($newData, $file){
            $excel->setTitle($file);
            $excel->setCreator('Joshua Bakasa')->setCompany($file);
            $excel->setDescription($file);

            $excel->sheet('Sheetname', function($sheet) use($newData) {
                $sheet->fromArray($newData);
            });
        })->store('csv');
        echo "==> Data consolidation complete\n";
    }
}
