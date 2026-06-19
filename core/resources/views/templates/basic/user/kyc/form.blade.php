@extends($activeTemplate . 'layouts.master')
@section('content')
    <section class="py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title">@lang('KYC Form')</h5>
                        </div>
                        <div class="card-body">
                            <div class="account-form">
                                <form action="{{ route('user.kyc.submit') }}" method="post" enctype="multipart/form-data">
                                    @csrf

                                    <x-viser-form identifier="act" identifierValue="kyc" />

                                    <div>
                                        <button class="btn btn--base  w-100" type="submit">@lang('Submit')</button>
                                    </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
