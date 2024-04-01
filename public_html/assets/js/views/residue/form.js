var tableResidue;
$(function () {
    tableResidue = getTable(false);
});

$(document).on('click', '.editResidueModal', function(){
    const residue_id = $(this).attr('residue-id');
    const name = $(this).closest('tr').find('td:eq(0)').text();

    $('#editResidueModal input[name="name"]').val(name);
    $('#editResidueModal input[name="residue_id"]').val(residue_id);
    $('#editResidueModal').modal('show');
    checkLabelAnimate();
})

$(document).on('click', '.btnRemoveResidue', function (){
    const residue_id = $(this).attr('residue-id');
    const residue_name = $(this).closest('tr').find('td:eq(0)').text();

    Swal.fire({
        title: 'Exclusão de Resíduo',
        html: "Você está prestes a excluir definitivamente o resíduo <br><strong>"+residue_name+"</strong><br>Deseja continuar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#bbb',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('#deleteResidue').val(),
                data: { residue_id },
                dataType: 'json',
                success: response => {
                    reloadTable();
                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.message
                    })
                }, error: e => {
                    if (e.status !== 403 && e.status !== 422)
                        console.log(e);
                },
                complete: function(xhr) {
                    if (xhr.status === 403) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Você não tem permissão para fazer essa operação!'
                        });
                        $(`button[residue-id="${residue_id}"]`).trigger('blur');
                    }
                    if (xhr.status === 422) {

                        let arrErrors = [];

                        $.each(xhr.responseJSON.errors, function( index, value ) {
                            arrErrors.push(value);
                        });

                        if (!arrErrors.length && xhr.responseJSON.message !== undefined)
                            arrErrors.push('Você não tem permissão para fazer essa operação!');

                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                        });
                    }
                }
            });
        }
    })
});

// Validar dados
$("#formUpdateResidueModal").validate({
    rules: {
        name: {
            required: true
        }
    },
    messages: {
        name: {
            required: 'Informe um nome para o resíduo'
        }
    },
    invalidHandler: function(event, validator) {
        $('html, body').animate({scrollTop:0}, 400);
        let arrErrors = [];
        $.each(validator.errorMap, function (key, val) {
            arrErrors.push(val);
        });
        setTimeout(() => {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
            });
        }, 500);
    },
    submitHandler: function(form) {
        let getForm = $('#formUpdateResidueModal');

        getForm.find('button[type="submit"]').attr('disabled', true);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: getForm.attr('action'),
            data: getForm.serialize(),
            dataType: 'json',
            success: response => {

                getForm.find('button[type="submit"]').attr('disabled', false);

                if (!response.success) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + response.message + '</li></ol>'
                    });
                    return false;
                }

                Toast.fire({
                    icon: 'success',
                    title: response.message
                });

                $('#editResidueModal').modal('hide');
                reloadTable();
            }, error: e => {
                console.log(e);
                getForm.find('button[type="submit"]').attr('disabled', false);
                let arrErrors = [];

                $.each(e.responseJSON.errors, function( index, value ) {
                    arrErrors.push(value);
                });

                if (!arrErrors.length && e.responseJSON.message !== undefined)
                    arrErrors.push('Você não tem permissão para fazer essa operação!');

                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                });
            }
        });
    }
});

const getTable = (stateSave = true) => {
    return $("#tableResidues").DataTable({
        "responsive": true,
        "processing": true,
        "autoWidth": false,
        "serverSide": true,
        "sortable": true,
        "searching": true,
        "stateSave": stateSave,
        "serverMethod": "post",
        "order": [[ 0, 'asc' ]],
        "ajax": {
            url: $('#fetchResidue').val(),
            pages: 2,
            type: 'POST',
            data: { "_token": $('meta[name="csrf-token"]').attr('content') },
            error: function(jqXHR, ajaxOptions, thrownError) {
                console.log(jqXHR, ajaxOptions, thrownError);
            }
        },
        "initComplete": function( settings, json ) {
            $('[data-bs-toggle="tooltip"]').tooltip();
        },
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
        }
    });
}

const reloadTable = () => {
    $('[data-bs-toggle="tooltip"]').tooltip('dispose')
    tableResidue.destroy();
    $("#tableResidues tbody").empty();
    tableResidue = getTable();
}
