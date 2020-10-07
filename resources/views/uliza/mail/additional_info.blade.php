<p>
	Dear Sir/Madam,
</p>
<p>
	Please note that we have received your clinical summary CCC#: {{ $uliza_clinical_form->cccno ?? '' }} , Nat#: {{ $uliza_clinical_form->nat_number ?? '' }} for review.
</p>
<p>
	Additional information is however required before the request can be reviewed.
</p>
<p>
	<a href="{{ url('uliza-form/' . $uliza_clinical_form->id . '/edit') }}">Click here</a> to access the record for editting.
</p>
<p>
	Kind Regards, <br />
	Uliza-NASCOP Secretariat
</p>
<p>
	<i> Please do not respond to the message, it is auto-generated. </i>
</p>