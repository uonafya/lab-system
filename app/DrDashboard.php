<?php

namespace App;

use DB;

class DrDashboard 
{

	public static function resolve_month($m)
	{
		$months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		return $months[$m] ?? '';
	}

	public static function get_divisions()
	{		
		$counties = DB::table('countys')->get();
		$subcounties = DB::table('districts')->get();
		$partners = DB::table('partners')->get();
		$drug_classes = DB::table('drug_classes')->get();
		$drugs = DB::table('regimen_classes')->get();
		$dr_projects = DB::table('dr_projects')->get();
		return compact('counties', 'subcounties', 'partners', 'drug_classes', 'drugs', 'dr_projects');
	}

	public static function get_category($row)
	{
		$groupby = session('filter_groupby', 1);
		if($groupby > 9){
			if($groupby == 10) return 'Calendar Year ' . $row->year;
			if($groupby == 11) return 'FY ' . $row->financial_year;
			if($groupby == 12) return self::resolve_month($row->month) . ', ' . $row->year;
			if($groupby == 13) return "FY {$row->financial_year} Q {$row->quarter}";
			if($groupby == 14) return "FY {$row->financial_year} W {$row->week_number}";
		}
		else{
			return $row->name ?? '';
		}	
	} 


	public static function get_percentage($num, $den, $roundby=2)
	{
		if(!$den){
			$val = 0;
		}else{
			$val = round(($num / $den * 100), $roundby);
		}
		return $val;
	}

	public static function divisions_query()
	{
		$query = " 1 ";
		if(session('filter_county')) $query .= " AND county_id" . self::set_division_query(session('filter_county'));
		if(session('filter_subcounty')) $query .= " AND subcounty_id" . self::set_division_query(session('filter_subcounty'));
		if(session('filter_ward')) $query .= " AND ward_id" . self::set_division_query(session('filter_ward'));
		if(session('filter_facility')) $query .= " AND view_facilitys.id" . self::set_division_query(session('filter_facility'));
		if(session('filter_partner') || is_numeric(session('filter_partner'))) $query .= " AND partner_id" . self::set_division_query(session('filter_partner'));
		if(session('filter_project')) $query .= " AND project" . self::set_division_query(session('filter_project'));
		if(session('filter_drug_class')) $query .= " AND drug_class_id" . self::set_division_query(session('filter_drug_class'));
		if(session('filter_drug')) $query .= " AND short_name_id" . self::set_division_query(session('filter_drug'));

		return $query;
	}

	public static function set_division_query($param, $quote=false)
	{
		if(is_array($param)){
			$str = " IN (";
			foreach ($param as $key => $value) {
				if($quote) $str .= "'{$value}', ";
				else{
					$str .= "{$value}, ";
				}				
			}
			$str = substr($str, 0, -2);
			$str .= ") ";
			return $str;
		}
		else{
			if($quote) return "='{$param}' ";
			return "={$param} ";
		}
	}


	public static function groupby_query()
	{
		$groupby = session('filter_groupby', 2);

		switch ($groupby) {
			case 1:
				$select_query = "partner as div_id, partnername as name";
				$group_query = "partner";
				break;
			case 2:
				$select_query = "county_id as div_id, county as name";
				$group_query = "county_id";
				break;
			case 3:
				$select_query = "subcounty_id as div_id, subcounty as name";
				$group_query = "subcounty_id";
				break;
			case 4:
				$select_query = "ward_id as div_id, wardname as name";
				$group_query = "ward_id";
				break;
			case 5:
				$select_query = "view_facilitys.id as div_id, view_facilitys.name";
				$group_query = "view_facilitys.id";
				break;
			case 6:
				$select_query = "project.name";
				$group_query = "project";
				break;	
			case 7:
				$select_query = "drug_class as name";
				$group_query = "drug_class_id";
				break;	
			case 8:
				$select_query = "short_name as name";
				$group_query = "short_name_id";
				break;	
			default:
				break;
		}
		return ['select_query' => $select_query, 'group_query' => $group_query];
	}


	public static function splines(&$data, $splines)
	{
		$groupby = session('filter_groupby', 1);
		if(!is_array($splines)) $splines = [$splines];
		foreach ($splines as $key => $spline) {
			if($groupby < 10){
				$data['outcomes'][$spline]['lineWidth'] = 0;
				$data['outcomes'][$spline]['marker'] = ['enabled' => true, 'radius' => 4];
				$data['outcomes'][$spline]['states'] = ['hover' => ['lineWidthPlus' => 0]];
			}
			$data['outcomes'][$spline]['type'] = "spline";
		}
	}

	public static function bars($categories=[], $type='column', $colours=[])
	{
		$data['div'] = str_random(15);
		foreach ($categories as $key => $value) {
			$data['outcomes'][$key]['name'] = $value;
			$data['outcomes'][$key]['type'] = $type;
			if(isset($colours[$key])) $data['outcomes'][$key]['color'] = $colours[$key];
		}
		return $data;
	}

	public static function columns(&$data, $start, $finish, $type='column')
	{
		for ($i=$start; $i <= $finish; $i++) { 
			$data['outcomes'][$i]['type'] = $type;
		}
	}
}
