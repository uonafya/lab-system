<?php

namespace App;
use Excel;
use DB;
use App\Facilitys;

class Random
{

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

}
