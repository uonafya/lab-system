<?php

use App\CovidKit;
use Illuminate\Database\Seeder;

class CovidKitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	CovidKit::truncate();
        $kits = [
        		['material_no' => '9175431190', 'product_description' => 'Cobas® SARS-CoV-2 Test',
        		'pack_size' => 192, 'calculated_pack_size' => 192, 'type' => 'Kit'],
        		['material_no' => '9175440190', 'product_description' => 'Cobas® SARS-CoV-2 Control Kit',
        		'pack_size' => 16, 'calculated_pack_size' => 1536, 'type' => 'Kit'],
        		['material_no' => '7002238190', 'product_description' => 'Cobas® Buffer Negative Control Kit',
        		'pack_size' => 16, 'calculated_pack_size' => 1536, 'type' => 'Kit'],
        		['material_no' => '5534917001', 'product_description' => 'Cobas OMNI Processing Plate',
        		'pack_size' => 32, 'calculated_pack_size' => 1536, 'type' => 'Kit'],
        		['material_no' => '5534925001', 'product_description' => 'Cobas OMNI Pipette Tips',
        		'pack_size' => 1536, 'calculated_pack_size' => 768, 'type' => 'Kit'],
        		['material_no' => '5534941001', 'product_description' => 'Cobas OMNI Amplification Plate',
        		'pack_size' => 32, 'calculated_pack_size' => 3072, 'type' => 'Kit'],
        		['material_no' => '6997503190', 'product_description' => 'Kit Cobas 6800/8800 Wash Reagent IVD (4.2 L)',
        		'pack_size' => 288, 'calculated_pack_size' => 288, 'type' => 'Kit'],
        		['material_no' => '6997511190', 'product_description' => 'Kit Cobas 6800/8800 SPEC DIL REAGENT IVD (4 x 875 mL)',
        		'pack_size' => 1152, 'calculated_pack_size' => 1152, 'type' => 'Kit'],
        		['material_no' => '6997538190', 'product_description' => 'Kit Cobas 6800/8800 LYS REAGENT IVD (4 x 875 mL)',
        		'pack_size' => 1152, 'calculated_pack_size' => 1152, 'type' => 'Kit'],
        		['material_no' => '6997546190', 'product_description' => 'Kit Cobas 6800/8800 MGP IVD',
        		'pack_size' => 480, 'calculated_pack_size' => 480, 'type' => 'Kit'],
        		['material_no' => '8030073001', 'product_description' => 'Solid Waste Bag Set of 20',
        		'pack_size' => 20, 'calculated_pack_size' => 7680, 'type' => 'Kit'],
        		['material_no' => '6438776001', 'product_description' => 'Cobas omni Secondary Tubes 13x75 (optional)*',
        		'pack_size' => 1500, 'calculated_pack_size' => 1500, 'type' => 'Kit'],
        		['material_no' => 'P1', 'product_description' => 'Swabs and viral transport medium', 'type' => 'Consumable'],
        		['material_no' => 'P2', 'product_description' => 'Extraction kits', 'type' => 'Consumable'],
        		['material_no' => 'P3', 'product_description' => 'Medical  disposable protective clothing', 'type' => 'Consumable'],
        		['material_no' => 'P4', 'product_description' => 'Face Shield', 'type' => 'Consumable'],
        		['material_no' => 'P5', 'product_description' => 'Medical gloves', 'type' => 'Consumable'],
        		['material_no' => 'P6', 'product_description' => 'Surgical Masks', 'type' => 'Consumable'],
        		['material_no' => 'P7', 'product_description' => 'Secondary sample collection (1 box= 1200 tubes)', 'type' => 'Consumable']
        	];
        foreach ($kits as $key => $kit)
        	CovidKit::create($kit);
    }
}
