@php
    $providerSelected = old() ? old('provider') : (isset($rental) ? $rental->provider_id : '');
@endphp
<label>Fornecedor <sup>*</sup></label>
<div class="input-group provider-load" data-target="#timepicker-example" data-toggle="datetimepicker">
    <select {{ $disabled ?? '' }} class="form-control label-animate required" name="provider" required></select>
    <div class="input-group-addon input-group-append">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newProviderModal" title="Novo Fornecedor" {{ in_array('BillsToPayCreatePost', $permissions) ? '' : 'disabled' }}><i class="fa fa-user-plus"></i></button>
    </div>
</div>
