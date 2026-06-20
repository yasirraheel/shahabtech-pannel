@extends($activeTemplate . 'layouts.frontend')

@section('content')
<section class="pricing-section py-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <h2 class="section-title">@lang('Choose Your Access Plan')</h2>
                <p class="section-desc text-muted">@lang('Unlock premium platforms and level up your business with our tailored subscription plans.')</p>
                
                @auth
                    <div class="mt-4">
                        <div class="d-inline-flex align-items-center gap-2 bg--dark px-3 py-2 rounded">
                            <span class="text--white">
                                <i class="las la-wallet text--base"></i> @lang('Current Balance'): <strong>{{ showAmount(auth()->user()->balance) }}</strong>
                            </span>
                            <a href="{{ route('user.deposit.index') }}" class="btn btn-sm btn--base">
                                <i class="las la-plus"></i> @lang('Deposit')
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
        
        <div class="row justify-content-center g-4">
            @forelse ($plans as $plan)
                <div class="col-xl-4 col-md-6 col-sm-10">
                    <div class="card custom--card h-100">
                        <div class="card-header text-center pt-4 pb-3">
                            <h3 class="card-title">{{ __($plan->name) }}</h3>
                            <h2 class="plan-price my-3 text--base">
                                {{ showAmount($plan->price) }}
                            </h2>
                        </div>

                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                @if($plan->features)
                                    @foreach($plan->features as $feature)
                                        <li class="list-group-item d-flex align-items-center bg-transparent">
                                            <i class="las la-check-circle text--success fs-4 me-2"></i> {{ __($feature) }}
                                        </li>
                                    @endforeach
                                @else
                                    <li class="list-group-item d-flex align-items-center bg-transparent">
                                        <i class="las la-check-circle text--success fs-4 me-2"></i> @lang('Access to premium platforms')
                                    </li>
                                    <li class="list-group-item d-flex align-items-center bg-transparent">
                                        <i class="las la-check-circle text--success fs-4 me-2"></i> @lang('One-click auto login extension')
                                    </li>
                                @endif

                                @if($plan->included_resources && count($plan->included_resources) > 0)
                                    @foreach($plan->included_resources as $resource)
                                        <li class="list-group-item d-flex align-items-center bg-transparent">
                                            <i class="las la-check-circle text--success fs-4 me-2"></i> {{ $resource }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        
                        <div class="card-footer pb-4 pt-0 bg-transparent border-0">
                            @auth
                                @if(auth()->user()->plan_id == $plan->id)
                                    <button class="btn btn--success w-100" disabled>
                                        <i class="las la-check"></i> @lang('Current Plan')
                                    </button>
                                @else
                                    <button type="button" class="btn btn--base w-100 confirmationBtn" data-action="{{ route('user.plan.subscribe', $plan->id) }}" data-question="@lang('Are you sure you want to purchase this plan for ' . showAmount($plan->price) . '?')">
                                        <i class="las la-shopping-cart"></i> @lang('Subscribe Now')
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('user.login') }}" class="btn btn--outline-base w-100">
                                    @lang('Login to Subscribe')
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="card custom--card">
                        <div class="card-body py-5">
                            <i class="las la-box-open mb-3" style="font-size: 4rem; color: #888;"></i>
                            <h4 class="text-muted">@lang('No subscription plans available right now.')</h4>
                            <p class="text-muted">@lang('Please check back later.')</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

@auth
    <x-confirmation-modal addClass="custom--modal" :customButton=true />
@endauth

@endsection

@push('style')
<style>
    .product-item:hover {
        transform: translateY(-10px);
        border-color: var(--base-color) !important;
        box-shadow: 0 10px 30px rgba(108, 99, 255, 0.1);
    }
</style>
@endpush
