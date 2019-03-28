<?php

use Illuminate\Database\Seeder;

class GeneralConsumablesSeeder extends Seeder
{
    public $consumables = [
        ['name' => 'FILTERED PIPETTE TIPS,  200UL(pack of 960)'],
        ['name' => 'FILTERED PIPETTE TIPS,  1000UL(pack of 960)'],
        ['name' => 'GLOVES LATEX EXAMINATION DISPOSABLE MEDIUM(pack of 50 pair)'],
        ['name' => 'TUBE CRYIOVIALS STERILE WITH SCREW CAP 2ML(pack of 1000s)'],
        ['name' => 'PLASMA PREPARATION TUBES (ppt tubes pack of 1000 tubes)'],
        ['name' => 'KIM WIPES'],
        ['name' => 'BENCH GUARDS'],
        ['name' => 'BIO HAZARD BAG (LARGE)'],
        ['name' => 'RNA AWAY'],
        ['name' => 'DNA AWAY'],
        ['name' => 'PASTEUR PIPETTE STERILE 3ML'],
        ['name' => 'LABORATORY COATS(Pack of 50s)'],
        ['name' => 'ETHANOL 2.5 L'],
        ['name' => 'DBS BUNDLES(50`S CARD)'],
        ['name' => 'DBS BUNDLES(20`S CARD)'],
        ['name' => 'NEEDLE HOLDERS (pack of 1000)'],
        ['name' => 'Needles pack of 480'],
        ['name' => 'Hypochloride 5L'],
        ['name' => 'GAUZE ROLLS'],
        ['name' => 'PHOSPHATE BUFFER SALINE (10X)'],
        ['name' => 'PHOSPHATE BUFFER SALINE (1X)'],
        ['name' => 'Falcon tubes'],
        ['name' => 'Marker pens'],
        ['name' => 'centrifuge tubes']
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->consumables as $consumable){
            \App\GeneralConsumable::create($consumable);
        }
    }
}