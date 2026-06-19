@php
    $subscribeContent = getContent('subscribe.content', true);
@endphp
<div class="cta-section">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-6">
                <div class="cta-right">
                    <h3 class="cta-right__title mb-2">
                        {{ __(@$subscribeContent->data_values->heading) }}
                    </h2>
                    <p class="cta-right__desc"> {{ __(@$subscribeContent->data_values->subheading) }} </p>
                </div>
            </div>
            <div class="col-lg-6">
                <form class="call-to-action-form">
                    <div class="cta-left">
                        <div class="input-group mb-2">
                            <input class="form-control form--control email-input" name="email" type="email" placeholder="@lang('Enter Your Email')">
                            <button class="btn btn--base" type="submit"> @lang('Subscribe') </button>
                        </div>
                        <p class="cta-left__desc"> {{ __(@$subscribeContent->data_values->short_description) }} </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        (function($) {
            "use strict";

                $(document).on('submit', '.call-to-action-form', function(e) {
                    e.preventDefault();
                    var email = $('.email-input').val();
                    if (!email) {
                        notify('error', 'Email field is required');
                    } else {
                        $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            },
                            url: "{{ route('subscribe') }}",
                            method: "POST",
                            data: {
                                email: email
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('input[name="email"]').val('');
                                    notify('success', response.message);
                                } else {
                                    notify('error', response.error);
                                }

                            }
                        });
                    }
                });

        })(jQuery);
    </script>
@endpush
