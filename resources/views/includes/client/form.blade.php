@php
    $clientSelected = old() ? old('client') : (isset($rental) ? $rental->client_id : '');
@endphp
<label>Cliente</label>
<div class="input-group client-load" data-target="#timepicker-example" data-toggle="datetimepicker">
    <select {{ $disabled ?? '' }} class="form-control label-animate required" name="client" required></select>
    <div class="input-group-addon input-group-append">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newClientModal" title="Novo Cliente" {{ in_array('ClientCreatePost', $permissions) ? '' : 'disabled' }}><i class="fa fa-user-plus"></i></button>
    </div>
</div>
