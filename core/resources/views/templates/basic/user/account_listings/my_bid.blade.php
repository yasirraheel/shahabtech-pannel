@extends($activeTemplate . 'layouts.master')
@section('content')
    <section class="py-120">
        <div class="container">
            <div class="card custom--card">
                <div class="card-body p-0">
                    @include($activeTemplate . 'user.account_listings.listing_table')
                </div>
            </div>
        </div>
    </section>
@endsection
