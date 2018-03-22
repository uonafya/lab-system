<!-- Jquery Validate -->
<script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>
<script src="{{ asset('js/toastr/toastr.min.js') }}"></script>

{{ $js_scripts or '' }}

<script type="text/javascript">
    $(document).ready(function(){
    	$(".form-control").attr('autocomplete', 'off');

        $("select").select2();

        setTimeout(function(){
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 4000
            };
            toastr.success("{{ session()->pull('toast_message', 'Please fill out the form correctly.') }}");
        });

        var msg;
        var dynamicErrorMsg = function () { return msg; }


        jQuery.validator.addMethod("lessThan", function(value, element, param) {

            var start = value;
            var finish = $( param[0] ).val();

            var s = start.split("-");
            var f = finish.split("-");

            // console.log("length is " + f.length);

            if(f.length < 3){
                return true;
            }
            else{
                var start_date = new Date(s[0], s[1], s[2]);
                var finish_date = new Date(f[0], f[1], f[2]);

                msg =  param[1] + " cannot be set to a date greater than " + param[2];

                return this.optional(element) || (start_date <= finish_date);

            }

            /*if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) > new Date($(params).val());
            }

            return isNaN(value) && isNaN($(params).val())
                || (Number(value) > Number($(params).val()));*/

        }, dynamicErrorMsg);

        $(".form-horizontal").validate({
            errorPlacement: function (error, element)
            {
                element.before(error);
            }
            {{ $val_rules or '' }}
        });

        {{ $slot }}

    });

</script>
