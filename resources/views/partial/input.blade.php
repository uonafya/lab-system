				<?php $prop2 = $prop2 ?? null; ?>
				<div class="form-group {{ $form_class ?? '' }} ">
					<label class="col-sm-4 control-label">{{ $label }}
                        @if(isset($required) && $required)
                            <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                        @endif
					</label>
					<div class="col-sm-8">
						<input class="form-control" type="{{ $input_type ?? 'text' }}" 
							name="{{ $prop }}" 
							id="{{ $prop }}" 
							value="{{ $default_val ?? ?? $model->$prop ??'' }}" 
                            @if(isset($required) && $required)
                                required                                 
                            @endif
							{!! $attributes ?? '' !!} 
							@isset($is_number) number='number' @endisset 
							@isset($placeholder) placeholder="{{ $placeholder }}" @endisset 
						>
					</div>
				</div>