				<?php $prop2 = $prop2 ?? null; ?>
				<div class="form-group">
					<label class="col-sm-4 control-label">{{ $label }}
                        @if(isset($required) && $required)
                            <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                        @endif
					</label>
					<div class="col-sm-8">
						<input class="form-control" 
							type="{{ $input_type ?? 'text' }}" 
							name="{{ $prop }}" 
							id="{{ $prop }}" 
							value="{{ $model->$prop2->$prop ?? $model->$prop ?? $default_val ?? '' }}" 
							@isset($required) required @endisset 
							{!! $attributes ?? '' !!} 
							@isset($is_number) number='number' @endisset 
							@isset($placeholder) placeholder="{{ $placeholder }}" @endisset 
						>
					</div>
				</div>