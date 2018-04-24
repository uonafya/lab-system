<script type="text/javascript">
	$(document).ready(function(){

		set_select("batch_search", "{{ url('/batch/search') }}", 1, "Search for batch");
		set_select("viralbatch_search", "{{ url('/viralbatch/search') }}", 1, "Search for batch");
		set_select("sidebar_batch_search", "{{ url('/batch/search') }}", 1, "Search for batch");
		set_select("sidebar_viralbatch_search", "{{ url('/viralbatch/search') }}", 1, "Search for batch");

		set_select_patient("patient_search", "{{ url('/patient/search') }}", 2, "Search for patient");
		set_select_patient("viralpatient_search", "{{ url('/viralpatient/search') }}", 2, "Search for patient");
		set_select_patient("sidebar_patient_search", "{{ url('/patient/search') }}", 2, "Search for patient");
		set_select_patient("sidebar_viralpatient_search", "{{ url('/viralpatient/search') }}", 2, "Search for patient");

		set_select("worksheet_search", "{{ url('/worksheet/search') }}", 1, "Search for worksheet");
		set_select("viralworksheet_search", "{{ url('/viralworksheet/search') }}", 1, "Search for worksheet");
		set_select("sidebar_worksheet_search", "{{ url('/worksheet/search') }}", 1, "Search for worksheet");
		set_select("sidebar_viralworksheet_search", "{{ url('/viralworksheet/search') }}", 1, "Search for worksheet");

		set_select_facility("facility_search", "{{ url('/facility/search') }}", 3, "Search for facility");
		set_select_facility("sidebar_facility_search", "{{ url('/facility/search') }}", 3, "Search for facility");

		set_select("sidebar_labID_search", "{{ url('sample/search') }}", 1, "Search by Lab ID");
		set_select("sidebar_virallabID_search", "{{ url('viralsample/search') }}", 1, "Search by Lab ID");

		set_select_facility("report_facility_search", "{{ url('facility/search') }}", 3, "Search for facility", false);
		set_select_facility("report_district_search", "{{ url('district/search') }}", 3, "Search for Sub-County", false)
		set_select_facility("report_county_search", "{{ url('county/search') }}", 1, "Search for County", false);
		set_select_facility("report_province_search", "{{ url('province/search') }}", 1, "Search for Province", false)

		// {{ url('') }}

	});
	
	function set_select(div_name, url, minimum_length, placeholder) {
		div_name = '#' + div_name;		

		$(div_name).select2({
			minimumInputLength: minimum_length,
			placeholder: placeholder,
			ajax: {
				delay	: 100,
				type	: "POST",
				dataType: 'json',
				data	: function(params){
					return {
						search : params.term
					}
				},
				url		: function(params){
					params.page = params.page || 1;
					return  url + "?page=" + params.page;
				},
				processResults: function(data, params){
					return {
						results 	: $.map(data.data, function (row){
							return {
								text	: row.id,
								id		: row.id		
							};
						}),
						pagination	: {
							more: data.to < data.total
						}
					};
				}
			}
		});
		set_change_listener(div_name, url);	
	}
	
	function set_select_patient(div_name, url, minimum_length, placeholder) {
		div_name = '#' + div_name;		

		$(div_name).select2({
			minimumInputLength: minimum_length,
			placeholder: placeholder,
			ajax: {
				delay	: 100,
				type	: "POST",
				dataType: 'json',
				data	: function(params){
					return {
						search : params.term
					}
				},
				url		: function(params){
					params.page = params.page || 1;
					return  url + "?page=" + params.page;
				},
				processResults: function(data, params){
					return {
						results 	: $.map(data.data, function (row){
							return {
								text	: row.patient,
								id		: row.id		
							};
						}),
						pagination	: {
							more: data.to < data.total
						}
					};
				}
			}
		});
		set_change_listener(div_name, url);	
	}

	function set_select_facility(div_name, url, minimum_length, placeholder, create_listener=true) {
		div_name = '#' + div_name;		

		$(div_name).select2({
			minimumInputLength: minimum_length,
			placeholder: placeholder,
			ajax: {
				delay	: 100,
				type	: "POST",
				dataType: 'json',
				data	: function(params){
					return {
						search : params.term
					}
				},
				url		: function(params){
					params.page = params.page || 1;
					return  url + "?page=" + params.page;
				},
				processResults: function(data, params){
					return {
						results 	: $.map(data.data, function (row){
							return {
								text	: row.facilitycode + ' - ' + row.name,
								id		: row.id		
							};
						}),
						pagination	: {
							more: data.to < data.total
						}
					};
				}
			}
		});

		if(create_listener){
			set_change_listener(div_name, url);
		}			
	}

	function set_change_listener(div_name, url)
	{
		url = url.substring(0, url.length -6);
		$(div_name).change(function(){
			var val = $(this).val();
			window.location.href = url + val;
		});	
	}


	{{--
		$('#batch_search').select2({
			minimumInputLength: 1,
			placeholder: 'Search for the batch',
			ajax: {
				delay	: 100,
				type	: "POST",
				dataType: 'json',
				data	: function(params){
					return {
						search : params.term
					}
				},
				url		: function(params){
					params.page = params.page || 1;
					return "{{ url('/batch/search') }}?page=" + params.page;
				},
				processResults: function(data, params){
					console.log(data);
					return {
						results 	: $.map(data.data, function (row){
							return {
								text	: row.id,
								id		: row.id		
							};
						}),
						pagination	: {
							more: data.to < data.total
						}
					};
				}
			}
		});
	--}}
</script>