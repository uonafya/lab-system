@section('css_scripts')

<link href="{{ asset('css/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('css/select2/select2.min.css') }}" rel="stylesheet" type="text/css">

{{ $slot }}

@endsection
