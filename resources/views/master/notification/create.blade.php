@extends('adminlte::page')

@section('title', 'Cadastro de Notificação')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Notificação</h1>
@stop

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('assets/vendors/dropify/dropify.min.css') }}">
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <style>
        [name="expires_in"] {
            border-bottom-right-radius: 0 !important;
            border-top-right-radius: 0 !important;
        }
        .flatpickr a.input-button:last-child {
            border-bottom-right-radius: 5px;
            border-top-right-radius: 5px;
        }
        select#title_icon{
            font-family: fontAwesome
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
    <script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>
    <script src="{{ asset('assets/vendors/dropify/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/js/shared/dropify.js') }}"></script>
    <script>
        $(function(){
            new Quill('#description_content', {
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'align': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['link']
                    ]
                },
                theme: 'snow' // or 'bubble'
            });

            $('.flatpickr').flatpickr({
                enableTime: true,
                dateFormat: "d/m/Y H:i",
                time_24hr: true,
                wrap: true,
                clickOpens: false,
                allowInput: true,
                locale: "pt",
                onClose: function(selectedDates, dateStr, instance){
                    checkLabelAnimate();
                }
            });
        })
        // Validar dados
        $("#formCreateNotification").validate({
            rules: {
                title: {
                    required: true
                }
            },
            messages: {
                title: {
                    required: 'Informe um título para o notificação'
                }
            },
            invalidHandler: function(event, validator) {
                $('html, body').animate({scrollTop:0}, 100);
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
                }, 150);
            },
            submitHandler: function(form) {
                $('#formCreateNotification [type="submit"]').attr('disabled', true);
                $("#description").val($("#description_content .ql-editor").html());
                form.submit();
            }
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    @if ($errors->any())
                        <div class="alert alert-animate alert-warning">
                            <ol>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                    <form action="{{ route(empty($notification) ? 'master.notification.insert' : 'master.notification.update',  empty($notification) ? [] : array('id' => $notification->id)) }}" method="POST" enctype="multipart/form-data" id="formCreateNotification">
                        <div class="card">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados da Notificação</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações da nova notificação </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="company_id">Ícone</label>
                                        <select class="form-control" name="title_icon" id="title_icon">
                                            <option value=""></option>
                                            @include('includes.icon.option', ['selected' => $notification->title_icon ?? ''])
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="title">Título da notificação <sup>*</sup></label>
                                        <input type="text" class="form-control" id="title" name="title" autocomplete="nope" value="{{ old('title', $notification->title ?? '') }}" required>
                                    </div>
                                    <div class="form-group col-md-3 d-flex align-items-center">
                                        <div class="switch d-flex mt-3">
                                            <input type="checkbox" class="check-style check-xs" name="active" id="active" {{ old('active', isset($notification) ? ($notification->active) : true) ? 'checked' : '' }}>
                                            <label for="active" class="check-style check-xs"></label>&nbsp;Ativo
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 mt-3">
                                        <h5>Descrição <sup>*</sup></h5>
                                        <div id="description_content" class="quill-container">{!! $notification->description ?? '' !!}</div>
                                        <textarea type="hidden" class="d-none" name="description" id="description"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="company_id">Empresa que terá acesso</label>
                                        <select class="form-control select2" name="company_id" id="company_id">
                                            <option value="">Todas</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ ($notification->company_id ?? '') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="company_id">Permissão de usuário</label>
                                        <select class="form-control select2" name="only_permission" id="only_permission">
                                            <option value="">Todas</option>
                                            @foreach($user_permissions as $permission)
                                                <option value="{{ $permission->id }}" {{ ($notification->only_permission ?? '') == $permission->id ? 'selected' : '' }}>[{{ $permission->text }}] {{ $permission->group_text }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group flatpickr label-animate">
                                            <label class="label-date-btns">Data de expiração</label>
                                            <input type="tel" name="expires_in" class="form-control col-md-9 pull-left" value="{{ !empty($notification->expires_in) ? dateInternationalToDateBrazil($notification->expires_in, DATETIME_BRAZIL_NO_SECONDS) : '' }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
                                            <div class="input-button-calendar col-md-3 no-padding pull-right">
                                                <a class="input-button pull-left btn-primary btn btn-sm" title="toggle" data-toggle>
                                                    <i class="fa fa-calendar text-white"></i>
                                                </a>
                                                <a class="input-button pull-right btn-primary btn btn-sm" title="clear" data-clear>
                                                    <i class="fa fa-times text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-2">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('master.notification.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
