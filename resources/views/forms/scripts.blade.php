<!-- Jquery Validate -->
<script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>

{{ $js_scripts ?? '' }}

<script type="text/javascript">
    $(document).ready(function(){
    	$(".form-control").attr('autocomplete', 'off');

        $("select").select2();

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
            {{ $val_rules ?? '' }}
        });

        $("#sampleSearch").select2({
                placeholder: "Search Sample",
                width: '120px',
                ajax: {
                    url: "{{ url('/sample/new_patient') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                      q: params.term, // search term
                      page: params.page
                  };
              },
              processResults: function (data, params) {
                console.log(params);
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
              },
              cache: false
            },
            // escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            minimumInputLength: 1,
            templateResult: formatRepo,
            templateSelection: formatRepoSelection
        });

        function formatRepo (repo) {
            if (repo.loading) return repo.text;
            return repo.desc;
        }

        function formatRepoSelection (repo) {
            return repo.desc || repo.text;
        }
        
        {{ $slot }}

    });

</script>
