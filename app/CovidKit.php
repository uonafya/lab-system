<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidKit extends BaseModel
{
    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine', 'id');
    }

    public function details()
    {
        return $this->hasMany(CovidConsumptionDetail::class, 'kit_id', 'id');
    }

    public function computekitsUsed($tests)
    {
    	if ($tests == 0 || $this->calculated_pack_size == NULL)
    		return 0;
    	
        if ($this->machine == 2) // Use the Abbott calculator
            return round((($this->calculated_pack_size * ceil($tests/94))/$this->pack_size), 2);

    	return (int)ceil(($tests + (($tests/94) * 2))/$this->calculated_pack_size);
    	// return $tests;
    }

    public function beginingbalance($date)
    {
    	$balance = 0;
        $last_week = date('Y-m-d', strtotime('-7 days', strtotime($date)));
    	$last_week_consumption = CovidConsumption::whereDate('start_of_week', $last_week)->get();
    	
    	if (!$last_week_consumption->isEmpty()){
    		$details = $last_week_consumption->first()->details->where('kit_id', $this->id);
    		if (!$details->isEmpty()){
    			$balance = $details->first()->ending;
    		}
    	}
    								
    	return $balance;
    }

    public function specific_details($consumption_id)
    {
        return $this->details->where('consumption_id', $consumption_id)->first();
    }

 //    private function getPreviousWeek()
 //    {
 //    	$date = strtotime('-14 days', strtotime(date('Y-m-d')));
 //    	return $this->getStartAndEndDate(date('W', $date),
 //    							date('Y', $date));
 //    }

 //    private function getStartAndEndDate($week, $year) {
	// 	$dto = new \DateTime();
	// 	$dto->setISODate($year, $week);
	// 	$ret['week_start'] = $dto->format('Y-m-d');
	// 	$dto->modify('+6 days');
	// 	$ret['week_end'] = $dto->format('Y-m-d');
	// 	$ret['week'] = date('W', strtotime($ret['week_start']));
	// 	return (object)$ret;
	// }

    public function updateAbbott()
    {
        $updates = [
            ['material_no' => '09N77-090', 'pack_size' => 96, 'calculated_pack_size' => 96],
            ['material_no' => '09N77-080', 'pack_size' => 16, 'calculated_pack_size' => 2],
            ['material_no' => '4J71-10', 'pack_size' => 2304, 'calculated_pack_size' => 841],
            ['material_no' => '4J71-17', 'pack_size' => 2304, 'calculated_pack_size' => 96],
            ['material_no' => '4J71-80', 'pack_size' => 150, 'calculated_pack_size' => 1],
            ['material_no' => '4J71-30', 'pack_size' => 32, 'calculated_pack_size' => 3],
            ['material_no' => '4J71-20', 'pack_size' => 2000, 'calculated_pack_size' => 96],
            ['material_no' => '4J71-60', 'pack_size' => 90, 'calculated_pack_size' => 6],
            ['material_no' => '4J71-45', 'pack_size' => 50, 'calculated_pack_size' => 1],
            ['material_no' => '4J71-70', 'pack_size' => 20, 'calculated_pack_size' => 1],
            ['material_no' => '4J71-75', 'pack_size' => 100, 'calculated_pack_size' => 1],
            ['material_no' => '06K12-24',  'pack_size' => 96, 'calculated_pack_size' => 96]
        ];

        foreach ($updates as $key => $updatekit) {
            $kit = $this->where('material_no', $updatekit['material_no'])->first();
            if ($kit) {
                $kit->pack_size = $updatekit['pack_size'];
                $kit->calculated_pack_size = $updatekit['calculated_pack_size'];
                $kit->save();
            }
        }
    }
}
