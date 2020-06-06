<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use \App\MiscCovid;
use \App\Cd4Sample;
use Carbon\Carbon;
use Exception;

class Cd4WorksheetImport implements ToCollection
{
	protected $worksheet;
	protected $cancelled;
    protected $daterun;

	public function __construct($worksheet)
	{
        $this->worksheet = $worksheet;
	}

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
    	$worksheet = $this->worksheet;

        foreach ($collection as $key => $value) 
        {
            try {
                $daterun = Carbon::parse($value[23]);
                $daterun = $daterun->toDateString() ?? date('Y-m-d');                
            } catch (Exception $e) {
                $daterun = null;
            }
            
            $sample = Cd4Sample::find($value[4]);
            if($sample) {
                if ($value[9] != "") { 
                    $repeatt=2;
                } else { 
                    $repeatt=1;
                }

                if ($value[10] != "") { 
                    $repeatt=2;
                } else { 
                    $repeatt=1;
                }

                if ($value[11] != "") { 
                    $repeatt=2;
                } else { 
                    $repeatt=1;
                }
            
                if ($value[12] != "") { 
                    $repeatt=2;
                } else { 
                    $repeatt=1;
                }
           
                if ($value[21] != "") { 
                    $repeatt=2;
                } else { 
                    $repeatt=1;
                }

                $sample->THelperSuppressorRatio = $value[8];
                $sample->AVGCD3percentLymph = $value[9];
                $sample->AVGCD3AbsCnt = $value[10];
                $sample->AVGCD3CD4percentLymph = $value[11];
                $sample->AVGCD3CD4AbsCnt = $value[12];
                $sample->AVGCD3CD8percentLymph = $value[13];
                $sample->AVGCD3CD8AbsCnt = $value[14];
                $sample->AVGCD3CD4CD8percentLymph = $value[15];
                $sample->AVGCD3CD4CD8AbsCnt = $value[16];
                $sample->CD45AbsCnt = $value[21];
                $sample->datemodified = date('Y-m-d');
                $sample->datetested = $daterun;
                $sample->status_id = 4;
                $sample->repeatt = $repeatt;
                // dd($sample);
                $sample->save();
            }

        }
    }
}
