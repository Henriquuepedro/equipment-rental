@extends('adminlte::page')

@section('title', 'Cadastro de Atendimento')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Atendimento</h1>
@stop

@section('css')
    <style>
        #descriptionDiv {
            border-top-right-radius: 0;
            border-top-left-radius: 0;
        }
    </style>
@stop

@section('js')
<script src="{{ asset('assets/js/views/support/form.js') }}" type="application/javascript"></script>
<script>
    // Validar dados
    $("#formCreateSupport").validate({
        rules: {
            subject: {
                required: true
            }
        },
        messages: {
            subject: {
                required: 'Informe o assunto'
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
            $('#formCreateSupport [type="submit"]').attr('disabled', true);

            const description = $("#descriptionDiv .ql-editor").html();

            if (description === '<p><br></p>') {
                Toast.fire({
                    icon: 'warning',
                    title: 'Informe a descrição'
                });
                $('#formCreateSupport [type="submit"]').attr('disabled', false);
                return false;
            }

            $("#description").val(description);

            let verifyAddress = verifyAddressComplet(true);
            if (!verifyAddress[0]) {
            }

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
                    <form action="{{ route('support.insert') }}" method="POST" enctype="multipart/form-data" id="formCreateSupport">
                        <div class="card">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do atendimento</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações do novo atendimento </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="subject">Assunto</label>
                                        <input type="text" class="form-control" id="subject" name="subject" autocomplete="nope" value="{{ old('subject') }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 mt-3">
                                        <h5>Descrição</h5>
                                        <div id="descriptionDiv" class="quill-container"></div>
                                        <textarea type="hidden" class="d-none" name="description" id="description"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('support.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                            </div>
                        </div>
                        <input type="hidden" name="path_files" value="{{ $path_files }}">
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="route_to_save_image_support" value="{{ route('ajax.support.save_image_description', array('path' => $path_files)) }}">
@stop
