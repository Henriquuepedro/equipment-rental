<label>Motorista Responsável</label>
<div class="input-group driver-load" data-bs-target="#timepicker-example" data-bs-toggle="datetimepicker">
    <select {{ $disabled ?? '' }} class="form-control label-animate" name="driver">
        <option>Selecione ...</option>
        @foreach($drivers as $_driver)
            <option value="{{ $_driver->id }}" {{ $driverSelected == $_driver->id ? 'selected' : '' }}>{{ $_driver->name }}</option>
        @endforeach
    </select>
    <div class="input-group-addon input-group-append">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newDriverModal" title="Novo Motorista" {{ in_array('DriverCreatePost', $permissions) ? '' : 'disabled' }}><i class="fa fa-user-plus"></i></button>
    </div>
</div>
