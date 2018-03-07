@if($worksheet_status == 1)

	<a href="{{ url('viralworksheet/' . $worksheet_id) }}" title="Click to View Worksheet Details">
		Details
	</a> | 
	<a href="{{ url('viralworksheet/print/' . $worksheet_id) }}" title="Click to Download Worksheet" target='_blank'>
		Print
	</a> | 
	<a href="{{ url('viralworksheet/cancel/' . $worksheet_id) }}" title="Click to Cancel Worksheet" OnClick="return confirm('Are you sure you want to Cancel Worksheet $ID');">
		Cancel
	</a> | 
	<a href="{{ url('viralworksheet/upload/' . $worksheet_id) }}" title="Click to Update Results Worksheet" target='_blank'>
		Update
	</a>

@elseif($worksheet_status == 2)

	<a href="{{ url('viralworksheet/approve/' . $worksheet_id) }}" title="Click to Approve Samples Results in worksheet for Rerun or Dispatch" target='_blank'>
		Approve Worksheet Results
	</a> | 
	<a href="{{ url('viralworksheet/print/' . $worksheet_id) }}" title="Click to Download Worksheet" target='_blank'>
		Print
	</a>

@elseif($worksheet_status == 3)

	<a href="{{ url('viralworksheet/approve/' . $worksheet_id) }}" title="Click to view Samples in this Worksheet" target='_blank'>
		View Results
	</a> | 
	<a href="{{ url('viralworksheet/print/' . $worksheet_id) }}" title="Click to Download Worksheet" target='_blank'>
		Print
	</a>


@elseif($worksheet_status == 4)
	<a href="{{ url('viralworksheet/' . $worksheet_id) }}" title="Click to View Cancelled Worksheet Details">
		View Cancelled  Worksheet Details
	</a>

@else
@endif
