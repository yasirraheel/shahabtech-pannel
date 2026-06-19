@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $contactContent = getContent('contact.content', true)->data_values;
    @endphp

    <section class="contact-section py-120">
        <div class="container">
            <div class="row gy-4 flex-wrap-reverse">
                <div class="col-lg-8 col-md-7 pe-lg-5">
                    <div class="contactus-form">
                        <div class="contactus-form-header mb-4">
                            <h4 class="text--base mb-2"> {{ __(@$contactContent->heading_one) }} </h4>
                            <p>{{ __(@$contactContent->subheading_one) }}</p>
                        </div>
                        <form class="verify-gcaptcha disableSubmission" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form--label">@lang('Name')</label>
                                        <input class="form--control" name="name" type="text" value="{{ old('name', @$user->fullname) }}" @if ($user && $user->profile_complete) readonly @endif required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="form--label">@lang('Email')</label>
                                        <input class="form--control" name="email" type="email" value="{{ old('email', @$user->email) }}" @if ($user) readonly @endif required>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="form--label">@lang('Subject')</label>
                                        <input class="form--control" name="subject" type="text" value="{{ old('subject') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="form--label">@lang('Message')</label>
                                        <textarea class="form--control" name="message" wrap="off" required>{{ old('message') }}</textarea>
                                    </div>
                                </div>
                                @php
                                    $addLabelClass = 'form--label';
                                @endphp
                                <div class="col-12">
                                    <x-captcha :addLabelClass="$addLabelClass" />
                                </div>
                                <div class="col-sm-12">
                                    <button class="btn btn--base" type="submit">
                                        @lang('Submit')
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5">
                    <div class="contact-form-right">
                        <div class="contactus-form-header mb-4">
                            <h4 class="text--base mb-2"> {{ __(@$contactContent->heading_two) }}</h4>
                            <p> {{ __(@$contactContent->subheading_two) }}</p>
                        </div>
                        <div class="row gy-4 justify-content-center">
                            <div class="col-lg-12 col-md-12 col-sm-6">
                                <div class="contact-item">
                                    <div class="contact-item__icon">
                                        @php echo @$contactContent->email_icon @endphp
                                    </div>
                                    <div class="contact-item__content">
                                        <h5 class="contact-item__title">
                                            {{ __(@$contactContent->email_title) }}
                                        </h5>
                                        <a class="contact-item__desc" href="mailto:{{ @$contactContent->email }}">
                                            {{ @$contactContent->email }} </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-6">
                                <div class="contact-item">
                                    <div class="contact-item__icon">
                                        @php echo @$contactContent->phone_number_icon @endphp
                                    </div>
                                    <div class="contact-item__content">
                                        <h5 class="contact-item__title">
                                            {{ __(@$contactContent->phone_number_title) }} </h5>
                                        <a class="contact-item__desc" href="tel:{{ @$contactContent->phone_number }}"> {{ @$contactContent->phone_number }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if (@$sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection

@push('name')
    <script>
        (function($) {
            "use strict";
            let disableSubmission = false;
            $('.disableSubmission').on('submit', function(e) {
                console.log(disableSubmission);
                if (disableSubmission) {
                    e.preventDefault()
                } else {
                    disableSubmission = true;
                }
            });
        })(jQuery);
    </script>
@endpush
