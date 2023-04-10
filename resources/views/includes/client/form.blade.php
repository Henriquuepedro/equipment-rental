@php
    $clientSelected = old() ? old('client') : (isset($rental) ? $rental->client_id : '');
    $show_btn_create = !isset($show_btn_create) || $show_btn_create;
    $required = !isset($required) || $required ? 'required' : '';
@endphp
<label>Cliente @if($required)<sup>*</sup>@endif</label>
@if($show_btn_create)
<div class="input-group client-load d-flex flex-nowrap" data-target="#timepicker-example" data-toggle="datetimepicker">
@endif
    <select {{ $disabled ?? '' }} class="form-control label-animate {{ $required }}" name="client" {{ $required }}></select>
    @if($show_btn_create)
    <div class="input-group-addon input-group-append">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newClientModal" title="Novo Cliente" {{ in_array('ClientCreatePost', $permissions) ? '' : 'disabled' }}><i class="fa fa-user-plus"></i></button>
    </div>
    @endif
@if($show_btn_create)
</div>
@endif
