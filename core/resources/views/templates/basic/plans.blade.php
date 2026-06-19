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
                        <span class="badge badge--primary px-3 py-2" style="font-size: 14px; background: rgba(108, 99, 255, 0.1); color: #6c63ff; border: 1px solid rgba(108, 99, 255, 0.3);">
                            <i class="las la-wallet"></i> @lang('Current Balance'): {{ showAmount(auth()->user()->balance) }} {{ gs('cur_text') }}
                        </span>
                        <a href="{{ route('user.deposit.index') }}" class="btn btn--sm btn--base ms-2" style="padding: 5px 15px; font-size: 12px;">
                            <i class="las la-plus"></i> @lang('Deposit Funds')
                        </a>
                    </div>
                @endauth
            </div>
        </div>
        
        <div class="row justify-content-center g-4">
            @forelse ($plans as $plan)
                <div class="col-xl-4 col-md-6 col-sm-10">
                    <div class="product-item" style="border: 1px solid #2a2a3c; transition: all 0.3s ease;">
                        <div class="product-item__wrapper p-4 text-center">
                            
                            <div class="plan-header mb-4">
                                <h3 class="plan-name text--base mb-2" style="font-size: 28px; font-weight: 700;">{{ __($plan->name) }}</h3>
                                <div class="plan-price">
                                    <span class="currency" style="font-size: 20px; vertical-align: top;">{{ gs('cur_sym') }}</span>
                                    <span class="amount text-white" style="font-size: 48px; font-weight: 700; line-height: 1;">{{ showAmount($plan->price) }}</span>
                                </div>
                            </div>

                            <div class="plan-body mb-5 text-start">
                                <ul class="list-group list-group-flush" style="background: transparent;">
                                    <li class="list-group-item d-flex align-items-center" style="background: transparent; border-color: rgba(255,255,255,0.05); color: #a0a0b0;">
                                        <i class="las la-check-circle text--success fs-4 me-2"></i> @lang('Access to premium platforms')
                                    </li>
                                    <li class="list-group-item d-flex align-items-center" style="background: transparent; border-color: rgba(255,255,255,0.05); color: #a0a0b0;">
                                        <i class="las la-check-circle text--success fs-4 me-2"></i> @lang('One-click auto login extension')
                                    </li>
                                    <li class="list-group-item d-flex align-items-center" style="background: transparent; border-color: rgba(255,255,255,0.05); color: #a0a0b0;">
                                        <i class="las la-check-circle text--success fs-4 me-2"></i> @lang('Secure cloud cookies')
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="plan-footer mt-auto">
                                @auth
                                    @if(auth()->user()->plan_id == $plan->id)
                                        <button class="btn w-100 py-3" style="background: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.3); font-weight: 600; cursor: default;" disabled>
                                            <i class="las la-check"></i> @lang('Current Plan')
                                        </button>
                                    @else
                                        <button type="button" class="btn btn--base w-100 py-3 confirmationBtn" data-action="{{ route('user.plan.subscribe', $plan->id) }}" data-question="@lang('Are you sure you want to purchase this plan for ' . showAmount($plan->price) . ' ' . gs('cur_text') . '?')">
                                            <i class="las la-shopping-cart"></i> @lang('Subscribe Now')
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ route('user.login') }}" class="btn w-100 py-3" style="background: rgba(255, 255, 255, 0.05); color: #fff; border: 1px solid rgba(255, 255, 255, 0.1);">
                                        @lang('Login to Subscribe')
                                    </a>
                                @endauth
                            </div>
                            
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
