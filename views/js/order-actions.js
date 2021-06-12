/* global ingenico_api_url */
jQuery(document).ready(function ($) {
    // Close Button
    $(document).on('click', '.btn-close', function (e) {
        e.preventDefault();
        let modal = $(this).closest('.modal');
        modal.removeClass('in').hide();
    });

    // Capture Button
    $(document).on('click', '#process-capture, .btn-ing-capture', function (e) {
        e.preventDefault();
        Ingenico.openModal('capture-confirmation-modal');
    });

    $(document).on('click', '#process-capture-action', function (e) {
        e.preventDefault();

        const el = $(e.target);
        el.addClass('disabled');
        $.post(ingenico_api_url, {
            ajax: true,
            action: 'capture',
            orderId: $('#ingenico_order_id').val(),
            paymentId: $('#ingenico_pay_id').val(),
            captureAmount: $('#capture_amount').val()
        }).done(function (response) {
            el.removeClass('disabled');
            if (response.status !== 'ok') {
                alert('Error: ' + response.message);
                return false;
            }

            alert(response.message);
            self.location.href = document.URL;
        });
    });

    // Cancel Button
    $(document).on('click', '#process-cancel-action, .btn-ing-cancel', function (e) {
        e.preventDefault();

        const el = $(e.target);

        el.addClass('disabled');
        $.post(ingenico_api_url, {
            ajax: true,
            action: 'cancel',
            orderId: $('#ingenico_order_id').val(),
            paymentId: $('#ingenico_pay_id').val(),
        }).done(function (response) {
            el.removeClass('disabled');
            if (response.status !== 'ok') {
                alert('Error: ' + response.message);
                return false;
            }
            alert(response.message);
            self.location.href = document.URL;
        });
    });

    // Refund Button
    $(document).on('click', '#process-refund, .btn-ing-refund', function (e) {
        e.preventDefault();
        Ingenico.openModal('refund-modal');
    });

    $(document).on('click', '#process-perform-refund', function (e) {
        e.preventDefault();
        Ingenico.openModal('refund-confirmation-modal');
    });

    $(document).on('click', '#process-refund-action', function (e) {
        e.preventDefault();

        const el = $(e.target);
        el.addClass('disabled');
        $.post(ingenico_api_url, {
            ajax: true,
            action: 'refund',
            orderId: $('#ingenico_order_id').val(),
            paymentId: $('#ingenico_pay_id').val(),
            refundAmount: $('#refund_amount').val()
        }).done(function (response) {
            el.removeClass('disabled');
            if (response.status !== 'ok') {
                if (response.status === 'action_required') {
                    Ingenico.openModal('refund-failed-modal');
                    return false;
                }

                alert('Error: ' + response.message);
                return false;
            }

            alert(response.message);
            self.location.href = document.URL;
        });
    });
});