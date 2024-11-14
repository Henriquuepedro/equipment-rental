@extends('adminlte::page')

@section('title', 'Cadastro de Manual')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Manual</h1>
@stop

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('assets/vendors/dropify/dropify.min.css') }}">
@stop

@section('js')
    <script src="{{ asset('assets/vendors/dropify/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/js/shared/dropify.js') }}"></script>
    <script>
        // Validar dados
        $("#formCreateGuide").validate({
            rules: {
                title: {
                    required: true
                },
                file: {
                    extension: "pdf"
                }
            },
            messages: {
                name: {
                    required: 'Informe um título para o manual'
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
                $('#formCreateGuide [type="submit"]').attr('disabled', true);
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
                    <form action="{{ route('master.guide.update', ['id' => $guide->id]) }}" method="POST" enctype="multipart/form-data" id="formCreateGuide">
                        <div class="card">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Manual</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações do novo manual </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="title">Título do Manual <sup>*</sup></label>
                                        <input type="text" class="form-control" id="title" name="title" autocomplete="nope" value="{{ old('title', $guide->title) }}" required>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-lg-12">
                                        <label for="file">Arquivo do Manual <sup>*</sup></label>
                                        <div class="card">
                                            <input type="file" name="file" class="dropify" data-allowed-file-extensions="pdf" data-default-file="{{ "assets/files/guides/$guide->file/guide.pdf" }}"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-2">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('master.guide.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
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
