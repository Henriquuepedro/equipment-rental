@extends('adminlte::page')

@section('title', 'Listagem de Resíduos')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Resíduos</h1>
@stop

@section('css')
@stop

@section('js')
    <script src="{{ asset('assets/js/views/residue/form.js') }}" type="application/javascript"></script>
    @if(in_array('ResidueCreatePost', $permissions))@include('includes.residue.modal-script')@endif
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin">
            @if(session('success'))
                <div class="alert alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Resíduos Cadastrados</h4>
                        @if(in_array('ResidueCreatePost', $permissions))
                        <button data-bs-toggle="modal" data-bs-target="#newResidueModal" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</button>
                        @endif
                    </div>
                    <table id="tableResidues" class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Criado Em</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th>Nome</th>
                                <th>Criado Em</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="fetchResidue" value="{{ route('ajax.residue.fetch') }}">
    <input type="hidden" id="deleteResidue" value="{{ route('ajax.residue.delete') }}">

    @if(in_array('ResidueCreatePost', $permissions))@include('includes.residue.modal-create')@endif
    @if(in_array('ResidueUpdatePost', $permissions))
    <div class="modal fade" id="editResidueModal" tabindex="-1" role="dialog" aria-labelledby="editResidueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form action="{{ route('ajax.residue.edit-residue') }}" method="POST" id="formUpdateResidueModal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editResidueModalLabel">Atualizar resíduo</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="name">Nome do Resíduo <sup>*</sup></label>
                                        <input type="text" class="form-control" name="name" autocomplete="nope" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-bs-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Atualizar</button>
                    </div>
                        <input type="hidden" name="residue_id">
                </form>
            </div>
        </div>
    </div>
    @endif

@stop
