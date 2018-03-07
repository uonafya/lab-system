<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Facility;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $facilities = DB::table('facilitys')
                            ->select('facilitys.id','facilitys.facilitycode','facilitys.name as facility','districts.name as district', 'countys.name as county','ftype','telephone','telephone2','facilitys.email','facilitys.contactperson','facilitys.PostalAddress','facilitys.contacttelephone','facilitys.contacttelephone2','facilitys.ContactEmail','partners.name as partner','facilitys.smsprinterphoneno','facilitys.G4Sbranchname','facilitys.G4Slocation')
                            ->join('view_facilitys', 'view_facilitys.ID', '=', 'facilitys.ID')
                            ->join('districts', 'districts.ID', '=', 'facilitys.district')
                            ->join('countys', 'countys.ID', '=', 'view_facilitys.county')
                            ->join('partners', 'partners.ID', '=', 'view_facilitys.partner')
                            ->where('facilitys.flag', '=', 1)
                            ->get();
        $table = '';
        foreach ($facilities as $key => $value) {
            $table .= '<tr>';
            $table .= '<td>'.$value->facilitycode.'</td>';
            $table .= '<td>'.$value->facility.'</td>';
            $table .= '<td>'.$value->county.'</td>';
            $table .= '<td>'.$value->district.'</td>';
            $table .= '<td>'.$value->telephone.'</td>';
            $table .= '<td>'.$value->telephone2.'</td>';
            $table .= '<td>'.$value->email.'</td>';
            $table .= '<td>'.$value->smsprinterphoneno.'</td>';
            $table .= '<td>'.$value->contactperson.'</td>';
            $table .= '<td>'.$value->contacttelephone.'</td>';
            $table .= '<td>'.$value->contacttelephone2.'</td>';
            $table .= '<td>'.$value->ContactEmail.'</td>';
            $table .= '<td>'.$value->G4Sbranchname.'</td>';
            $table .= '<td><a href="'.route('facility.show',$value->id).'">View</a>|<a href="'.route('facility.edit',$value->id).'">Edit</a></td>';
            $table .= '</tr>';
        }
        $columns = ['MFL Code','Facility Name','County','Sub-county','Facility Phone 1','Facility Phone 2','Facility Email','Facility SMS Printer','Contact Person Names','Contact Phone 1','Contact Phone 2','Contact Email','G4S Branch','Task'];
        $column = '<tr>';
        foreach ($columns as $key => $value) {
            $column .= '<th>'.$value.'</th>';
        }
        $column .= '</tr>';
        
        return view('tables.facilities', ['row' => $table, 'columns' => $column]);
    }

    public function served()
    {
        $facilities = DB::table('facilitys')
                            ->select('facilitys.id','facilitys.facilitycode','facilitys.name as facility','districts.name as district', 'countys.name as county','ftype','telephone','telephone2','facilitys.email','facilitys.contactperson','facilitys.PostalAddress','facilitys.contacttelephone','facilitys.contacttelephone2','facilitys.ContactEmail','partners.name as partner','facilitys.smsprinterphoneno','facilitys.G4Sbranchname','facilitys.G4Slocation')
                            ->join('view_facilitys', 'view_facilitys.ID', '=', 'facilitys.ID')
                            ->join('districts', 'districts.ID', '=', 'facilitys.district')
                            ->join('countys', 'countys.ID', '=', 'view_facilitys.county')
                            ->join('partners', 'partners.ID', '=', 'view_facilitys.partner')
                            ->where('facilitys.flag', '=', 1)
                            ->where('facilitys.lab', '=', Auth()->user()->lab_id)
                            ->get();
        $count = 0;
        $table = '';
        foreach ($facilities as $key => $value) {
            $count++;
            $table .= '<tr>';
            $table .= '<td>'.$count.'</td>';
            $table .= '<td>'.$value->facilitycode.'</td>';
            $table .= '<td>'.$value->facility.'</td>';
            $table .= '<td>'.$value->county.'</td>';
            $table .= '<td>'.$value->district.'</td>';
            $table .= '<td>'.$value->telephone.'</td>';
            $table .= '<td>'.$value->email.'</td>';
            $table .= '<td>'.$value->contactperson.'</td>';
            $table .= '<td>'.$value->contacttelephone.'</td>';
            $table .= '<td>'.$value->ContactEmail.'</td>';
            $table .= '<td>'.$value->partner.'</td>';
            $table .= '</tr>';
        }
        $columns = ['#','MFL Code', 'Facility Name', 'County', 'Sub-county', 'Mobile', 'Email Address', 
                    'Contact Person', 'CP Telephone', 'CP Email', 'Supporting Partner'];
        $column = '<tr>';
        foreach ($columns as $key => $value) {
            $column .= '<th>'.$value.'</th>';
        }
        $column .= '</tr>';
        
        return view('tables.facilities', ['row' => $table, 'columns' => $column]);
    }

    public function smsprinters()
    {
        $facilities = DB::table('facilitys')
                            ->select('facilitys.id','facilitys.facilitycode','facilitys.name as facility','districts.name as district', 'countys.name as county','ftype','telephone','telephone2','facilitys.email','facilitys.contactperson','facilitys.PostalAddress','facilitys.contacttelephone','facilitys.contacttelephone2','facilitys.ContactEmail','partners.name as partner','facilitys.smsprinterphoneno','facilitys.serviceprovider')
                            ->join('view_facilitys', 'view_facilitys.ID', '=', 'facilitys.ID')
                            ->join('districts', 'districts.ID', '=', 'facilitys.district')
                            ->join('countys', 'countys.ID', '=', 'view_facilitys.county')
                            ->join('partners', 'partners.ID', '=', 'view_facilitys.partner')
                            ->where('facilitys.flag', '=', 1)
                            ->where('facilitys.lab', '=', Auth()->user()->lab_id)
                            ->where('facilitys.smsprinter', '<>', '')
                            ->get();
        
        $columns = ['#','MFL Code', 'Facility Name', 'County', 'Sub-county', 'Email Address', 
                    'Contact Person', 'CP Telephone', 'CP Email', 'Supporting Partner', 'SMS Printer No.', 'Service Provider'];
        $column = '<tr>';
        foreach ($columns as $key => $value) {
            $column .= '<th>'.$value.'</th>';
        }
        $count = 0;
        $table = '';
        foreach ($facilities as $key => $value) {
            $count++;
            $table .= '<tr>';
            $table .= '<td>'.$count.'</td>';
            $table .= '<td>'.$value->facilitycode.'</td>';
            $table .= '<td>'.$value->facility.'</td>';
            $table .= '<td>'.$value->county.'</td>';
            $table .= '<td>'.$value->district.'</td>';
            $table .= '<td>'.$value->email.'</td>';
            $table .= '<td>'.$value->contactperson.'</td>';
            $table .= '<td>'.$value->contacttelephone.'</td>';
            $table .= '<td>'.$value->ContactEmail.'</td>';
            $table .= '<td>'.$value->partner.'</td>';
            $table .= '<td>'.$value->smsprinterphoneno.'</td>';
            $table .= '<td>'.$value->serviceprovider.'</td>';
            $table .= '</tr>';
        }
        $column .= '</tr>';
        return view('tables.facilities', ['row' => $table, 'columns' => $column]);
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

    public function getFacility($id)
    {
        return DB::table('facilitys')->select('facilitys.id','facilitys.facilitycode', 'facilitys.name as facility', 'districts.name as subcounty', 'countys.name as county', 'labs.name as lab','facilitys.physicaladdress', 'facilitys.PostalAddress','facilitys.telephone', 'facilitys.telephone2', 'facilitys.fax','facilitys.email', 'facilitys.contactperson', 'facilitys.ContactEmail', 'facilitys.contacttelephone', 'facilitys.contacttelephone2','facilitys.smsprinterphoneno', 'facilitys.G4Sbranchname', 'facilitys.G4Slocation', 'facilitys.G4Sphone1', 'facilitys.G4Sphone2', 'facilitys.G4Sphone3', 'facilitys.G4Sfax')
                        ->join('labs' ,'labs.id', '=', 'facilitys.lab')
                        ->join('districts', 'districts.id', '=', 'facilitys.district')
                        ->join('view_facilitys', 'view_facilitys.id', '=', 'facilitys.id')
                        ->join('countys', 'countys.id', '=', 'view_facilitys.county')
                        ->where('facilitys.id', '=', $id)
                        ->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $facility = Facility::find($id);
        $facility = self::getFacility($id);
        // dd($facility[0]);
        return view('facilities.facility', ['facility' => $facility[0], 'disabled' => 'disabled']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $facility = self::getFacility($id);
        // dd($facility[0]);
        return view('facilities.facility', ['facility' => $facility[0], 'disabled' => ''])
                        ->with('edit', true);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id=null)
    {
        $id = $request->id;
        // $this->validate($request, [
        //     'facilitycode' => 'required',
        //     'name' => 'required',
        //     'subconty' => 'required',
        //     'county' => 'required',
        //     'lab' => 'required',
        // ]);

        $data = ['facilitycode' => $request->facilitycode, 'name' => $request->name,
                'PostalAddress' => $request->PostalAddress, 'physicaladdress' => $request->physicaladdress,
                'telephone' => $request->telephone, 'fax' => $request->fax,
                'telephone2' => $request->telephone2, 'email' => $request->email,
                'smsprinterphoneno' => $request->smsprinterphoneno, 'contactperson' => $request->contactperson,
                'contacttelephone' => $request->contacttelephone, 'ContactEmail' => $request->ContactEmail,
                'contacttelephone2' => $request->contacttelephone2, 'G4Sbranchname' => $request->G4Sbranchname,
                'G4Sphone1' => $request->G4Sphone1, 'G4Sphone3' => $request->G4Sphone3,
                'G4Slocation' => $request->G4Slocation, 'G4Sphone2' => $request->G4Sphone2,'G4Sfax' => $request->G4Sfax];
        $update = DB::table('facilitys')
                    ->where('id', $id)
                    ->update($data);
                    // dd($update);
        if ($update) {
            return redirect()->route('facility.index')
                        ->with('success', 'Update was sucessfull');
        } else {
            return redirect()->route('facility.index')
                        ->with('failed', 'Updated failed try again later');
        }
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
}
