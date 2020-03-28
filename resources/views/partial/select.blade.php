				<?php
					if(!isset($prop2)) $prop2 = 'name';
				?>

				<div class="form-group">
					<label for="{{ $prop }}" class="col-sm-3 col-form-label"><div class="text-right">{{ $label }}</div></label>
					<div class="col-sm-9">
						<select class="form-control" id="{{ $prop }}" name="{{ $prop }}" @isset($required) required @endisset> 
							<option></option>
							@foreach($items as $item)
								<option value="{{ $item->id }}" @if((isset($model) && $model->$prop == $item->id) || (isset($default_val) && $default_val == $item->id)) selected @endif> {{ $item->$prop2 }} </option>
							@endforeach							
						</select>
					</div>

					@error($prop)
						<span class="alert alert-danger" role="alert">
							<strong>{{ $message }}</strong>
						</span>
					@enderror				
				</div>