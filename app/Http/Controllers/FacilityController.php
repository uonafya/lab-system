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
                            ->select('facilitys.id','facilitys.facilitycode','facilitys.name as facility','districts.name as district', 'countys.name as county','ftype','facilitys.telephone','facilitys.telephone2','facilitys.email','facilitys.contactperson','facilitys.PostalAddress','facilitys.contacttelephone','facilitys.contacttelephone2','facilitys.ContactEmail','partners.name as partner','facilitys.smsprinterphoneno','facilitys.G4Sbranchname','facilitys.G4Slocation')
                            ->join('view_facilitys', 'view_facilitys.ID', '=', 'facilitys.ID')
                            ->join('districts', 'districts.ID', '=', 'facilitys.district')
                            ->join('countys', 'countys.ID', '=', 'view_facilitys.county')
                            ->join('partners', 'partners.ID', '=', 'facilitys.partner')
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
        $columns = parent::_columnBuilder(['MFL Code','Facility Name','County','Sub-county','Facility Phone 1','Facility Phone 2','Facility Email','Facility SMS Printer','Contact Person Names','Contact Phone 1','Contact Phone 2','Contact Email','G4S Branch','Task']);
        
        return view('tables.facilities', ['row' => $table, 'columns' => $columns])->with('pageTitle', 'Facilities');
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
        $columns = parent::_columnBuilder(['#','MFL Code', 'Facility Name', 'County', 'Sub-county', 'Mobile', 'Email Address', 
                    'Contact Person', 'CP Telephone', 'CP Email', 'Supporting Partner']);
        
        return view('tables.facilities', ['row' => $table, 'columns' => $columns])->with('pageTitle', 'Facilities Served');
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
        
        $columns = parent::_columnBuilder(['#','MFL Code', 'Facility Name', 'County', 'Sub-county', 'Email Address', 
                    'Contact Person', 'CP Telephone', 'CP Email', 'Supporting Partner', 'SMS Printer No.', 'Service Provider']);
        
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
        
        return view('tables.facilities', ['row' => $table, 'columns' => $columns])->with('pageTitle', 'With SMS Printers');
    }

    public function withoutemails()
    {
        $columns = parent::_columnBuilder(['Facility Code', 'Facility Name', 'Mobile No', 'Email Address', 'Contact Person', 'CP Telephone', 'CP Email']);
        
        $facilities = DB::table('facilitys')
                            ->select('facilitys.id','facilitys.facilitycode','facilitys.name as facility','districts.name as district', 'countys.name as county', 'partners.name as partner','ftype','telephone','telephone2','facilitys.email','facilitys.contactperson','facilitys.PostalAddress','facilitys.contacttelephone','facilitys.contacttelephone2','facilitys.ContactEmail','partners.name as partner','facilitys.smsprinterphoneno','facilitys.serviceprovider')
                            ->join('view_facilitys', 'view_facilitys.ID', '=', 'facilitys.ID')
                            ->join('districts', 'districts.ID', '=', 'facilitys.district')
                            ->join('countys', 'countys.ID', '=', 'view_facilitys.county')
                            ->join('partners', 'partners.ID', '=', 'view_facilitys.partner')
                            ->where('facilitys.flag', '=', 1)
                            ->where('facilitys.lab', '=', Auth()->user()->lab_id)
                            ->whereRaw("((facilitys.email = '' and facilitys.ContactEmail ='') or (facilitys.email = '' and facilitys.ContactEmail is null) or (facilitys.email is null and facilitys.ContactEmail ='') or ((facilitys.email is null and facilitys.ContactEmail is null)))")
                            ->get();
        // dd($facilities);
        $table = '';
        foreach ($facilities as $key => $value) {
            $table .= '<tr>';
            $table .= '<td>'.$value->facilitycode.'</td>';
            $table .= '<td>'.$value->facility.'</td>';
            $table .= '<td>'.$value->telephone.'</td>';
            $table .= '<td>
                            <input type="hidden" name="id[]" value="'.$value->id.'">
                            <input type="text" class="form-control m-b input-sm" size="20" name="email[]" value="'.$value->email.'">
                        </td>';
            $table .= '<td><input type="text" class="form-control m-b input-sm" size="20" name="contactperson[]" value="'.$value->contactperson.'"></td>';
            $table .= '<td><input type="text" class="form-control m-b input-sm" size="20" name="contacttelephone[]" value="'.$value->contacttelephone.'"></td>';
            $table .= '<td><input type="text" class="form-control m-b input-sm" size="20" name="ContactEmail[]" value="'.$value->ContactEmail.'"></td>';
            $table .= '</tr>';
        }
        return view('tables.editable', ['row' => $table, 'columns' => $columns, 'function' => 'update'])->with('pageTitle', 'Without Emails');
    }

    public function withoutG4S()
    {
        $columns = parent::_columnBuilder(['Facility Code', 'Facility Name', 'County', 'Sub-county', 'G4S Branch Name', 'G4S Branch Location']);
        
        $facilities = DB::table('facilitys')
                            ->select('facilitys.id','facilitys.facilitycode','facilitys.name as facility','districts.name as district', 'countys.name as county', 'facilitys.G4Sbranchname','facilitys.G4Slocation')
                            ->join('view_facilitys', 'view_facilitys.ID', '=', 'facilitys.ID')
                            ->join('districts', 'districts.ID', '=', 'facilitys.district')
                            ->join('countys', 'countys.ID', '=', 'view_facilitys.county')
                            ->where('facilitys.flag', '=', 1)
                            ->where('facilitys.lab', '=', Auth()->user()->lab_id)
                            ->where('G4Sbranchname', '=', '')
                            ->where('G4Slocation', '=', '')
                            ->get();
        $table = '';
        foreach ($facilities as $key => $value) {
            $table .= '<tr>';
            $table .= '<td>'.$value->facilitycode.'</td>';
            $table .= '<td>'.$value->facility.'</td>';
            $table .= '<td>'.$value->county.'</td>';
            $table .= '<td>'.$value->district.'</td>';
            $table .= '<td>
                            <input type="hidden" name="id[]" value="'.$value->id.'">
                            <input type="text" class="form-control m-b input-sm" size="20" name="G4Sbranchname[]" value="'.$value->G4Sbranchname.'">
                        </td>';
            $table .= '<td><input type="text" class="form-control m-b input-sm" size="20" name="G4Slocation[]" value="'.$value->G4Slocation.'"></td>';
            $table .= '</tr>';
        }
        return view('tables.editable', ['row' => $table, 'columns' => $columns, 'function' => 'update'])->with('pageTitle', 'Without G4S');
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
        // return DB::table('facilitys')->select('facilitys.id','facilitys.facilitycode', 'facilitys.name as facility', 'districts.name as subcounty', 'countys.name as county', 'labs.name as lab','facilitys.physicaladdress', 'facilitys.PostalAddress','facilitys.telephone', 'facilitys.telephone2', 'facilitys.fax','facilitys.email', 'facilitys.contactperson', 'facilitys.ContactEmail', 'facilitys.contacttelephone', 'facilitys.contacttelephone2','facilitys.smsprinterphoneno', 'facilitys.G4Sbranchname', 'facilitys.G4Slocation', 'facilitys.G4Sphone1', 'facilitys.G4Sphone2', 'facilitys.G4Sphone3', 'facilitys.G4Sfax')
        //                 ->join('labs' ,'labs.id', '=', 'facilitys.lab')
        //                 ->join('districts', 'districts.id', '=', 'facilitys.district')
        //                 ->join('view_facilitys', 'view_facilitys.id', '=', 'facilitys.id')
        //                 ->join('countys', 'countys.id', '=', 'view_facilitys.county')
        //                 ->where('facilitys.id', '=', $id)
        //                 ->get();

        return DB::table('view_facilitys')->select('*', 'name as facility')->get();              
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
        // dd($facility);
        $facility = $this->getFacility($id);
        // dd($facility);
        return view('facilities.facility', ['facility' => $facility[0], 'disabled' => 'disabled'])->with('pageTitle', 'Facilities');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $facility = $this->getFacility($id);
        // dd($facility[0]);
        return view('facilities.facility', ['facility' => $facility[0], 'disabled' => ''])
                        ->with('edit', true)
                        ->with('pageTitle', 'Facilities');
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
        $success = 'Update was sucessfull';
        $failed = 'Updated failed try again later';
        // $this->validate($request, [
        //     'facilitycode' => 'required',
        //     'name' => 'required',
        //     'subconty' => 'required',
        //     'county' => 'required',
        //     'lab' => 'required',
        // ]);
        // dd($request->contacttelephone[199]);

        if (gettype($request->id) == "array") {//From the bulk update views
            if (isset($request->G4Sbranchname)||isset($request->G4Slocation)) {// update the G4S details
                foreach ($request->id as $key => $value) {
                    $data = ['G4Sbranchname' => $request->G4Sbranchname[$key],'G4Slocation' => $request->G4Slocation[$key]];

                    $update = DB::table('facilitys')
                        ->where('id', $request->id[$key])
                        ->update($data);
                }
                if ($update) {
                    return redirect()->route('withoutG4S')
                                ->with('success', $success);
                } else {
                    return redirect()->route('withoutG4S')
                                ->with('failed', $failed);
                }
            } else { //Updating the facilities contact details
                foreach ($request->id as $key => $value) {
                    $data = ['email' => $request->email[$key],'contactperson' => $request->contactperson[$key],
                                'contacttelephone' => $request->contacttelephone[$key],'ContactEmail' => $request->ContactEmail[$key]];
                    $update = DB::table('facilitys')
                        ->where('id', $request->id[$key])
                        ->update($data);
                }
                if ($update) {
                    return redirect()->route('withoutemails')
                                ->with('success', $success);
                } else {
                    return redirect()->route('withoutemails')
                                ->with('failed', $failed);
                }
            }
        } else {//From the single row update views
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
            if ($update) {
                return redirect()->route('facility.index')
                            ->with('success', $success);
            } else {
                return redirect()->route('facility.index')
                            ->with('failed', $failed);
            }
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

    public function search(Request $request)
    {
        $search = $request->input('search');
        $facilities = \App\ViewFacility::select('ID as id', 'name', 'facilitycode', 'county')
            ->whereRaw("(name like '%" . $search . "%' OR  facilitycode like '" . $search . "%')")
            ->paginate(10);
        return $facilities;
    }
}
