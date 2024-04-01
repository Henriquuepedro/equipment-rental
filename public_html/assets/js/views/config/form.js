$(() => {
    $('[name="cep"]').mask('00.000-000');
    $('#cpf_cnpj').mask($('#cpf_cnpj').val().length === 11 ? '000.000.000-00' : '00.000.000/0000-00');
    $('[name="phone_1"],[name="phone_2"],[name="phone_modal"]').mask('(00) 000000000');

    setTimeout(() => {
        $('#newUserModal #formCreateUser .form-group').each(function (){
            $(this).removeClass('label-animate').find('input.form-control').val('');
        });
    }, 1000);

    var src = document.getElementById("profile-logo");
    var target = document.getElementById("src-profile-logo");
    showImage(src,target);
    setTabConfigCompany();
});

$('#newUserModal').on('shown.bs.modal', function(){
    checkLabelAnimate();
});

const setTabConfigCompany = () => {
    const url = window.location.href;
    const splitUrl = url.split('#');

    if (splitUrl.length === 2) {
        $(`#${splitUrl[1]}-tab`).tab('show');
    }
}

$(document).on('blur', '[name="cep"]', function (){
    const cep = $(this).val().replace(/\D/g, '');
    let el = $(this).closest('form');

    if (cep.length === 0) return false;
    if (cep.length !== 8) {
        Toast.fire({
            icon: 'error',
            title: 'CEP não encontrado'
        });
        return false;
    }
    $.getJSON("https://viacep.com.br/ws/"+ cep +"/json/", function(dados) {

        if (!("erro" in dados)) {
            if(dados.logradouro !== '') el.find('[name="address"]').val(dados.logradouro).parent().addClass("label-animate");
            if(dados.bairro !== '')     el.find('[name="neigh"]').val(dados.bairro).parent().addClass("label-animate");
            if(dados.localidade !== '') el.find('[name="city"]').val(dados.localidade).parent().addClass("label-animate");
            if(dados.uf !== '')         el.find('[name="state"]').val(dados.uf).parent().addClass("label-animate");
        } //end if.
        else {
            Toast.fire({
                icon: 'error',
                title: 'CEP não encontrado'
            })
        }
    });
})

$("#formUpdateCompany").validate({
    rules: {
        name: {
            required: true
        },
        phone_1: {
            required: true,
            rangelength: [13, 14]
        },
        phone_2: {
            rangelength: [13, 14]
        },
        email: {
            required: true,
            email: true
        },
        address: {
            required: true
        },
        neigh: {
            required: true
        },
        city: {
            required: true
        },
        state: {
            required: true
        }
    },
    messages: {
        name: {
            required: 'Digite o nome/razão social da empresa.'
        },
        phone_1: {
            required: "O campo telefone primário é um campo obrigatório.",
            rangelength: "O campo telefone primário deve ser um telefone válido."
        },
        phone_2: {
            rangelength: "O campo telefone secundário deve ser um telefone válido."
        },
        email: {
            required: "Informe um e-mail comercial válido.",
            email: "Informe um e-mail comercial válido."
        },
        address: {
            required: "Informe o endereço para a empresa."
        },
        neigh: {
            required: "Informe o bairro para a empresa."
        },
        city: {
            required: "Informe a cidade para a empresa."
        },
        state: {
            required: "Informe o estado para a empresa."
        }
    },
    invalidHandler: function(event, validator) {
        let arrErrors = [];
        $.each(validator.errorMap, function (key, val) {
            arrErrors.push(val);
        });
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
        });
    },
    submitHandler: function(form) {
        form.submit();
    }
});

$('#new-user').on('click', function (){
    $('#newUserModal').modal('show');
});

