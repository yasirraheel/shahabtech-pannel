@php

    $policyPages = getContent('policy_pages.element', false, null, true);
    $pages = App\Models\Page::where('tempname', $activeTemplate)
        ->where('is_default', Status::NO)
        ->get();
@endphp
<footer class="footer-area">
    <div class="py-60">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="footer-item">
                        <div class="{{ gs('multi_language') ? 'flex-between' : 'flex-center' }} gap-3">
                            <div class="{{ gs('multi_language') ? '' : 'text-center' }}">
                                <div class="footer-item__logo">
                                    <a href="{{ route('home') }}"> <img src="{{ siteLogo() }}" alt="Logo"></a>
                                </div>
                                <ul class="footer-menu">
                                    <li class="footer-menu__item"><a class="footer-menu__link" href="{{ route('home') }}"> @lang('Home') </a></li>
                                    <li class="footer-menu__item"><a class="footer-menu__link" href="{{ route('buy.account') }}"> @lang('Buy Account') </a></li>
                                    <li class="footer-menu__item"><a class="footer-menu__link" href="{{ route('blogs') }}"> @lang('Blogs') </a></li>
                                    <li class="footer-menu__item"><a class="footer-menu__link" href="{{ route('contact') }}"> @lang('Contact') </a></li>
                                    @guest
                                        <li class="footer-menu__item"><a class="footer-menu__link" href="{{ route('user.register') }}"> @lang('Join') </a></li>
                                    @else
                                        <li class="footer-menu__item"><a class="footer-menu__link" href="{{ route('user.home') }}"> @lang('Dashboard') </a></li>
                                    @endguest
                                </ul>
                            </div>
                            @if (gs('multi_language'))
                                @php
                                    $language = App\Models\Language::all();
                                    $selectLanguage = App\Models\Language::where('code', session('lang'))->first();
                                @endphp
                                <div class="language dropdown">
                                    <button class="language-wrapper" data-bs-toggle="dropdown" aria-expanded="false">
                                        <div class="language-content">
                                            <div class="language_flag">
                                                <img src="{{ getImage(getFilePath('language') . '/' . $selectLanguage->image), '50x50' }}" alt="flag">
                                            </div>
                                            <p class="language_text_select">{{ __($selectLanguage->name) }}</p>
                                        </div>
                                        <span class="collapse-icon"><i class="las la-angle-down"></i></span>
                                    </button>
                                    <div class="dropdown-menu langList_dropdow py-2" style="">
                                        <ul class="langList">
                                            @foreach ($language as $item)
                                                <li class="language-list langSel" data-code={{ $item->code }}>
                                                    <div class="language_flag">
                                                        <img src="{{ getImage(getFilePath('language') . '/' . $item->image), '50x50' }}" alt="flag">
                                                    </div>
                                                    <p class="language_text">{{ __($item->name) }}</p>
                                                </li>
                                            @endforeach

                                        </ul>
                                    </div>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bottom-footer">
        <div class="container">
            <div class="row gy-3">
                <div class="col-lg-6">
                    <div class="bottom-footer-text">
                        <p> @lang('Copyright') &copy; {{ date('Y') }} @lang('All Right Reserved')</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <ul class="footer-menu style-right mt-0">
                        @foreach ($policyPages as $policyPage)
                            <li class="footer-menu__item"><a class="footer-menu__link" href="{{ route('policy.pages', [slug($policyPage->slug)]) }}"> {{ __($policyPage->data_values->title) }} </a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

@push('script')
    <script>
        $(document).ready(function() {
            const $mainlangList = $(".langList");
            const $langBtn = $(".language-content");
            const $langListItem = $mainlangList.children();

            $langListItem.each(function() {
                const $innerItem = $(this);
                const $languageText = $innerItem.find(".language_text");
                const $languageFlag = $innerItem.find(".language_flag");

                $innerItem.on("click", function(e) {
                    $langBtn.find(".language_text_select").text($languageText.text());
                    $langBtn.find(".language_flag").html($languageFlag.html());
                });
            });
        });
    </script>
@endpush
