@extends('adminlte::page')

@section('title', 'Listagem de Locações')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Locações</h1>
@stop

@section('css')
@stop

@section('js')
    <script>

    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin">
            @if(session('success'))
                <div class="alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Locações Realizadas</h4>
                        @if(in_array('RentalCreatePost', $permissions))
                        <a href="{{ route('rental.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Nova Locação</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