$('#formCreateUser').validate({
    rules: {
        name_modal: {
            required: true
        },
        email_modal: {
            required: true,
            email: true
        },
        password_modal: {
            required: true,
            minlength: 6
        },
        password_modal_confirmation: {
            equalTo : "#password_modal"
        }
    },
    messages: {
        name_modal: {
            required: 'Digite um nome para o usuário.'
        },
        email_modal: {
            required: "Informe um e-mail válido.",
            email: "Informe um e-mail válido."
        },
        password_modal: {
            required: "Digite uma senha para o usuário.",
            minlength: "A senha para acesso deve conter no mínimo 6 dígitos."
        },
        password_modal_confirmation: {
            equalTo: "As senhas não correspondem"
        }
    },
    invalidHandler: function(event, validator) {
        let arrErrors = [];
        $.each(validator.errorMap, function (key, val) {
            arrErrors.push(val);
        });
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
        });
    },
    submitHandler: function(form) {
        let getForm = $('#formCreateUser');

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

                $('#newUserModal').modal('hide');
                $('#newUserModal #formCreateUser .form-group').each(function (){
                    $(this).removeClass('label-animate').find('input.form-control').val('');
                    $(this).find('input[type="checkbox"]').prop('checked', false);
                });
                $('#newUserModal #formCreateUser .permissions input[type="checkbox"]').each(function (){
                    $(this).prop('checked', false);
                });
                $('#newUserModal #formCreateUser [name="type_user"]').val(0);
                $('#permission_select_all_permission').prop('checked', false);
                loadUsers();
            }, error: e => {
                getForm.find('button[type="submit"]').attr('disabled', false);
                let arrErrors = [];

                $.each(e.responseJSON.errors, function( index, value ) {
                    arrErrors.push(value);
                });

                if (!arrErrors.length && e.responseJSON.message !== undefined) {
                    arrErrors.push('Você não tem permissão para fazer essa operação!');
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                });
            }
        });
    }
});

$('.select-all-permission').on('change', function(){
    const checked = $(this).is(':checked');

    $(this).closest('.modal-body').find('.permissions input[type="checkbox"]').each(function (){
        $(this).prop('checked', checked);
    });
});

$('#users-tab').click(function (){
    loadUsers();
})

$('#formUpdatePermission').on('submit', function (){
    let getForm = $(this);

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

            Toast.fire({
                icon: response.success ? 'success' : 'error',
                title: response.message
            })

            if (response.success) {
                $('#viewPermission').modal('hide');
            }

        }, error: e => {
            getForm.find('button[type="submit"]').attr('disabled', false);
            let arrErrors = [];

            $.each(e.responseJSON.errors, function( index, value ) {
                arrErrors.push(value);
            });
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
            });
        }
    });
    return false;
});

$('#formUpdateUser').validate({
    rules: {
        update_user_name: {
            required: true
        },
        update_user_email: {
            required: true,
            email: true
        }
    },
    messages: {
        name_modal: {
            required: 'Digite um nome para o usuário para atualizar.'
        },
        email_modal: {
            required: "Informe um e-mail para atualizar.",
            email: "Informe um e-mail válido para atualizar."
        }
    },
    invalidHandler: function(event, validator) {
        let arrErrors = [];
        $.each(validator.errorMap, function (key, val) {
            arrErrors.push(val);
        });
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
        });
    },
    submitHandler: function(form) {
        let getForm = $('#formUpdateUser');

        getForm.find('button[type="submit"]').attr('disabled', true);

        const name      = getForm.find('input[name="update_user_name"]').val();
        const email     = getForm.find('input[name="update_user_email"]').val();
        const phone     = getForm.find('input[name="update_user_phone"]').val();
        const user_id   = getForm.find('input[name="update_user_id"]').val();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: getForm.attr('action'),
            data: { name, email, phone, user_id },
            dataType: 'json',
            enctype: 'multipart/form-data',
            success: response => {

                getForm.find('button[type="submit"]').attr('disabled', false);

                if (!response.success) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + response.data + '</li></ol>'
                    });
                    return false;
                }

                Toast.fire({
                    icon: 'success',
                    title: response.data
                })

                $('#updateUser').modal('hide');
                loadUsers();
            }, error: e => {
                getForm.find('button[type="submit"]').attr('disabled', false);
                let arrErrors = [];

                $.each(e.responseJSON.errors, function( index, value ) {
                    arrErrors.push(value);
                });
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                });
            }
        });
    }
});

