<script>
    @if(!empty($btn_class_action))
        const setFieldsToPayment = (btn, modal) => {
            if ($(`#${modal.prop('id')} [name="form_payment"] option`).length === 0) {
                getOptionsForm('form-of-payment', $(`#${modal.prop('id')} [name="form_payment"]`));
            }

            const rental_code = btn.data('rental-code');
            const name_client = btn.data('name-client');
            const date_rental = btn.data('date-rental');
            const due_date = btn.data('due-date');
            const due_value = 'R$ ' + btn.data('due-value');
            const payment_id = btn.data('payment-id');
            const rental_payment_id = btn.data('rental-payment-id');
            const payday = btn.data('payday');

            modal.find('[name="date_payment"]').closest('.form-group').show();
            modal.find('[name="form_payment"]').closest('.form-group').show();

            if ($('#paid-tab.active').length) {
                modal.find('[name="date_payment"]').val(payday);
                modal.find('[name="form_payment"]').val(payment_id);
                modal.find('.modal-title').text('Detalhes do Pagamento');
                modal.find('[name="payment_id"]').val(rental_payment_id);
            } else {
                modal.find('[name="date_payment"]').closest('.form-group').hide();
                modal.find('[name="form_payment"]').closest('.form-group').hide();
                modal.find('.modal-title').text('Detalhes do Lançamento');
            }

            modal.find('[name="rental_code"]').val(rental_code);
            modal.find('[name="client"]').val(name_client);
            modal.find('[name="date_rental"]').val(date_rental);
            modal.find('[name="due_date"]').val(due_date);
            modal.find('[name="due_value"]').val(due_value);
            checkLabelAnimate();
            modal.modal('show');
        }
    @endif

    setTimeout(() => {
        $(document).on('click', '.{{ $btn_class_action ?? 'btnViewPayment' }}', function () {
            setFieldsToPayment($(this), $('#{{ $modal_id ?? 'modalViewPayment' }}'));
        });
    }, 250);
</script>
