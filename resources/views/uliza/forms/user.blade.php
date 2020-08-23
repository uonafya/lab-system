@extends('uliza.main_layout')

@section('content')


<div class="col-md-9">

	<div class="card mr-2">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-center p-1 text-white bg-success rounded box-shadow">
				<div class="text-center">
					<h6 class="mb-0 text-white">Technical Working Group</h6>
				</div>
			</div>
			<div class="card mt-1">
				<div class="card-body">
			        @if(isset($ulizaTwg))
			            <form method="POST" class="val-form" action='{{ url("/uliza-user/{$user->id}") }}' >
			            @method('PUT')
			        @else
			            <form method="POST" class="val-form" action='{{ url("/uliza-user/") }}'>
			        @endif
			        
			        @csrf
			        	<input name="lab_id" type="hidden" value="0">

						<div class="form-row mb-3">
							<div class="col-md-3">
								<span class="input-group-text text-left"> User Type: </span>
							</div>
							<select class="form-control col-md-9 select2" name="user_type_id">
								<option></option>
								@foreach($user_types as $user_type)
									<option value="{{ $user_type->id }}" @if(isset($user) && $user->user_type_id == $user_type->id) selected  @endif > {{ $user_type->user_type }} </option>
								@endforeach
							</select>
						</div>

						<div class="form-row mb-3">
							<div class="col-md-3">
								<span class="input-group-text text-left"> TWG: </span>
							</div>
							<select class="form-control col-md-9 select2" name="twg_id">
								<option></option>
								@foreach($twgs as $twg)
									<option value="{{ $user_type->id }}" @if(isset($user) && $user->twg_id == $twg->id) selected  @endif > {{ $twg->twg }} </option>
								@endforeach
							</select>
						</div>

						<div class="form-row mb-3">
							<div class="col-md-12 input-group required">
								<div class="input-group-prepend">
									<span class="input-group-text text-left">
										Email:
										<span style='color: #ff0000;'>*</span>
									</span>
								</div>
								<input class="form-control" name="email" required="required" type="email" value="{{ $user->email ?? '' }}">
							</div>
						</div>

						<div class="form-row mb-3">
							<div class="col-md-12 input-group required">
								<div class="input-group-prepend">
									<span class="input-group-text text-left">
										Surname:
										<span style='color: #ff0000;'>*</span>
									</span>
								</div>
								<input class="form-control" name="surname" required="required" type="text" value="{{ $user->surname ?? '' }}">
							</div>
						</div>
						<div class="form-row mb-3">
							<div class="col-md-12 input-group required">
								<div class="input-group-prepend">
									<span class="input-group-text text-left">
										Other Name:
										<span style='color: #ff0000;'>*</span>
									</span>
								</div>
								<input class="form-control" name="oname" required="required" type="text" value="{{ $user->oname ?? '' }}">
							</div>
						</div>

					  
						<div class="mb-3 float-right">
							<button class="btn btn-warning" type="submit" >Submit</button>
						</div>
					</form>					
				</div>
			</div>
			<br>
		</div>
	</div>
</div>

@endsection

@section('scripts')

    @component('/uliza/forms/scripts')
	@endcomponent
@endsection