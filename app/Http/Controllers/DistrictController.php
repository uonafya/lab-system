<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $columns = parent::_columnBuilder(['#', 'Name', 'County', 'Province', 'Comment']);
        $districts = DB::table('districts')
                ->select('districts.id', 'districts.name as district', 'districts.comment', 'countys.name as county', 'provinces.name as province')
                ->join('countys', 'countys.id', '=', 'districts.county')
                ->join('provinces', 'provinces.id', '=', 'districts.province')
                ->get();
        $count = 0;
        $table = '';
        foreach ($districts as $key => $value) {
            $count ++;
            $table .= '<tr>';
            $table .= '<td>'.$count.'</td>';
            $table .= '<td>'.$value->district.'</td>';
            $table .= '<td>'.$value->county.'</td>';
            $table .= '<td>'.$value->province.'</td>';
            $table .= '<td>'.$value->comment.'</td>';
            $table .= '</tr>';
        }
        return view('tables.display', ['row' => $table, 'columns' => $columns])->with('pageTitle', 'Districts');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



    public function search(Request $request)
    {
        $search = $request->input('search');
        $district = DB::table('districts')->select('id', 'name')
            ->whereRaw("(name like '%" . $search . "%')")
            ->paginate(10);
        return $district;
    }
}
