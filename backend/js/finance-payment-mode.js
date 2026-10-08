(function ($) {
    'use strict';
    function sync(form) {
        var cheque = $(form).find('[data-finance-payment-mode]').val() === 'Cheque';
        $(form).find('.finance-cheque-fields').toggleClass('d-none', !cheque)
            .find('input').prop('required', cheque).prop('disabled', !cheque);
    }
    $(document).on('change', '[data-finance-payment-mode]', function () { sync(this.form); });
    $(document).on('reset', 'form', function () {
        var form = this;
        if ($(form).find('[data-finance-payment-mode]').length) {
            setTimeout(function () { sync(form); }, 0);
        }
    });
    $(document).on('shown.bs.modal', '#myModal, #myModaledit', function () {
        $(this).find('[data-finance-payment-mode]').each(function () { sync(this.form); });
    });
    $(function () { $('[data-finance-payment-mode]').each(function () { sync(this.form); }); });
}(jQuery));