$(document).on('click', '.viewPermission', function (){
    const user_id = $(this).attr('user-id');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetUserPermission').val(),
        data: { user_id },
        dataType: 'json',
        success: response => {

            if (!response.success) {
                Toast.fire({
                    icon: response.success ? 'success' : 'error',
                    title: response.data
                });
                return false;
            }

            $('#viewPermission .modal-body .user-permission-update').empty();

            const htmlPermission = response.data + '<input type="hidden" name="user_id" value="'+user_id+'">'

            $('#viewPermission').modal('show').find('.modal-body .user-permission-update').append(htmlPermission);
            $('#permission_select_all_permission_update').prop('checked', false);
        }, error: e => {
            console.log(e);
        }
    });
});

$(document).on('click', '.inactivate-user', function (){
    const user_id       = $(this).attr('user-id');
    const user_name     = $(this).attr('user-name');
    const operationOff  = $(`button.inactivate-user[user-id="${user_id}"]`).hasClass('btn-warning');
    const nameAction    = operationOff ? 'Inativar' : 'Ativar';
    const nameStatus    = operationOff ? 'Inativo' : 'Ativo';
    const colorAction   = operationOff ? '#ffaf00' : '#19d895';
    const msgComplement = operationOff ? 'Após a inativação o usuário ser automaticamente desconectado.' : 'Após a ativação o usuário já poderá se autenticar novamente.';
    const statusUser    = $(this).closest('.card-body').find('.wrapper .status-user');

    Swal.fire({
        title: nameAction + ' usuário',
        html: "Você está prestes a " + nameAction.toLowerCase() + " o usuário <br><strong>" + user_name + "</strong><br><br>"+msgComplement+"<br><br>Deseja continuar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: colorAction,
        cancelButtonColor: '#bbb',
        confirmButtonText: 'Sim, ' + nameAction.toLowerCase(),
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('#routeInactiveUser').val(),
                data: { user_id },
                dataType: 'json',
                success: response => {

                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.message
                    })

                    if (response.success) {
                        let htmlBtn = $(`button.inactivate-user[user-id="${user_id}"]`).hasClass('btn-warning') ?
                            '<i class="fa fa-user-check"></i> Ativar' : '<i class="fa fa-user-times"></i> Inativar';

                        $(`button.inactivate-user[user-id="${user_id}"]`).toggleClass('btn-warning btn-success').html(htmlBtn);

                        statusUser.text(nameStatus)
                        statusUser.toggleClass('badge-success badge-warning');
                    }
                }, error: e => {
                    console.log(e);
                }
            });
        }
    })
});

$(document).on('click', '#viewPermission .modal-body input[type="checkbox"], #newUserModal .modal-body input[type="checkbox"]', function(){
    const permission_id = parseInt($(this).data('permission-id'));
    const auto_check    = $(this).data('auto-check');
    const parentEl      = $(this).hasClass('update-permission') ? '#viewPermission' : '#newUserModal';
    let input_auto_check;

    $(`${parentEl} input[type="checkbox"]:checked`).each(function(){
        input_auto_check = $(this).data('auto-check');
        if (input_auto_check.includes(permission_id)) {
            $(`${parentEl} input[type="checkbox"][data-permission-id="${permission_id}"]`).prop('checked', true);
            return false;
        }
    });

    if (auto_check.length) {
        auto_check.forEach(id => {
            $(`${parentEl} input[type="checkbox"][data-permission-id="${id}"]`).prop('checked', true);
        })
    }
});

$(document).on('click', '.changeTypeUser', function (){
    const user_id   = $(this).attr('user-id');
    const user_name = $(this).attr('user-name');
    const type_user = $(this).attr('type-user');

    if (type_user != 0 && type_user != 1) {
        Toast.fire({
            icon: 'error',
            title: 'Ocorreu um problema para identificar o usuário, tente mais tarde!'
        })
        return false;
    }

    const typeChange = type_user == 0 ? 'administrador' : 'usuário';

    Swal.fire({
        title: 'Tornar usuário como '+typeChange,
        html: "Você está prestes a tornar o usuário <br><strong>"+user_name+"</strong> como "+typeChange+".<br><br>Deseja continuar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#8862e0',
        cancelButtonColor: '#bbb',
        confirmButtonText: 'Sim, continuar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('#routeUserChangeType').val(),
                data: { user_id },
                dataType: 'json',
                success: response => {
                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.message
                    });

                    if (response.success) {
                        loadUsers(type_user == 1, user_id);
                    }

                }, error: e => {
                    console.log(e);
                },
                complete: function(xhr) {
                    if (xhr.status === 403) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Você não tem permissão para fazer essa operação!'
                        });
                    }
                }
            });
        }
    })
})

