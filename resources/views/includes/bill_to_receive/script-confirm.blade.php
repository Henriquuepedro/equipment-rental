<script>
    setTimeout(() => {
        $(document).on('ifChanged', '#{{ $modal_id ?? 'modalConfirmPayment' }} .equipment, #modalWithdraw{{ empty($modal_id) ? '' : '_hidden' }} .equipment', function () {

            const check = !$(this).is(':checked');

            $(this).closest('tr').find('.flatpickr-input, select, .input-button-calendar a').attr('disabled', check);

            $(this).closest('tr').toggleClass('noSelected selected');
        });

        $(document).on('click', '.{{ $btn_class_action ?? 'btnConfirmPayment' }}', function () {
            if ($(`#{{ $modal_id ?? 'modalConfirmPayment' }} [name="form_payment"] option`).length === 0) {
                getOptionsForm('form-of-payment', $(`#{{ $modal_id ?? 'modalConfirmPayment' }} [name="form_payment"]`));
            }
            const payment_id = $(this).data('rental-payment-id');
            const rental_code = $(this).data('rental-code');
            const name_client = $(this).data('name-client');
            const date_rental = $(this).data('date-rental');
            const due_date = $(this).data('due-date');
            const due_value = 'R$ ' + $(this).data('due-value');

            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[name="rental_code"]').val(rental_code).closest('.form-group').show();
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[name="client"]').val(name_client);
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[name="date_rental"]').val(date_rental).closest('.form-group').show();
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[name="due_date"]').val(due_date).closest('.form-group').show();
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[name="due_value"]').val(due_value);
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[name="payment_id"]').val(payment_id);
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[name="date_payment"]').val((new Date()).toJSON().slice(0, 10));
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[name="form_payment"]').val("");
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').find('[type="submit"]').attr('disabled', false);
            checkLabelAnimate();
            $('#{{ $modal_id ?? 'modalConfirmPayment' }}').modal('show');
        });

        $('#{{ $form_id ?? 'formConfirmPayment' }}').on('submit', function (e) {
            e.preventDefault();
            const payment_id = $('[name="payment_id"]', this).val();
            const form_payment = $('[name="form_payment"]', this).val();
            const date_payment = $('[name="date_payment"]', this).val();
            const endpoint = $(this).attr('action');
            const btn = $(this).find('[type="submit"]');

            btn.attr('disabled', true);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: endpoint,
                data: {
                    payment_id,
                    form_payment,
                    date_payment
                },
                dataType: 'json',
                success: response => {
                    if (!response.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>' + response.message + '</li></ol>'
                        });
                        return false;
                    }

                    $('#{{ $modal_id ?? 'modalConfirmPayment' }}').modal('hide');

                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });

                    @if (empty($form_id))
                        getTable($('#contentListBillToReceive [data-bs-toggle="tab"].active').attr('id').replace('-tab', ''));
                    @else
                        getTableGeneralSearchBillReceive()
                    @endif
                }, error: e => {
                    console.log(e);
                    let arrErrors = [];

                    $.each(e.responseJSON.errors, function (index, value) {
                        arrErrors.push(value);
                    });

                    if (!arrErrors.length && e.responseJSON.message !== undefined) {
                        arrErrors.push('Você não tem permissão para fazer essa operação!');
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                }
            }).always(() => {
                btn.attr('disabled', false);
            });
        });
    }, 250);
</script>
