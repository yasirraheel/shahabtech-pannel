@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="py-60">
        <div class="container">
            @php
                echo $cookie->data_values->description;
            @endphp
        </div>
    </section>
@endsection
