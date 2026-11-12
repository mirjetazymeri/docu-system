<script>
    window.uiLocale = @js(app()->getLocale());
    window.uiMessages = @js(__('widgets'));
    window.applyUiLocale = function () {
        const $ = window.jQuery;
        if (!$) return;
        const messages = window.uiMessages;
        if ($.fn.dataTable) {
            $.extend(true, $.fn.dataTable.defaults, { language: messages.datatable });
        }
        if ($.fn.select2 && $.fn.select2.defaults) {
            const language = {};
            Object.keys(messages.select).forEach(function (key) {
                language[key] = function (args) {
                    let count = args && args.maximum;
                    if (key === 'inputTooLong') count = args.input.length - args.maximum;
                    if (key === 'inputTooShort') count = args.minimum - args.input.length;
                    return messages.select[key].replace(':count', count);
                };
            });
            $.fn.select2.defaults.set('language', language);
        }
        if ($.summernote) {
            $.summernote.lang[window.uiLocale] = messages.summernote;
            $.summernote.options.lang = window.uiLocale;
            $.summernote.options.styleTags = ['p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        }
        if (window.moment) {
            if (window.uiLocale === 'sq' && window.uiMomentSq !== window.moment) {
                window.moment.defineLocale('sq', {
                    months: 'janar_shkurt_mars_prill_maj_qershor_korrik_gusht_shtator_tetor_nëntor_dhjetor'.split('_'),
                    monthsShort: 'jan_shk_mar_pri_maj_qer_kor_gus_sht_tet_nën_dhj'.split('_'),
                    weekdays: 'e diel_e hënë_e martë_e mërkurë_e enjte_e premte_e shtunë'.split('_'),
                    weekdaysShort: 'die_hën_mar_mër_enj_pre_sht'.split('_'),
                    weekdaysMin: 'Di_Hë_Ma_Më_En_Pr_Sh'.split('_'),
                    longDateFormat: { LT: 'HH:mm', LTS: 'HH:mm:ss', L: 'DD/MM/YYYY', LL: 'D MMMM YYYY', LLL: 'D MMMM YYYY HH:mm', LLLL: 'dddd, D MMMM YYYY HH:mm' },
                    week: { dow: 1, doy: 4 }
                });
                window.uiMomentSq = window.moment;
            }
            window.moment.locale(window.uiLocale);
        }
    };
    window.applyUiLocale();
    if (window.jQuery) jQuery(window.applyUiLocale);

    // Browser validation normally follows the browser's language, not the page.
    document.addEventListener('invalid', function (event) {
        const field = event.target;
        if (!field.validity || !field.setCustomValidity) return;
        field.setCustomValidity('');
        const validity = field.validity;
        const messages = window.uiMessages.validation;
        let message = messages.invalid;
        if (validity.valueMissing) message = messages.required;
        else if (validity.typeMismatch) message = field.type === 'email' ? messages.email : messages.url;
        else if (validity.patternMismatch) message = messages.pattern;
        else if (validity.tooLong) message = messages.tooLong;
        else if (validity.tooShort) message = messages.tooShort;
        else if (validity.rangeUnderflow || validity.rangeOverflow) message = messages.range;
        field.setCustomValidity(message);
    }, true);
    ['input', 'change'].forEach(function (name) {
        document.addEventListener(name, function (event) {
            if (event.target.setCustomValidity) event.target.setCustomValidity('');
        }, true);
    });
</script>
