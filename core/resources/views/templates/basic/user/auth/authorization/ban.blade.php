@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card custom--card">
                        <div class="card-body text-center">
                            <h4 class="text-center text--danger">@lang('YOU ARE BANNED')</h4>
                            <div class="d-flex gap-3 flex-wrap justify-content-center">
                                <p class="fw-bold mb-1">@lang('Reason'):</p>
                                <p>{{ __($user->ban_reason) }}</p>
                            </div>
                            <a href="{{ route('home') }}" class="btn btn--base mt-3">
                                <i class="las la-home"></i>@lang('Home')
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
