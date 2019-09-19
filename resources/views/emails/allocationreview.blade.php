<p>

	{{ $lab->labname }},

	<br />
	<br />

	{{$data->approved}} of your {{$data->month}}-{{$data->year}} have been 
	@if($approved){{'approved'}}@endif
	@if($rejected){{'rejected'}}@endif.

	@if($approved)
	The commodities will be the delivered between {{$from}} and {{$to}} by KEMSA.
	@endif
	@if($rejected)
	Kindly log into the system under the ‘Kits’ link to view the comments for your review then re-submit the
	allocation as soon as possible.
	@endif
	
	Regards

	------------------------------------------------------------------------------------------

	<br />  
	<br />  

	This email was automatically generated. Please do not respond to this or it will be ignored.

	<br />
	<br />

	EID/VL Support Team

</p>