<li class="nav-item dropdown">
    <a id="navbarDropdownLang" class="dropdown-toggle" href="#" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        <i class="livicon" data-name="flag" data-loop="true" data-color="#FFFFFF" data-hovercolor="#FFFFFF" data-size="28"></i>
        {{ __('ui.language_' . app()->getLocale()) }}
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownLang">
        @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
            <a rel="alternate" hreflang="{{ $localeCode }}"
                href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                class="dropdown-item language-item">
                <i class="livicon" data-name="flag" data-loop="true" data-color="#333" data-hovercolor=" #333" data-size="20"></i>
                <span>{{ __('ui.language_' . $localeCode) }}</span>
            </a>
        @endforeach
    </div>
</li>