$(document).on('click', '.removeUser', function (){
    const user_id   = $(this).attr('user-id');
    const user_name = $(this).attr('user-name');
    const elDelete  = $(this).closest('.card').closest('.col-md-12');

    Swal.fire({
        title: 'Excluir usuário definitivamente',
        html: "Você está prestes a excluir o usuário <br><strong>"+user_name+"</strong><br><br>Não será possível recuperar o usuário.<br><br>É possível apenas inativar o usuário, que o acesso será bloqueado.<br><br>Deseja continuar a excluir?",
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
                url: $('#routeDeleteUser').val(),
                data: { user_id },
                dataType: 'json',
                success: response => {
                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.message
                    })

                    if (response.success) {
                        elDelete.slideUp(700);
                        setTimeout(() => { elDelete.remove() }, 900);
                    }
                }, error: e => {
                    console.log(e);
                },
                complete: function(xhr) {
                    if (xhr.status === 403) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Você não tem permissão para fazer essa operação!'
                        });
                    }
                }
            });
        }
    })
});

$(document).on('click', '.updateUser', function (){
    const user_id = $(this).attr('user-id');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetUser').val(),
        data: { user_id },
        dataType: 'json',
        success: response => {
            if (!response.success) {
                Toast.fire({
                    icon: response.success ? 'success' : 'error',
                    title: response.data
                });
                return false;
            }

            $('#updateUser input[name="update_user_name"]').val(response.data.name);
            $('#updateUser input[name="update_user_email"]').val(response.data.email);
            $('#updateUser input[name="update_user_phone"]').val(response.data.phone);
            $('#updateUser input[name="update_user_id"]').val(user_id);

            $('#updateUser').modal('show');
            checkLabelAnimate();
            $('[name="update_user_phone"]').unmask().mask('(00) 000000000');
        }, error: e => {
            console.log(e);
        }
    });
});

$('[name="type_user"]').on('change', function(){
    const type = parseInt($(this).val());

    $('.user-permission').css({ display: type === 0 ? 'block' : 'none' })
});

