<label>Motorista Responsável</label>
<div class="input-group driver-load" data-target="#timepicker-example" data-toggle="datetimepicker">
    <select {{ $disabled ?? '' }} class="form-control label-animate" name="driver">
        <option>Selecione ...</option>
        @foreach($drivers as $_driver)
            <option value="{{ $_driver->id }}" {{ $driverSelected == $_driver->id ? 'selected' : '' }}>{{ $_driver->name }}</option>
        @endforeach
    </select>
    <div class="input-group-addon input-group-append">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newDriverModal" title="Novo Motorista" {{ in_array('DriverCreatePost', $permissions) ? '' : 'disabled' }}><i class="fa fa-user-plus"></i></button>
    </div>
</div>
