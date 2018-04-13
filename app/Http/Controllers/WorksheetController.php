<?php

namespace App\Http\Controllers;

use App\Worksheet;
use App\Sample;
use App\User;
use App\Misc;
use App\Lookup;
use DB;
use Excel;
use Illuminate\Http\Request;

class WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state=0, $date_start=NULL, $date_end=NULL)
    {
        // $state = session()->pull('worksheet_state', null);
        $worksheets = Worksheet::with(['creator'])
        ->when($state, function ($query) use ($state){
            return $query->where('status_id', $state);
        })
        ->when($date_start, function($query) use ($date_start, $date_end){
            if($date_end)
            {
                return $query->whereDate('worksheets.created_at', '>=', $date_start)
                ->whereDate('worksheets.created_at', '<=', $date_end);
            }
            return $query->whereDate('worksheets.created_at', $date_start);
        })
        ->orderBy('worksheets.created_at', 'desc')
        ->get();

        $samples = $this->get_worksheets();
        $data = Lookup::worksheet_lookups();

        $worksheets->transform(function($worksheet, $key) use ($samples, $data){
            $status = $worksheet->status_id;
            $total = $worksheet->sample_count;

            if($status == 2 || $status == 3){
                $neg = $samples->where('worksheet_id', $worksheet->id)->where('result', 1)->first()->totals ?? 0;
                $pos = $samples->where('worksheet_id', $worksheet->id)->where('result', 2)->first()->totals ?? 0;
                $failed = $samples->where('worksheet_id', $worksheet->id)->where('result', 3)->first()->totals ?? 0;
                $redraw = $samples->where('worksheet_id', $worksheet->id)->where('result', 5)->first()->totals ?? 0;
                $noresult = $samples->where('worksheet_id', $worksheet->id)->where('result', 0)->first()->totals ?? 0;
            }
            else{
                $neg = $pos = $failed = $redraw = $noresult = 0;

                if($status == 1){
                    $noresult = $worksheet->sample_count;
                }
            }
            $worksheet->neg = $neg;
            $worksheet->pos = $pos;
            $worksheet->failed = $failed;
            $worksheet->redraw = $redraw;
            $worksheet->noresult = $noresult;
            $worksheet->mylinks = $this->get_links($worksheet->id, $status);
            $worksheet->machine = $data['machines']->where('id', $worksheet->machine_type)->first()->output;
            $worksheet->status = $data['worksheet_statuses']->where('id', $status)->first()->output;


            return $worksheet;
        });

        // return view('tables.worksheets', ['worksheets' => $worksheets, 'myurl' => url('worksheet/index/' . $state . '/')]);

        // $table_rows = "";

        // foreach ($worksheets as $key => $worksheet) {
        //     $new_key = $key+1;
        //     $table_rows .= "<tr> <td>{$new_key}</td> <td>" . $worksheet->my_date_format('created_at') . "</td><td> " . $worksheet->creator->full_name . "</td><td>" . $this->mtype($worksheet->machine_type) . "</td><td>";
        //     $status = $worksheet->status_id;
        //     $table_rows .= $this->wstatus($status) . "</td><td>";

        //     if($status == 2 || $status == 3){
        //         $neg = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 1));
        //         $pos = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 2));
        //         $failed = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 3));
        //         $redraw = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 5));
        //         $noresult = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 0));

        //         $total = $neg + $pos + $failed + $redraw + $noresult;

        //     }
        //     else{
        //         $neg = $pos = $failed = $redraw = $noresult = $total = 0;

        //         if($status == 1){
        //             $noresult = $total = $this->checknull($samples->where('worksheet_id', $worksheet->id));
        //         }
        //     }

        //     $table_rows .= "{$pos}</td><td>{$neg}</td><td>{$failed}</td><td>{$redraw}</td><td>{$noresult}</td><td>{$total}</td><td>" . $worksheet->my_date_format('daterun') . "</td><td>" . $worksheet->my_date_format('dateuploaded') . "</td><td>" . $worksheet->my_date_format('datereviewed') . "</td><td>" . $this->get_links($worksheet->id, $status) . "</td></tr>";

        // }
        return view('tables.worksheets', ['worksheets' => $worksheets, 'myurl' => url('worksheet/index/' . $state . '/')])->with('pageTitle', 'Worksheets');
        // return view('tables.worksheetsdtsvr', ['myurl' => url('worksheet/index/' . $state . '/')])->with('pageTitle', 'Worksheets');
    }

    public function getworksheetserverside(Request $request)
    {
        $primaryKey = 'id';
        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array('db' => 'created_at', 'dt' => 0 ),
            array( 'db' => 'full_name',  'dt' => 1 ),
            array( 'db' => 'machine_type',   'dt' => 2 ),
            array( 'db' => 'status_id',     'dt' => 3 ),
            array( 'db' => 'pos',     'dt' => 4 ),
            array( 'db' => 'neg',     'dt' => 5 ),
            array( 'db' => 'failed',     'dt' => 6 ),
            array( 'db' => 'redraw',     'dt' => 7 ),
            array( 'db' => 'noresult',     'dt' => 8 ),
            array( 'db' => 'total',     'dt' => 9 ),
            array( 'db' => 'daterun', 'dt' => 10 ),
            array( 'db' => 'dateuploaded', 'dt' => 11 ),
            array( 'db' => 'datereviewed', 'dt' => 12 )

        );

        self::simple($_GET, $primaryKey, $columns);
    }

    static function simple ($request, $primaryKey, $columns) {
        $bindings = array();
        $worksheet_id = NULL;
        // Build the SQL query string from the request
        // $query = self::query();
        $where = self::filter( $request, $columns, $bindings );
        // $order = self::order( $request, $columns );
        // $limit = self::limit( $query, $request)->toSql();
        print_r($where);die();

        // $samples = Sample::selectRaw("count(*) as totals, worksheet_id, result")
        //     ->whereNotNull('worksheet_id')
        //     ->when($worksheet_id, function($query) use ($worksheet_id){                
        //         if (is_array($worksheet_id)) {
        //             return $query->whereIn('worksheet_id', $worksheet_id);
        //         }
        //         return $query->where('worksheet_id', $worksheet_id);
        //     })
        //     ->where('inworksheet', 1)
        //     ->where('receivedstatus', '!=', 2)
        //     ->groupBy('worksheet_id', 'result')
        //     ->toSql();

        // print_r($samples);die();
    }

    static function query($state=0, $date_start=NULL, $date_end=NULL)
    {
        $worksheets = Worksheet::with(['creator'])
        ->when($state, function ($query) use ($state){
            return $query->where('status_id', $state);
        })
        ->when($date_start, function($query) use ($date_start, $date_end){
            if($date_end)
            {
                return $query->whereDate('worksheets.created_at', '>=', $date_start)
                ->whereDate('worksheets.created_at', '<=', $date_end);
            }
            return $query->whereDate('worksheets.created_at', $date_start);
        });
        // ->orderBy('worksheets.created_at', 'desc')
        // ->get();
        return $worksheets;
    }


    /**
     * Searching / Filtering
     *
     * Construct the WHERE clause for server-side processing SQL query.
     *
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here performance on large
     * databases would be very poor
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @param  array $bindings Array of values for PDO bindings, used in the
     *    sql_exec() function
     *  @return string SQL where clause
     */
    
    static function filter ( $request, $columns, &$bindings )
    {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = self::pluck( $columns, 'dt' );

        if ( isset($request['search']) && $request['search']['value'] != '' ) {
            $str = $request['search']['value'];

            for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];
                if ( $requestColumn['searchable'] == 'true' ) {
                    $binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
                    print_r($binding);die();
                    $globalSearch[] = "`".$column['db']."` LIKE ".$binding;
                }
            }
        } 

        // Individual column filtering
        // Mostly the first time it loads with no data in the search input
        if ( isset( $request['columns'] ) ) {
            for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                $requestColumn = $request['columns'][$i];// Gets the column attributes sent by Datatables through the request
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );// Gets the column dt key value from the defined columns
                $column = $columns[ $columnIdx ]; // Gets the column data defined in our code

                $str = $requestColumn['search']['value'];// Gets the search value incase it is set mostly not set

                if ( $requestColumn['searchable'] == 'true' && $str != '' ) {
                    $binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
                    $columnSearch[] = "`".$column['db']."` LIKE ".$binding;
                } 
            }
        }

        // Combine the filters into a single string
        $where = '';

        if ( count( $globalSearch ) ) {
            $where = '('.implode(' OR ', $globalSearch).')';
        }

        if ( count( $columnSearch ) ) {
            $where = $where === '' ?
                implode(' AND ', $columnSearch) :
                $where .' AND '. implode(' AND ', $columnSearch);
        }

        if ( $where !== '' ) {
            $where = 'WHERE '.$where;
        }

        return $where;
    }

    static function limit ( $query, $request )
    {
        $limit = '';

        if ( isset($request['start']) && $request['length'] != -1 ) {
            $limit = $query->offset($request['start'])->limit($request['length']);
        }

        return $limit;
    }

    /**
     * Ordering
     *
     * Construct the ORDER BY clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL order by clause
     */
    static function order ( $request, $columns )
    {
        $order = '';

        if ( isset($request['order']) && count($request['order']) ) {
            $orderBy = array();
            $dtColumns = self::pluck( $columns, 'dt' );

            for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
                // Convert the column index into the column data property
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];

                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];

                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                        'ASC' :
                        'DESC';

                    $orderBy[] = '`'.$column['db'].'` '.$dir;
                }
            }

            $order = 'ORDER BY '.implode(', ', $orderBy);
        }

        return $order;
    }

    /**
     * Create a PDO binding key which can be used for escaping variables safely
     * when executing a query with sql_exec()
     *
     * @param  array &$a    Array of bindings
     * @param  *      $val  Value to bind
     * @param  int    $type PDO field type
     * @return string       Bound key to be used in the SQL where this parameter
     *   would be used.
     */
    static function bind ( &$a, $val, $type )
    {
        $key = ':binding_'.count( $a );

        $a[] = array(
            'key' => $key,
            'val' => $val,
            'type' => $type
        );

        return $key;
    }


    /**
     * Pull a particular property from each assoc. array in a numeric array, 
     * returning and array of the property values from each item.
     *
     *  @param  array  $a    Array to get data from
     *  @param  string $prop Property to read
     *  @return array        Array of property values
     */
    static function pluck ( $a, $prop )
    {
        $out = array();

        for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
            $out[] = $a[$i][$prop];
        }

        return $out;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($machine_type=2)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        if($machine == NULL || $machine->eid_limit == NULL)
        {
            return back();
        }

        $samples = Sample::selectRaw("samples.*, patients.patient, facilitys.name, batches.datereceived, batches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->join('patients', 'samples.patient_id', '=', 'patients.id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'desc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('samples.id', 'asc')
            ->limit($machine->eid_limit)
            ->get();

        // dd($samples);

        $count = $samples->count();

        if($count == $machine->eid_limit){
            return view('forms.worksheets', ['create' => true, 'machine_type' => $machine_type, 'samples' => $samples, 'machine' => $machine])->with('pageTitle', 'Create Worksheet');
        }

        return view('forms.worksheets', ['create' => false, 'machine_type' => $machine_type, 'count' => $count])->with('pageTitle', 'Create Worksheet');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $worksheet = new Worksheet;
        $worksheet->fill($request->except('_token'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $worksheet->machine_type)->first();

        $samples = Sample::selectRaw("samples.id, patient_id, samples.parentid, batches.datereceived, batches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result = 0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('samples.id', 'asc')
            ->limit($machine->eid_limit)
            ->get();

        if($samples->count() != $machine->eid_limit){
            $worksheet->delete();
            return back();
        }

        $sample_ids = $samples->pluck('id');

        DB::table('samples')->whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id, 'inworksheet' => true]);

        return redirect()->route('worksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $samples = Sample::where('worksheet_id', $worksheet->id)->with(['patient'])->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples];

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Worksheets');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Worksheet $worksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Worksheet $worksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Worksheet $worksheet)
    {
        // DB::table("samples")->where('worksheet_id', $worksheet->id)->update(['worksheet_id' => 0, 'inworksheet' => 0, 'result' => 0]);
        // $worksheet->status_id = 4;
        // $worksheet->save();

        // return redirect("/worksheet");
    }

    public function print(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $samples = Sample::where('worksheet_id', $worksheet->id)->with(['patient'])->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples, 'print' => true];

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Worksheets');
        }
    }

    public function cancel(Worksheet $worksheet)
    {
        DB::table("samples")->where('worksheet_id', $worksheet->id)->update(['worksheet_id' => 0, 'inworksheet' => 0, 'result' => 0]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        return redirect("/worksheet");
    }

    public function upload(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $users = User::where('user_type_id', '<', 5)->get();
        return view('forms.upload_results', ['worksheet' => $worksheet, 'users' => $users])->with('pageTitle', 'Worksheet Upload');
    }




    /**
     * Update the specified resource in storage with results file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function save_results(Request $request, Worksheet $worksheet)
    {
        $worksheet->fill($request->except(['_token', 'upload']));
        $file = $request->upload->path();
        $today = $dateoftest = date("Y-m-d");
        $positive_control;
        $negative_control;

        if($worksheet->machine_type == 2)
        {
            $dateoftest = $today;
            // config(['excel.import.heading' => false]);
            $data = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $check = array();

            // dd($data);

            $bool = false;
            $positive_control = $negative_control = "Passed";

            foreach ($data as $key => $value) {
                if($value[5] == "RESULT"){
                    $bool = true;
                    continue;
                }

                if($bool){
                    $sample_id = $value[1];
                    $interpretation = $value[5];
                    $error = $value[10];

                    switch ($interpretation) {
                        case 'Not Detected':
                            $result = 1;
                            break;
                        case 'HIV-1 Detected':
                            $result = 2;
                            break;
                        case 'Detected':
                            $result = 2;
                            break;
                        case 'Collect New Sample':
                            $result = 5;
                            break;
                        default:
                            $result = 3;
                            $interpretation = $error;
                            break;
                    }
                    

                    $data_array = ['datemodified' => $today, 'datetested' => $today, 'interpretation' => $interpretation, 'result' => $result];
                    $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    DB::table('samples')->where($search)->update($data_array);

                    $check[] = $search;

                    if($sample_id == "HIV_NEG"){
                        $negative_control = $value[5];
                    }
                    else if($sample_id == "HIV_HIPOS"){
                        $positive_control = $value[5];
                    }

                }

                if($bool && $value[5] == "RESULT") break;
            }

            if($positive_control == "Passed"){
                $pos_result = 6;
            }
            else{
                $pos_result = 7;
            }

            if($negative_control == "Passed"){
                $neg_result = 6;
            }
            else{
                $neg_result = 7;
            }
        }
        else
        {
            $handle = fopen($file, "r");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $interpretation = $data[8];
                $dateoftest=date("Y-m-d", strtotime($data[3]));

                $flag = $data[10];

                if($flag != NULL){
                    $interpretation = $flag;
                }

                if($interpretation == "Target Not Detected" || $interpretation == "Not Detected DBS")
                {
                    $result = 1;
                } 
                else if($interpretation == 1 || $interpretation == "1" || $interpretation == ">1" || $interpretation == ">1 " || $interpretation == "> 1" || $interpretation == "> 1 " || $interpretation == "1.00E+00" || $interpretation == ">1.00E+00" || $interpretation == ">1.00E+00 " || $interpretation == "> 1.00E+00")
                {
                    $result = 2;
                }
                else
                {
                    $result = 3;
                }

                $data_array = ['datemodified' => $today, 'datetested' => $dateoftest, 'interpretation' => $interpretation, 'result' => $result];

                $search = ['id' => $data[4], 'worksheet_id' => $worksheet->id];
                DB::table('samples')->where($search)->update($data_array);

                if($data[5] == "NC"){
                    // $worksheet->neg_control_interpretation = $interpretation;
                    $negative_control = $result;
                }
                if($data[5] == "LPC" || $data[5] == "PC"){
                    $positive_control = $result;
                }

            }
            fclose($handle);

            switch ($negative_control) {
                case 'Target Not Detected':
                    $neg_result = 1;
                    break;
                case 'Valid':
                    $neg_result = 6;
                    break;
                case 'Invalid':
                    $neg_result = 7;
                    break;
                case '5':
                    $neg_result = 5;
                    break;                
                default:
                    $neg_result = 3;
                    break;
            }

            if($positive_control == 1 || $positive_control == "1" || $positive_control == ">1" || $positive_control == "> 1 " || $positive_control == "> 1" || $positive_control == "1.00E+00" || $positive_control == ">1.00E+00" || $positive_control == "> 1.00E+00" || $positive_control == "> 1.00E+00 ")
            {
                $pos_result = 2;
            }
            else if($positive_control == "5")
            {
                $pos_result = 5;
            }
            else if($positive_control == "Valid")
            {
                $pos_result = 6;
            }
            else if($positive_control == "Invalid")
            {
                $pos_result = 7;
            }
            else
            {
                $pos_result = 3;
            }

        }

        DB::table('samples')->where(['worksheet_id' => $worksheet->id])->where('run', 0)->update(['run' => 1]);

        $worksheet->neg_control_interpretation = $negative_control;
        $worksheet->neg_control_result = $neg_result;

        $worksheet->pos_control_interpretation = $positive_control;
        $worksheet->pos_control_result = $pos_result;
        $worksheet->daterun = $dateoftest;
        $worksheet->save();

        $path = $request->upload->store('results/eid');

        $my = new Misc;
        $my->requeue($worksheet->id);

        return redirect('worksheet/approve/' . $worksheet->id)->with('pageTitle', 'Save Results');
    }

    public function approve_results(Worksheet $worksheet)
    {        
        $worksheet->load(['reviewer', 'creator', 'runner', 'sorter', 'bulker']);

        $results = DB::table('results')->get();
        $actions = DB::table('actions')->get();
        $samples = Sample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();

        $s = $this->get_worksheets($worksheet->id);

        $neg = $this->checknull($s->where('result', 1));
        $pos = $this->checknull($s->where('result', 2));
        $failed = $this->checknull($s->where('result', 3));
        $redraw = $this->checknull($s->where('result', 5));
        $noresult = $this->checknull($s->where('result', 0));

        $total = $neg + $pos + $failed + $redraw + $noresult;

        $subtotals = ['neg' => $neg, 'pos' => $pos, 'failed' => $failed, 'redraw' => $redraw, 'noresult' => $noresult, 'total' => $total];

        return view('tables.confirm_results', ['results' => $results, 'actions' => $actions, 'samples' => $samples, 'subtotals' => $subtotals, 'worksheet' => $worksheet, 'double_approval' => Lookup::$double_approval])->with('pageTitle', 'Approve Results');
    }

    public function approve(Request $request, Worksheet $worksheet)
    {
        $double_approval = Lookup::$double_approval;
        $samples = $request->input('samples');
        $batches = $request->input('batches');
        $results = $request->input('results');
        $actions = $request->input('actions');

        $today = date('Y-m-d');
        $approver = auth()->user()->id;

        $batch = array();
        $my = new Misc;

        foreach ($samples as $key => $value) {

            if(in_array(env('APP_LAB'), $double_approval) && $worksheet->reviewedby && !$worksheet->reviewedby2 && $worksheet->reviewedby != $approver){
                $data = [
                    'approvedby2' => $approver,
                    'dateapproved2' => $today,
                ];
            }
            else{
                $data = [
                    'approvedby' => $approver,
                    'dateapproved' => $today,
                ];
            }

            $data['result'] = $results[$key];
            $data['repeatt'] = $actions[$key];

            DB::table('samples')->where('id', $samples[$key])->update($data);

            if($actions[$key] == 1){
                $my->save_repeat($samples[$key]);
            }
        }

        if(in_array(env('APP_LAB'), $double_approval)){
            if($worksheet->reviewedby && $worksheet->reviewedby != $approver){
                $batch = collect($batches);
                $b = $batch->unique();
                $unique = $b->values()->all();

                foreach ($unique as $value) {
                    $my->check_batch($value);
                }

                $worksheet->status_id = 3;
                $worksheet->datereviewed2 = $today;
                $worksheet->reviewedby2 = $approver;
                $worksheet->save();

                return redirect('/batch/dispatch');                 
            }
            else{
                $worksheet->datereviewed = $today;
                $worksheet->reviewedby = $approver;
                $worksheet->save();

                return redirect('/worksheet');
            }
        }

        else{
            $batch = collect($batches);
            $b = $batch->unique();
            $unique = $b->values()->all();

            foreach ($unique as $value) {
                $my->check_batch($value);
            }

            $worksheet->status_id = 3;
            $worksheet->datereviewed = $today;
            $worksheet->reviewedby = $approver;
            $worksheet->save();

            return redirect('/batch/dispatch');            
        }
    }

    public function mtype($machine)
    {
        if($machine == 1){
            return "<strong> TaqMan </strong>";
        }
        else{
            return " <strong><font color='#0000FF'> Abbott </font></strong> ";
        }
    }

    public function wstatus($status)
    {
        switch ($status) {
            case 1:
                return "<strong><font color='#FFD324'>In-Process</font></strong>";
                break;
            case 2:
                return "<strong><font color='#0000FF'>Tested</font></strong>";
                break;
            case 3:
                return "<strong><font color='#339900'>Approved</font></strong>";
                break;
            case 4:
                return "<strong><font color='#FF0000'>Cancelled</font></strong>";
                break;            
            default:
                break;
        }
    }

    public function get_links($worksheet_id, $status)
    {
        if($status == 1)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to view Samples in this Worksheet' target='_blank'>Details</a> | "
                . "<a href='" . url('worksheet/print/' . $worksheet_id) . "' title='Click to Print this Worksheet' target='_blank'>Print</a> | "
                . "<a href='" . url('worksheet/cancel/' . $worksheet_id) . "' title='Click to Cancel this Worksheet' onClick=\"return confirm('Are you sure you want to Cancel Worksheet {$worksheet_id}\" >Cancel</a> | "
                . "<a href='" . url('worksheet/upload/' . $worksheet_id) . "' title='Click to Upload Results File for this Worksheet'>Update Results</a>";
        }
        else if($status == 2)
        {
            $d = "<a href='" . url('worksheet/approve/' . $worksheet_id) . "' title='Click to Approve Samples Results in worksheet for Rerun or Dispatch' target='_blank'> Approve Worksheet Results</a>";

        }
        else if($status == 3)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to view Samples in this Worksheet' target='_blank'>Details</a> | "
                . "<a href='" . url('worksheet/approve/' . $worksheet_id) . "' title='Click to View Approved Results & Action for Samples in this Worksheet' target='_blank'>View Results</a> | "
                . "<a href='" . url('worksheet/print/' . $worksheet_id) . "' title='Click to Print this Worksheet' target='_blank'>Print</a> ";

        }
        else if($status == 4)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to View Cancelled Worksheet Details' target='_blank'>Details</a> ";

        }
        return $d;
    }

    public function get_worksheets($worksheet_id=NULL)
    {
        $samples = Sample::selectRaw("count(*) as totals, worksheet_id, result")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){                
                if (is_array($worksheet_id)) {
                    return $query->whereIn('worksheet_id', $worksheet_id);
                }
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->where('inworksheet', 1)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('worksheet_id', 'result')
            ->get();

        return $samples;
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $worksheets = Worksheet::whereRaw("id like '" . $search . "%'")->paginate(10);
        return $worksheets;
    }

    public function checknull($var)
    {
        return $var->first()->totals ?? 0;
    }

}
