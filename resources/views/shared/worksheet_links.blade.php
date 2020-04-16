@if($worksheet->status_id == 1)

	<a href="{{ url($worksheet->route_name . '/' . $worksheet->id) }}" title="Click to View Worksheet Details">
		Details
	</a> | 
	<a href="{{ url($worksheet->route_name . '/print/' . $worksheet->id) }}" title="Click to Download Worksheet" target='_blank'>
		Print
	</a> | 
	
	@if($worksheet->machine_type == 2)
		@if($worksheet->samples_no < 23)
			<a href="{{ url($worksheet->route_name . '/convert/' . $worksheet->id . '/1') }}" title="Convert Worksheet" target='_blank'>
				Convert to Roche/Taqman
			</a> |
		@endif
	@else
		<a href="{{ url($worksheet->route_name . '/convert/' . $worksheet->id . '/2') }}" title="Convert Worksheet" target='_blank'>
			Convert to Abbott
		</a> |
	@endif

	@if(in_array(env('APP_LAB'), [1, 4, 5]))
		<a href="{{ url($worksheet->route_name . '/labels/' . $worksheet->id) }}" title="Click to Print Worksheet Labels">
			Print Labels
		</a> | 
	@endif

	<a href="{{ url($worksheet->route_name . '/cancel/' . $worksheet->id) }}" title="Click to Cancel Worksheet" OnClick="return confirm('Are you sure you want to Cancel Worksheet {{$worksheet->id}}?');">		
		Cancel
	</a> | 

	<a href="{{ url($worksheet->route_name . '/upload/' . $worksheet->id) }}" title="Click to Update Results Worksheet" target='_blank'>
		Update
	</a>

@elseif($worksheet->status_id == 2)

	<a href="{{ url($worksheet->route_name . '/approve/' . $worksheet->id) }}" title="Click to Approve Samples Results in worksheet for Rerun or Dispatch" target='_blank'>
		Approve Worksheet Results
		@if(in_array(env('APP_LAB'), $double_approval) && $worksheet->datereviewed)
			(Second Review)
		@endif
	</a> | 
	@if($worksheet->failed)
		<a href="{{ url($worksheet->route_name . '/rerun_worksheet/' . $worksheet->id) }}" title="Click to Rerun Worksheet" target='_blank'> Rerun Worksheet </a> |
	@endif
	<a href="{{ url($worksheet->route_name . '/print/' . $worksheet->id) }}" title="Click to Download Worksheet" target='_blank'>
		Print
	</a>

@elseif($worksheet->status_id == 3 || $worksheet->status_id == 5)

	<a href="{{ url($worksheet->route_name . '/approve/' . $worksheet->id) }}" title="Click to view Samples in this Worksheet" target='_blank'>
		View Results
	</a> | 
	<a href="{{ url($worksheet->route_name . '/print/' . $worksheet->id) }}" title="Click to Download Worksheet" target='_blank'>
		Print
	</a>


@elseif($worksheet->status_id == 4)
	<a href="{{ url($worksheet->route_name . '/' . $worksheet->id) }}" title="Click to View Cancelled Worksheet Details">
		View Cancelled  Worksheet Details
	</a> |

	<a href="{{ url($worksheet->route_name . '/upload/' . $worksheet->id) }}" title="Click to Update Results Worksheet" target='_blank'>
		Update Results (In Case of Accidental Cancellation)
	</a> | 

	{!! $worksheet->delete_form !!}

@else
@endif
