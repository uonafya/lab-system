<?php

namespace App\Exports;

use DB;

trait RequestFilters{
	

    public function date_filter($request, $column)
    {
        return function($query) use($request, $column){
            if($request->input('specificDate')){
                return $query->where($column, $request->input('specificDate'));
            }
            else if (null !== $request->input('period') || $request->input('fromDate')){
                if ($request->input('period') == 'range' || $request->input('fromDate')){
                    return $query->whereBetween($column, [$request->input('fromDate'), $request->input('toDate')]);
                }
                else if ($request->input('period') == 'monthly'){
                    $date_range = \App\Lookup::date_range_month($request->input('year'), $request->input('month'));
                    return $query->whereBetween($column, $date_range);
                }
                else if ($request->input('period') == 'quarterly'){
                    $quarters = ['Q1' => [1, 3], 'Q2' => [4, 6], 'Q3' => [7, 9], 'Q4' => [10, 12]];
                    $year = $request->input('year');
                    $q = $quarters[$request->input('quarter')];
                    $date_range = \App\Lookup::get_date_range($year, $q[0], $year, $q[1]);
                    return $query->whereBetween($column, $date_range);
                }
                else if ($request->input('period') == 'annually'){
                    return $query->whereBetween($column, \App\Lookup::date_range_month($request->input('year')));
                }
            }
        };
    }

    public function divisions_filter($request)
    {
        $param = $column = null;
        if ($request->input('category') == 'county') {
            $param = $request->input('county');
            $column = 'view_facilitys.county_id';
        } else if ($request->input('category') == 'subcounty') {
            $param = $request->input('district');
            $column = 'view_facilitys.subcounty_id';
        } else if ($request->input('category') == 'facility') {
            $param = $request->input('facility');
            $column = 'view_facilitys.id';
        } else if ($request->input('category') == 'partner') {
            $param = $request->input('partner');
            $column = 'view_facilitys.partner_id';
        }

        return function($query) use($param, $column){
            if(!$param) return null;
            if(is_array($param)) return $query->whereIn($column, $param);
            return $query->where($column, $param);          
        };
    }

    public function get_name($title = '', $request)
    {
        $param = $column = $table = $division = null;
        if ($request->input('category') == 'county') {
            $param = $request->input('county');
            $table = 'countys';
            $division = 'County';
        } else if ($request->input('category') == 'subcounty') {
            $param = $request->input('district');
            $table = 'districts';
            $division = 'Subcounty';
        } else if ($request->input('category') == 'facility') {
            $param = $request->input('facility');
            $table = 'facilitys';
            $division = 'Facility';
        } else if ($request->input('category') == 'partner') {
            $param = $request->input('partner');
            $table = 'partners';
            $division = 'Partner';
        }

        if($request->input('category') != 'overall'){
            $title .= ' overall ';
            if(is_array($param)){
                $names = DB::table($table)->whereIn('id', $param)->get()->pluck('name')->toArray();
                $title .= implode(',', $names).' '. str_plural($division);
            }
            else{
                $names = DB::table($table)->where('id', $param)->first()->name;
                $title .= DB::table($table)->where('id', $param)->first()->name .' '. $division;                
            }
        }

        $period = $request->input('period');

        if(!$period || $period == 'range'){
            $title  .= date('d-M-Y', strtotime($request->input('fromDate')))." - ".date('d-M-Y', strtotime($request->input('toDate')));
        }else if($period == 'monthly'){
            $title .= date("F", mktime(null, null, null, $request->input('month'))).' - '.$request->input('year');
        }else if ($period == 'quarterly'){
            $title .= $request->input('quarter').' - '.$request->input('year');
        }else if ($request->input('period') == 'annually') {
            $title .= $request->input('year');            
        }
        return $title;
    }
}