@php
    $layout = auth()->check() ? $activeTemplate . 'layouts.master' : $activeTemplate . 'layouts.frontend';
@endphp
@extends($layout)

@section('content')
<div class="@if(!auth()->check()) py-120 @endif">
    <div class="@if(!auth()->check()) container @endif">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-4">
                <h4 class="text-white fw-bold mb-2">@lang('Choose Your Access Plan')</h4>
                <p class="text-muted mb-0" style="font-size: 14px;">@lang('Unlock premium platforms and level up your workflow with our tailored subscription plans.')</p>
            </div>
        </div>
        
        <div class="row justify-content-center g-4">
            @forelse ($plans as $plan)
                <div class="col-xl-4 col-md-6 col-sm-10">
                    <div class="card h-100" style="background: #111827; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; transition: transform 0.2s ease;">
                        <div class="card-header text-center pt-4 pb-3" style="background: #162032; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                            <h5 class="text-white mb-2" style="font-size: 17px; font-weight: 600;">{{ __($plan->name) }}</h5>
                            <h3 class="my-2 text--primary" style="font-size: 26px; font-weight: 700;">
                                {{ showAmount($plan->price) }}
                            </h3>
                        </div>

                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                @if($plan->features)
                                    @foreach($plan->features as $feature)
                                        <li class="list-group-item d-flex align-items-center bg-transparent border-0 px-0 py-2 text-white" style="font-size: 13.5px;">
                                            <i class="las la-check-circle text--success fs-5 me-2"></i> {{ __($feature) }}
                                        </li>
                                    @endforeach
                                @else
                                    <li class="list-group-item d-flex align-items-center bg-transparent border-0 px-0 py-2 text-white" style="font-size: 13.5px;">
                                        <i class="las la-check-circle text--success fs-5 me-2"></i> @lang('Access to premium platforms')
                                    </li>
                                    <li class="list-group-item d-flex align-items-center bg-transparent border-0 px-0 py-2 text-white" style="font-size: 13.5px;">
                                        <i class="las la-check-circle text--success fs-5 me-2"></i> @lang('One-click auto login extension')
                                    </li>
                                @endif

                                @if($plan->included_resources && count($plan->included_resources) > 0)
                                    @foreach($plan->included_resources as $resource)
                                        <li class="list-group-item d-flex align-items-center bg-transparent border-0 px-0 py-2 text-white" style="font-size: 13.5px;">
                                            <i class="las la-check-circle text--success fs-5 me-2"></i> {{ $resource }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        
                        <div class="card-footer pb-4 pt-0 bg-transparent border-0">
                            @auth
                                @if(auth()->user()->plan_id == $plan->id)
                                    <button class="btn btn--success btn-sm w-100 py-2" disabled>
                                        <i class="las la-check"></i> @lang('Current Plan')
                                    </button>
                                @else
                                    <button type="button" class="btn btn--primary btn-sm w-100 py-2 confirmationBtn" data-action="{{ route('user.plan.subscribe', $plan->id) }}" data-question="@lang('Are you sure you want to purchase this plan for ' . showAmount($plan->price) . '?')">
                                        <i class="las la-shopping-cart me-1"></i> @lang('Subscribe Now')
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('user.login') }}" class="btn btn--primary btn-sm w-100 py-2">
                                    <i class="las la-sign-in-alt me-1"></i> @lang('Login to Subscribe')
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="card" style="background: #111827; border: 1px solid rgba(255, 255, 255, 0.08);">
                        <div class="card-body py-5">
                            <i class="las la-box-open mb-3" style="font-size: 3.5rem; color: #64748b;"></i>
                            <h5 class="text-white">@lang('No subscription plans available right now.')</h5>
                            <p class="text-muted mb-0">@lang('Please check back later or contact support.')</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@auth
    <x-confirmation-modal addClass="custom--modal" :customButton=true />
@endauth

@endsection
