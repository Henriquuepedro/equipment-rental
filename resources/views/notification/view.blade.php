@extends('adminlte::page')

@section('title', 'Visualizar notificação')

@section('content_header')
    <h1 class="m-0 text-dark">Visualizar notificação</h1>
@stop

@section('css')
    <style>
        #description {
            border-top: 1px solid {{ $settings['style_template'] == 3 ? '#4A4C55' : '#dee2e6' }}
        }
        .ql-toolbar.ql-snow {
            display: none;
        }
        .quill-container {
            height: unset;
        }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            const quill = new Quill('#description', {
                modules: {
                    toolbar: []
                },
                theme: 'snow' // or 'bubble'
            });

            quill.disable()
        })
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
                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Dados da notificação</h4>
                                <p class="card-description"> Visualize o formulário abaixo com as informações da notificação </p>
                            </div>
                            <div class="row mb-2">
                                <div class="form-group col-md-12 text-center">
                                    <h3><i class="{{ $notification->title_icon }}"></i> {{ $notification->title }}</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <div id="description" class="quill-container">{!! $notification->description !!}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <div class="card-body d-flex justify-content-between">
                            <a href="{{ route('notification.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
