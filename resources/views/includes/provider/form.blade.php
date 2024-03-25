@php
    $providerSelected = old() ? old('provider') : (isset($rental) ? $rental->provider_id : '');
    $show_btn_create = !isset($show_btn_create) || $show_btn_create;
    $required = !isset($required) || $required ? 'required' : '';
@endphp
<label>Fornecedor @if($required)<sup>*</sup>@endif</label>
@if($show_btn_create)
<div class="input-group provider-load" data-bs-target="#timepicker-example" data-bs-toggle="datetimepicker">
@endif
    <select {{ $disabled ?? '' }} class="form-control label-animate select2 {{ $required }}" name="provider" {{ $required }}></select>
    @if($show_btn_create)
    <div class="input-group-addon input-group-append">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newProviderModal" title="Novo Fornecedor" {{ in_array('BillsToPayCreatePost', $permissions) ? '' : 'disabled' }}><i class="fa fa-user-plus"></i></button>
    </div>
    @endif
@if($show_btn_create)
</div>
@endif
