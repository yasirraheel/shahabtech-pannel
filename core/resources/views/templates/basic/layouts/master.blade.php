@extends($activeTemplate . 'layouts.app')
@section('panel')
    @if(gs('banner_status') && gs('banner_message'))
    <div class="notification-banner" style="background-color: #{{ gs('base_color') }}; color: #fff; padding: 10px 15px; text-align: center; position: relative; z-index: 9999;">
        <span style="font-size: 15px; font-weight: 500;">{{ gs('banner_message') }}</span>
        @if(gs('banner_cta_text') && gs('banner_cta_link'))
            <a href="{{ gs('banner_cta_link') }}" target="_blank" class="btn btn-sm btn-light ms-3" style="border-radius: 20px; padding: 3px 15px; font-weight: 600; font-size: 13px; text-decoration: none; color: #{{ gs('base_color') }}; background-color: #fff;">{{ gs('banner_cta_text') }}</a>
        @endif
    </div>
    @endif

    @include($activeTemplate . 'partials.header')
    
    <div class="root">
        @yield('content')
    </div>

    @include($activeTemplate . 'partials.footer')
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            var inputElements = $('[type=text],[type=password],select,textarea');
            $.each(inputElements, function(index, element) {
                element = $(element);
                if (element.hasClass('exclude')) {
                    return false;
                }
                element.closest('.form-group').find('label').attr('for', element.attr('name'));
                element.attr('id', element.attr('name'))
            });

            $.each($('input, select, textarea'), function(i, element) {
                if (element.hasAttribute('required')) {
                    $(element).closest('.form-group').find('label').addClass('required');
                }
            });

            $('.showFilterBtn').on('click', function() {
                $('.responsive-filter-card').slideToggle();
            });

            Array.from(document.querySelectorAll('table')).forEach(table => {
                let heading = table.querySelectorAll('thead tr th');
                Array.from(table.querySelectorAll('tbody tr')).forEach((row) => {
                    Array.from(row.querySelectorAll('td')).forEach((colum, i) => {
                        colum.setAttribute('data-label', heading[i].innerText)
                    });
                });
            });

            let disableSubmission = false;
            $('.disableSubmission').on('submit', function(e) {
                if (disableSubmission) {
                    e.preventDefault()
                } else {
                    disableSubmission = true;
                }
            });


        })(jQuery);
    </script>
@endpush
