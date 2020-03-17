<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\UlizaPage;

class UlizaController extends Controller
{

	public function home()
	{
		$page = UlizaPage::where('link', 'home')->first();
		$page2  = UlizaPage::where('link', 'home2')->first();
		return view('uliza.uliza', compact('page', 'page2'));
	}

	public function uliza()
	{
		$page = UlizaPage::where('link', 'uliza')->first();
		return view('uliza.uliza', compact('page'));
	}

	public function ushauri()
	{
		$page = UlizaPage::where('link', 'ushauri')->first();
		return view('uliza.uliza', compact('page'));
	}

	public function trainsmart()
	{
		$page = UlizaPage::where('link', 'trainsmart')->first();
		return view('uliza.uliza', compact('page'));
	}

	public function echo_page()
	{
		$page = UlizaPage::where('link', 'echo')->first();
		return view('uliza.uliza', compact('page'));
	}

	public function faqs()
	{
		$page = UlizaPage::where('link', 'faqs')->first();
		return view('uliza.uliza', compact('page'));
	}

	public function contactus()
	{
		$page = UlizaPage::where('link', 'contactus')->first();
		return view('uliza.uliza', compact('page'));
	}


	public function pages()
	{
		$pages = UlizaPage::all();
		return view('uliza.pages', compact('pages'));		
	}



	public function clinicalform()
	{
		$reasons = DB::table('uliza_reasons')->get();
		return view('uliza.clinicalform', compact('reasons'));		
	}
}
