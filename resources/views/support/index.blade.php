@extends('adminlte::page')

@section('title', 'Atendimento ao cliente')

@section('content_header')
    <h1 class="m-0 text-dark">Atendimento ao cliente</h1>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        .board-wrapper .portlet-card .badge {
            grid-column-end: 3;
        }
        .chat-app-wrapper .chat-container-wrapper {
            min-height: 10vh;
        }
        #modalViewSupport .chat-message img,
        #modalViewSupport .description img,
        #modalUpdatePriority .description img,
        #modalUpdateStatus .description img {
            width: 100%;
        }
        #modalViewSupport .sender-details .sender-avatar {
            margin-top: -105px;
        }
        #modalViewSupport .outgoing-chat .sender-details>span {
            margin-right: -40px;
        }
        #modalViewSupport .incoming-chat .sender-details>span {
            margin-left: -40px;
        }

        .chat-container-wrapper {
            max-height: 450px !important;
        }

        .chat-container-wrapper::-webkit-scrollbar {
            width: 5px;
        }

        .chat-container-wrapper::-webkit-scrollbar-track {
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .chat-container-wrapper::-webkit-scrollbar-thumb {
            background-color: darkgrey;
            outline: 1px solid slategrey;
        }

        @cannot('admin-master')
        .board-wrapper .portlet-card {
            cursor: default;
        }
        @endcannot

    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="{{ asset('assets/js/views/support/form.js') }}" type="application/javascript"></script>
    <script>
        $(function () {
            getListSupport();
            loadDaterangePickerInput($('input[name="intervalDates"]'), function () {});
            @can('admin-master')
            $('ul[id^="portlet-card-list-"]').sortable({
                connectWith: 'ul[id^="portlet-card-list-"]',
                items: ".portlet-card",
                update:  function (event, ui) {
                    if (this === ui.item.parent()[0]) {
                        const code_support = $(ui.item[0]).find('h4.task-title').text();
                        const id_support = $(ui.item[0]).data('supportId');
                        const new_status_name = $(ui.item[0]).closest('.board-portlet').find('h4.portlet-heading').text();
                        const new_status_code = $(ui.item[0]).closest('ul').data('status');
                        Swal.fire({
                            title: 'Alterar situação',
                            html: `Deseja alterar a situação do atendimento: <br><br>${code_support} <br><br>para <b>${new_status_name}</b>?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#19d895',
                            cancelButtonColor: '#bbb',
                            confirmButtonText: 'Sim, Alterar',
                            cancelButtonText: 'Cancelar',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                updateStatus(id_support, new_status_code, false, false);
                            } else {
                                ui.sender.sortable("cancel");
                            }
                        });
                    }
                }
            });
            @endcan
        });

        const getListSupport = () => {
            $('.board-wrapper ul').empty();
            $(`#kanban-task-number-*`).text('0 atendimentos');
            $(`p[id^="kanban-task-number-"]`).html('<i class="fa fa-spin fa-spinner"></i> atendimentos');
            $(`ul[id^="portlet-card-list-"]`).append('<li class="portlet-card"><div class="col-md-12 d-flex justify-content-center"><i class="fa fa-spin fa-spinner"></i></div></li>');

            const company = $('#company').val();
            const priority = $('#priority').val();
            const interval_dates = $('#intervalDates').val();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'GET',
                url: $('[name="route_get_all_supports"]').val(),
                data: { company, priority, interval_dates },
                dataType: 'json',
                success: response => {
                    let task_number = [];
                    let buttons = '';
                    let content_aditional = '';
                    $('.board-wrapper ul').empty();
                    $(`p[id^="kanban-task-number-"]`).html('0 atendimentos');
                    $(response).each(function (k, value) {
                        content_aditional = '';
                        if (typeof task_number[value.status] === "undefined")  {
                            $(`#kanban-task-number-${value.status}`).text('1 atendimento');
                            task_number[value.status] = 1;
                        } else {
                            task_number[value.status] += 1;
                            $(`#kanban-task-number-${value.status}`).text(`${task_number[value.status]} atendimentos`);
                        }

                        buttons = `<button class="dropdown-item btnViewSupport" data-support-id="${value.id}" data-status="${value.status}">Visualizar</button>`;
                        @can('admin-master') buttons += `<button class="dropdown-item btnOpenUpdatePriority" data-support-id="${value.id}">Alterar Prioridade</button>` @endcan;
                        @can('admin-master') buttons += `<button class="dropdown-item btnOpenUpdateStatus" data-support-id="${value.id}">Alterar Situação</button>` @endcan;
                        @can('admin-master') content_aditional = `<p class="text-primary mb-0" style="grid-column-end: 2"><i class="fa-regular fa-building"></i> ${value.company_name}</p>` @endcan;
                        @can('admin-master') content_aditional += `<p class="text-info mb-0" style="grid-column-end: 2"><i class="fa-solid fa-user"></i> ${value.user_name}</p>` @endcan;

                        $(`#portlet-card-list-${value.status}`).append(
                            `<li class="portlet-card" data-status="${value.status}" data-support-id="${value.id}">
                                <p class="task-date">${value.created_at}</p>
                                <div class="action-dropdown dropdown">
                                    <button type="button" class="dropdown-toggle" id="portlet-action-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="portlet-action-dropdown">
                                        ${buttons}
                                    </div>
                                </div>
                                <h4 class="task-title">#${value.code} - ${value.subject}</h4>
                                ${content_aditional}
                                <div class="badge badge-inverse-${value.priority_color}">${value.priority_name}</div>
                            </li>`
                        );
                    });
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
                        $(`button[data-client-id="${client_id}"]`).trigger('blur');
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

        const loadModal = (idModal, support_id, open_modal = true, status = null) => {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'GET',
                url: $('[name="route_view_support"]').val() + `/${support_id}`,
                dataType: 'json',
                success: response => {
                    let sent_by;

                    $(`#${idModal} .modal-title`).text(response.support.subject);
                    $(`#${idModal} [name="support_id"]`).val(support_id);
                    $(`#${idModal} .modal-body .description`).html(response.support.description);

                    if (idModal === 'modalViewSupport') {
                        $(`#${idModal} .chat-container-wrapper`).empty();
                        $(`#${idModal} [name="path_files"]`).val(response.support.path_files);
                        $(`#${idModal} [name="mark_close"]`).prop('checked', false);
                        editorQuill.deleteText(0,editorQuill.getLength());

                        $('#modalViewSupport .hidden-support-closed').css({display: status === 'closed' ? 'none' : 'block'});

                        $(response.support_message).each(function (key, value) {
                            sent_by = value.sent_by === 'user' ? 'outgoing' : 'incoming';

                            $(`#${idModal} .chat-container-wrapper`).append(
                                `<div class="chat-bubble ${sent_by}-chat">
                                <div class="chat-message">
                                   ${value.description}
                                </div>
                                <div class="sender-details">
                                    <img class="sender-avatar img-xs rounded-circle" src="${value.logo_message}" alt="profile image"><span class="font-weight-bold">&nbsp;${value.user_name}&nbsp;</span>
                                    <p class="seen-text pl-1 pr-0">${value.created_at}</p>
                                </div>
                            </div>`
                            );
                        });

                        if (!response.support_message.length) {
                            $(`#${idModal} .chat-container-wrapper`).append('<div class="row"><div class="col-md-12 d-flex justify-content-center mt-3"><h5>Atendimento sem comentários <i class="fa-solid fa-ban"></i></h5></div></div>')
                        }
                    } else if (idModal === 'modalUpdatePriority') {
                        $(`#${idModal} [name="old_priority"]`).val(response.support.priority_name);
                        $(`#${idModal} [name="new_priority"]`).val(response.support.priority);
                        checkLabelAnimate();
                    } else if (idModal === 'modalUpdateStatus') {
                        $(`#${idModal} [name="old_status"]`).val(response.support.status_name);
                        $(`#${idModal} [name="new_status"]`).val(response.support.status);
                        checkLabelAnimate();
                    }

                    if (open_modal) {
                        $(`#${idModal}`).modal();
                    }

                    $('.chat-container-wrapper').animate({
                        scrollTop: $(document).height()
                    }, 500);
                }, error: e => {
                    console.log(e);
                    Swal.fire({
                        icon: 'error',
                        title: e.responseJSON.message ?? 'Não foi possível localizar o atendimento.'
                    });
                }
            });
        }

        $(document).on('click', '.btnViewSupport', function(){
            const support_id = $(this).data('support-id');
            const status = $(this).data('status');
            loadModal('modalViewSupport', support_id, true, status);
        });

        $('#sendComment').on('click', function(){
            const btn = $(this);
            const description = $("#descriptionDiv .ql-editor").html();
            const support_id = $('#modalViewSupport [name="support_id"]').val();
            const mark_close = $('[name="mark_close"]').is(':checked') ? 1 : 0;

            if (description === '<p><br></p>') {
                Toast.fire({
                    icon: 'warning',
                    title: 'Informe a descrição'
                });
                $('#formCreateSupport [type="submit"]').attr('disabled', false);
                return false;
            }

            btn.attr('disabled', true);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('[name="route_create_comment"]').val() + `/${support_id}`,
                data: {
                    description,
                    mark_close
                },
                dataType: 'json',
                success: response => {
                    if (mark_close) {
                        $('#modalViewSupport').modal('hide');
                    } else {
                        loadModal('modalViewSupport', support_id, false);
                    }
                    getListSupport();
                },
                complete: () => {
                    btn.attr('disabled', false);
                }
            });
        });

        $('#company, #priority, #intervalDates').on('change', function(){
            getListSupport();
        });

        @can('admin-master')
        $(document).on('click', '.btnOpenUpdatePriority', function(){
            const support_id = $(this).data('support-id');
            loadModal('modalUpdatePriority', support_id, true);
        });

        $(document).on('click', '.btnOpenUpdateStatus', function(){
            const support_id = $(this).data('support-id');
            loadModal('modalUpdateStatus', support_id, true);
        });

        $('#btnUpdatePriority').on('click', function(){
            const btn = $(this);
            const new_priority = $('#modalUpdatePriority [name="new_priority"]').val();
            const support_id = $('#modalUpdatePriority [name="support_id"]').val();

            if (new_priority === '') {
                Toast.fire({
                    icon: 'warning',
                    title: 'Informe uma prioridade'
                });
                $('#formCreateSupport [type="submit"]').attr('disabled', false);
                return false;
            }

            btn.attr('disabled', true);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('[name="route_support_update_priority"]').val() + `/${support_id}`,
                data: {
                    new_priority
                },
                dataType: 'json',
                success: response => {
                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.message
                    });
                    if (response.success) {
                        $('#modalUpdatePriority').modal('hide');
                        getListSupport();
                    }
                },
                complete: () => {
                    btn.attr('disabled', false);
                }
            });
        });

        $('#btnUpdateStatus').on('click', function(){
            const btn = $(this);
            const new_status = $('#modalUpdateStatus [name="new_status"]').val();
            const support_id = $('#modalUpdateStatus [name="support_id"]').val();

            if (new_status === '') {
                Toast.fire({
                    icon: 'warning',
                    title: 'Informe uma prioridade'
                });
                $('#formCreateSupport [type="submit"]').attr('disabled', false);
                return false;
            }

            btn.attr('disabled', true);

            updateStatus(support_id, new_status).done(() => {
                btn.attr('disabled', false);
            });
        });
        @endcan

        const updateStatus = (support_id, new_status, close_modal = true, reload_list = true) => {
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('[name="route_support_update_status"]').val() + `/${support_id}`,
                data: {
                    new_status
                },
                dataType: 'json',
                success: response => {
                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.message
                    });
                    if (response.success) {
                        if (close_modal) {
                            $('#modalUpdateStatus').modal('hide');
                        }
                        if (reload_list) {
                            getListSupport();
                        }
                    }
                }
            });
        }
    </script>
    @if(in_array('BillsToReceiveView', $permissions)) <script src="{{ asset('assets/js/views/bill_to_receive/index.js') }}" type="application/javascript"></script> @endif
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="d-flex flex-column flex-md-row">
                <h4 class="">Atendimento ao cliente</h4>
                <div class="wrapper ml-md-auto d-flex flex-column flex-md-row kanban-toolbar ml-n2 ml-md-0 mt-md-0">
                    <div class="d-flex mt-md-0">
                        <a type="button" class="btn btn-success" href="{{ route('support.create') }}"><i class="fa fa-plus"></i> Novo Atendimento</a>
                    </div>
                </div>
            </div>
            <div class="card mb-2 mt-2">
                <div class="card-body">
                    <div class="row">
                        @if (count($companies))
                        <div class="form-group col-md-4 @if (count($companies) === 1) d-none @endif">
                            <label for="company">Empresas</label>
                            <select class="form-control select2" id="company" name="company">
                                @if (count($companies) !== 1)<option value="0">Selecione a empresa</option>@endif
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="form-group col-md-4">
                            <label for="company">Prioridade</label>
                            <select class="form-control select2" id="priority" name="priority">
                                <option value="0">Selecione a prioridade</option>
                                <option value="new">Novo</option>
                                <option value="low">Baixo</option>
                                <option value="medium">Médio</option>
                                <option value="high">Alto</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Data do Atendimento</label>
                            <input type="text" name="intervalDates" id="intervalDates" class="form-control" value="{{ formatDateInternational(subDate(dateNowInternational(), 2), DATE_BRAZIL) . ' - ' . $settings['intervalDates']['finish'] }}" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="board-wrapper pt-5">
                        <div class="board-portlet">
                            <h4 class="portlet-heading">Novo</h4>
                            <p class="task-number" id="kanban-task-number-open">0 atendimentos</p>
                            <ul id="portlet-card-list-open" class="portlet-card-list" data-status="open"></ul>
                        </div>
                        <div class="board-portlet">
                            <h4 class="portlet-heading">Em atendimento</h4>
                            <p class="task-number" id="kanban-task-number-ongoing">0 atendimentos</p>
                            <ul id="portlet-card-list-ongoing" class="portlet-card-list" data-status="ongoing"></ul>
                        </div>
                        <div class="board-portlet">
                            <h4 class="portlet-heading">Aguardando retorno</h4>
                            <p class="task-number" id="kanban-task-number-awaiting_return">0 atendimentos</p>
                            <ul id="portlet-card-list-awaiting_return" class="portlet-card-list" data-status="awaiting_return"></ul>
                        </div>
                        <div class="board-portlet">
                            <h4 class="portlet-heading">Finalizado</h4>
                            <p class="task-number" id="kanban-task-number-closed">0 atendimentos</p>
                            <ul id="portlet-card-list-closed" class="portlet-card-list" data-status="closed"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class="modal fade" id="modalViewSupport" tabindex="-1" role="dialog" aria-labelledby="modalViewSupport" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">...</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="accordion basic-accordion" id="accordion" role="tablist">
                        <div class="card">
                            <div class="card-header" role="tab" id="headingDescription">
                                <h6 class="mb-0">
                                    <a data-toggle="collapse" href="#collapseDescription" aria-expanded="false" aria-controls="collapseDescription">
                                        <i class="fa-regular fa-file-lines"></i> Desccrição
                                    </a>
                                </h6>
                            </div>
                            <div id="collapseDescription" class="collapse" role="tabpanel" aria-labelledby="headingDescription" data-parent="#accordion">
                                <div class="card-body">
                                    <div class="description"></div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="old-messages">
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-secondary py-3 mb-4 text-center d-md-none aside-toggler"><i class="mdi mdi-menu mr-0 icon-md"></i></button>
                                <div class="card chat-app-wrapper">
                                    <div class="row mx-0">
                                        <div class="col-lg-12 col-md-12 px-0 d-flex flex-column">
                                            <h5 class="d-flex justify-content-center"><i class="fa-solid fa-arrow-down-long"></i>&nbsp;&nbsp;Comentários&nbsp;&nbsp;<i class="fa-solid fa-arrow-down-long"></i></h5>
                                            <div class="chat-container-wrapper"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row hidden-support-closed">
                            <div class="form-group col-md-12 mt-3">
                                <h5>Comentário</h5>
                                <div id="descriptionDiv" class="quill-container"></div>
                                <textarea type="hidden" class="d-none" name="description" id="description"></textarea>
                            </div>
                        </div>
                        <div class="row hidden-support-closed">
                            <div class="card-body d-flex justify-content-between">
                                <label><input type="checkbox" name="mark_close"> Marcar como concluído</label>
                                <button type="button" class="btn btn-success col-md-3" id="sendComment"><i class="fa fa-save"></i> Adicionar comentário</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-around">
                    <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                </div>
            </div>
        </div>
        <input type="hidden" name="path_files">
        <input type="hidden" name="support_id">
    </div>




    <div class="modal fade" id="modalUpdatePriority" tabindex="-1" role="dialog" aria-labelledby="modalUpdatePriority" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alterar Prioridade</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="accordion basic-accordion" id="accordion" role="tablist">
                        <div class="card">
                            <div class="card-header" role="tab" id="headingDescription">
                                <h6 class="mb-0">
                                    <a data-toggle="collapse" href="#collapseDescription" aria-expanded="false" aria-controls="collapseDescription">
                                        <i class="fa-regular fa-file-lines"></i> Desccrição
                                    </a>
                                </h6>
                            </div>
                            <div id="collapseDescription" class="collapse" role="tabpanel" aria-labelledby="headingDescription" data-parent="#accordion">
                                <div class="card-body">
                                    <div class="description"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="form-group col-md-4">
                            <label>Prioridade Atual</label>
                            <input type="text" class="form-control" name="old_priority" disabled />
                        </div>
                        <div class="form-group col-md-4">
                            <label>Nova Prioridade</label>
                            <select class="form-control" name="new_priority">
                                <option value="new">Novo</option>
                                <option value="low">Baixo</option>
                                <option value="medium">Médio</option>
                                <option value="high">Alto</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-around">
                    <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                    <button type="button" class="btn btn-success col-md-3" id="btnUpdatePriority"><i class="fa fa-save"></i> Alterar Prioridade</button>
                </div>
            </div>
        </div>
        <input type="hidden" name="support_id">
    </div>



    <div class="modal fade" id="modalUpdateStatus" tabindex="-1" role="dialog" aria-labelledby="modalUpdateStatus" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alterar Situação</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="accordion basic-accordion" id="accordion" role="tablist">
                        <div class="card">
                            <div class="card-header" role="tab" id="headingDescription">
                                <h6 class="mb-0">
                                    <a data-toggle="collapse" href="#collapseDescription" aria-expanded="false" aria-controls="collapseDescription">
                                        <i class="fa-regular fa-file-lines"></i> Desccrição
                                    </a>
                                </h6>
                            </div>
                            <div id="collapseDescription" class="collapse" role="tabpanel" aria-labelledby="headingDescription" data-parent="#accordion">
                                <div class="card-body">
                                    <div class="description"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="form-group col-md-4">
                            <label>Situação Atual</label>
                            <input type="text" class="form-control" name="old_status" disabled />
                        </div>
                        <div class="form-group col-md-4">
                            <label>Nova Situação</label>
                            <select class="form-control" name="new_status">
                                <option value="open">Novo</option>
                                <option value="ongoing">Em atendimento</option>
                                <option value="awaiting_return">Aguardando retorno</option>
                                <option value="closed">Finalizado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-around">
                    <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                    <button type="button" class="btn btn-success col-md-3" id="btnUpdateStatus"><i class="fa fa-save"></i> Alterar Situação</button>
                </div>
            </div>
        </div>
        <input type="hidden" name="support_id">
    </div>



    <input type="hidden" name="route_get_all_supports" value="{{ route('ajax.support.listSupports') }}">
    <input type="hidden" name="route_view_support" value="{{ route('ajax.support.get_support') }}">
    <input type="hidden" name="route_to_save_image_support" value="{{ route('ajax.support.save_image_description') }}">
    <input type="hidden" name="route_create_comment" value="{{ route('ajax.support.register_comment') }}">
    <input type="hidden" name="route_support_update_priority" value="{{ route('ajax.support.update_priority') }}">
    <input type="hidden" name="route_support_update_status" value="{{ route('ajax.support.update_status') }}">
@stop
