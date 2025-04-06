<script>
    setTimeout(() => {
        $(document).on('click', '.{{ $btn_class_action ?? 'btnReopenPayment' }}', function () {
            setFieldsToPayment($(this), $('#{{ $modal_id ?? 'modalReopenPayment' }}'));
        });

        $('#{{ $form_id ?? 'formReopenPayment' }}').on('submit', function (e) {
            e.preventDefault();
            const payment_id = $('[name="payment_id"]', this).val();
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
                    payment_id
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

                    $('#{{ $modal_id ?? 'modalReopenPayment' }}').modal('hide');

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
            }).always(function () {
                btn.attr('disabled', false);
            });
        });
    }, 250);
</script>