const loadUsers = (openPermissions = false, user_id = false) => {
    $('#users-registred').empty().append('<div class="d-flex justify-content-center mt-3 mb-3"><h3><i class="fa fa-spinner fa-spin"></i> Carregando ...</h3></div>');
    $.ajax({
        type: 'GET',
        url: $('#routeGetUsers').val(),
        dataType: 'json',
        success: response => {
            $('#users-registred').empty();

            if (response.length === 0) {
                $('#users-registred').empty().append('<h4 class="text-center">Não foram encontrados nenhum usuário</h4>');
                return false;
            }

            let colorBtnStatus,
                nameBtnStatus,
                userMaster,
                statusUser,
                identificationUser,
                viewPermission,
                viewChangeTypeUser,
                viewBtnDeleteUser,
                htmlUser,
                viewInativeUser,
                viewBtnConfig,
                userSession,
                viewUpdateUser,
                viewChangeTypeAdm;

            $.each(response, function( index, value ) {

                userMaster          = value.type_user === 2;
                userSession         = value.user_id_session === value.id;
                colorBtnStatus      = value.active ? 'warning' : 'success';
                nameBtnStatus       = value.active ? '<i class="fa fa-user-times"></i> Inativar' : '<i class="fa fa-user-check"></i> Ativar';
                statusUser          = value.active ? '<div class="badge badge-success text-dark ml-2 status-user">Ativo</div>' : '<div class="badge badge-warning text-dark ml-2 status-user">Inativo</div>';
                identificationUser  = value.type_user === 2 ? 'Admin-Master' : (value.type_user === 1 ? 'Admin' : 'User');
                viewPermission      = value.type_user === 0 && !userSession;
                viewChangeTypeUser  = value.type_user === 0 && !userSession;
                viewChangeTypeAdm   = value.type_user === 1 && !userSession;
                viewBtnDeleteUser   = !userMaster && !userSession;
                viewInativeUser     = value.user_id_session !== value.id && !userMaster && !userSession;
                viewUpdateUser      = !userMaster && !userSession;
                viewBtnConfig       = !viewPermission && !viewChangeTypeUser && !viewInativeUser && !viewUpdateUser && (!viewBtnDeleteUser || !viewInativeUser) ? 'd-none' : '';

                htmlUser = `
                        <div class="col-md-12 mb-2">
                            <div class="card rounded shadow-none">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-10 d-lg-flex">
                                            <div class="user-avatar mb-auto">
                                                <img src="${ value.image }" alt="profile image" class="profile-img img-lg rounded-circle">
                                            </div>
                                            <div class="wrapper pl-0 pl-lg-4 w-100 ml-3">
                                                <div class="wrapper d-lg-flex align-items-center mb-2">
                                                    <h4 class="mb-0 font-weight-medium">${ value.name }</h4>
                                                    <div class="badge badge-secondary text-dark ml-lg-2 ml-2">${identificationUser}</div>
                                                    ${statusUser}
                                                </div>
                                                <div class="wrapper d-flex align-items-center font-weight-medium text-muted">
                                                    <i class="fa fa-envelope mr-2"></i>
                                                    <p class="mb-0 text-muted">${ value.email }</p>
                                                </div>
                                                <div class="wrapper d-lg-flex align-items-center font-weight-medium text-muted">
                                                    <strong>Último Login:</strong>
                                                    <p class="mb-0 text-muted">&nbsp;${ value.last_login }</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="wrapper text-right ${viewBtnConfig}">
                                                <div class="dropdown dropleft">
                                                    <button type="button" class="btn btn-primary icon-btn dropdown-toggle pull-right" id="dropdownConfigUser" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-cog"></i>
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownConfigUser">`;
                if (viewPermission) {
                    htmlUser += `<button type="button" class="btn btn-sm btn-primary col-md-12 mb-1 viewPermission text-left" user-id="${value.id}"><i class="fa fa-user-cog"></i> Permissões</button>`;
                }
                if (viewUpdateUser) {
                    htmlUser += `<button type="button" class="btn btn-sm btn-dark col-md-12 mb-1 updateUser text-left" user-id="${value.id}"><i class="fa fa-user-edit"></i> Cadastro</button>`;
                }
                if (viewChangeTypeUser) {
                    htmlUser += `<button type="button" class="btn btn-sm btn-info col-md-12 mb-1 changeTypeUser text-left" user-id="${value.id}" type-user="${value.type_user}" user-name="${value.name}"><i class="fa fa-user-shield"></i> Tornar Admin</button>`;
                }
                if (viewChangeTypeAdm) {
                    htmlUser += `<button type="button" class="btn btn-sm btn-info col-md-12 mb-1 changeTypeUser text-left" user-id="${value.id}" type-user="${value.type_user}" user-name="${value.name}"><i class="fa fa-user-shield"></i> Tornar Usuário</button>`;
                }
                if (viewInativeUser) {
                    htmlUser += `<button type="button" class="btn btn-sm btn-${colorBtnStatus} col-md-12 inactivate-user text-left" user-id="${value.id}" user-name="${value.name}">${nameBtnStatus}</button>`;
                }
                if (viewBtnDeleteUser && viewInativeUser) {
                    //htmlUser += `<div class="dropdown-divider"></div>
                    //                <button type="button" class="btn btn-sm btn-danger col-md-12 removeUser text-left" user-id="${value.id}" user-name="${value.name}"><i class="fa fa-user-times"></i> Excluir</button>`;
                }
                htmlUser += `</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                $('#users-registred').append(htmlUser);
            });

            if (openPermissions) {
                setTimeout(() => {
                    $('.viewPermission[user-id="'+user_id+'"]').trigger('click');
                    Toast.fire({
                        icon: 'warning',
                        title: 'Defina as novas permissões para esse usuário'
                    });
                }, 250);
            }

        }, error: e => {
            console.log(e);
        }
    });
}

const showImage = (src,target) => {
    var fr=new FileReader();
    // when image is loaded, set the src of the image where you want to display it
    fr.onload = function(e) { target.src = this.result; };
    src.addEventListener("change",function() {
        // fill fr with image data
        fr.readAsDataURL(src.files[0]);
    });
}
