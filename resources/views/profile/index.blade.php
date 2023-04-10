@extends('adminlte::page')

@section('title', 'Meu Perfil')

@section('content_header')
    <h1 class="m-0 text-dark">Meu Perfil</h1>
@stop

@section('css')
    <style>
        .profile-edit label {
            position: relative;
            top: -55px;
            right: -65px;
            border-radius: 50%;
            height: 35px;
            width: 35px;
            display: flex !important;
            align-items: center;
            justify-content: center;
            background-color: #ddd;
            color: #666;
            /*box-shadow: 0 0 8px 3px #B8B8B8;*/
            border: 3px solid #fff;
            cursor: pointer;
        }
        .profile-edit label:hover {
            color: #222;
            background-color: #bbb;
        }
        .profile-edit i {
            font-size: 17px;
            padding-top: 2px;
        }
        .btns-profile-image{
            display: none;
            position: absolute;
            top: 200px;
        }
        #src-profile-image {
            border: 3px solid #fff;
        }
    </style>
@stop

@section('js')
    <script>
        var imageOriginal = $('#src-profile-image').prop('src');

        $(() => {
            $('[name="phone"]').mask('(00) 000000000');
            var src = document.getElementById("profile-image");
            var target = document.getElementById("src-profile-image");
            showImage(src,target);
        });

        $('#cancel-profile-img').on('click', function (){
            $('.btns-profile-image').slideUp('slow');
            $('#src-profile-image').attr('src', imageOriginal);
        });

        $('#save-profile-img').on('click', () => {
            let formData = new FormData($('#updateProfileImage')[0]);
            let file = $('#profile-image')[0].files[0];
            formData.append('image', file, file.name);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('ajax.profile.update.image') }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                cache: false,
                dataType: 'json',
                success: response => {
                    if (!response.success) {
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        });
                        return false;
                    }

                    Toast.fire({
                        icon: 'success',
                        title: 'Imagem do perfil atualizada com sucesso.'
                    });

                    $('.btns-profile-image').slideUp('slow');
                    $('#profile-image').val('');
                    $('#UserDropdown img').attr('src', response.path);
                    $('[aria-labelledby="UserDropdown"] img').attr('src', response.path);
                    imageOriginal = response.path;
                }, error: e => {
                    console.log(e);
                }
            });
        });

        // Validar dados
        $("#formUpdateProfile").validate({
            rules: {
                name: {
                    required: true
                },
                phone: {
                    required: true,
                    rangelength: [13, 14]
                },
                password_current: {
                    required: function(element){
                        return $("#password").val()!="";
                    }
                },
                password: {
                    minlength: 6
                },
                password_confirmation: {
                    equalTo: "#password"
                }
            },
            messages: {
                name: {
                    required: 'É obrigatório informar seu nome!'
                },
                phone: {
                    required: "O número de telefone precisa ser preenchido!",
                    rangelength: "O número de telefone principal está inválido, informe um válido. (99) 999..."
                },
                password_current: {
                    required: "É preciso informar a senha atual para atualizar a senha!"
                },
                password: {
                    minlength: "A nova senha deve conter no mínimo 6 dígitos!"
                },
                password_confirmation: {
                    equalTo: "As senhas não se correspondem!"
                }
            },
            invalidHandler: function(event, validator) {
                $('html, body').animate({scrollTop:0}, 400);
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
                }, 500);
            },
            submitHandler: function(form) {
                form.submit();
            }
        });

        const showImage = (src,target) => {
            var fr=new FileReader();
            // when image is loaded, set the src of the image where you want to display it
            fr.onload = function(e) { target.src = this.result; };
            src.addEventListener("change",function() {
                // fill fr with image data
                fr.readAsDataURL(src.files[0]);
                $('.btns-profile-image').slideDown('slow');
            });
        }
    </script>
@stop

@section('content')
    <div class="row profile-page">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert-animate alert-warning">
                    <ol>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
            @if(session('success'))
                <div class="alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="profile-header text-white">
                        <div class="d-flex justify-content-center justify-content-md-between mx-4 mx-xl-5 px-xl-5 flex-wrap">
                            <div class="profile-info d-flex align-items-center justify-content-center flex-wrap mr-sm-3">
                                <div class="profile-edit">
                                    <img class="rounded-circle img-lg mb-3 mb-sm-0" src="{{ $settings['img_profile'] }}" id="src-profile-image" alt="profile image">
                                    <form method="POST" action="" enctype="multipart/form-data" id="updateProfileImage">
                                        <input type="file" name="profile-image" id="profile-image" class="display-none">
                                        <label for="profile-image"><i class="mdi mdi-camera"></i></label>
                                    </form>
                                    <div class="btns-profile-image">
                                        <button class="btn btn-danger" id="cancel-profile-img"><i class="fa fa-times"></i></button>
                                        <button class="btn btn-success" id="save-profile-img"><i class="fa fa-check"></i></button>
                                    </div>
                                </div>
                                <div class="wrapper pl-sm-4">
                                    <h5 class="profile-user-name text-center text-sm-left">{{ auth()->user()->name }}</h5>
                                    <div class="wrapper d-flex align-items-center justify-content-start flex-wrap">
                                        <p class="profile-user-designation text-center text-md-left my-2 my-md-0 text-uppercase">{{ $settings['name_company'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-body">
                        <ul class="nav tab-switch" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="user-profile-info-tab" data-toggle="pill" href="#user-profile-info" role="tab" aria-controls="user-profile-info" aria-selected="true">Perfil</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="user-config-info-tab" data-toggle="pill" href="#user-config" role="tab" aria-controls="user-config" aria-selected="true">Configurações</a>
                            </li>
                        </ul>
                        <form action="{{ route('profile.update') }}" method="POST" class="row" id="formUpdateProfile">
                            <div class="col-md-12">
                                <div class="tab-content tab-body" id="profile-log-switch">
                                    <div class="tab-pane fade show active" id="user-profile-info" role="tabpanel" aria-labelledby="user-profile-info-tab">
                                        <div class="row pb-4">
                                            <div class="form-group col-md-4">
                                                <label>Nome Completo</label>
                                                <input type="text" class="form-control" name="name" value="{{ old('name') ?? $user->name }}" required>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>Telefone</label>
                                                <input type="text" class="form-control" name="phone" value="{{ old('phone') ?? $user->phone }}" required>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>E-mail</label>
                                                <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="accordion basic-accordion col-md-12" id="accordion" role="tablist">
                                                <div class="card border-bottom">
                                                    <div class="card-header" role="tab" id="headingThree">
                                                        <h6 class="mb-0">
                                                            <a class="collapsed" data-toggle="collapse" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                                <i class="card-icon mdi mdi-lock"></i>Deseja alterar sua senha?
                                                            </a>
                                                        </h6>
                                                    </div>
                                                    <div id="collapseThree" class="collapse" role="tabpanel" aria-labelledby="headingThree" data-parent="#accordion">
                                                        <div class="card-body row">
                                                            <div class="form-group col-md-4">
                                                                <label>Senha Atual</label>
                                                                <input type="password" class="form-control" name="password_current">
                                                            </div>
                                                            <div class="form-group col-md-4">
                                                                <label>Nova Senha</label>
                                                                <input type="password" class="form-control" name="password" id="password">
                                                            </div>
                                                            <div class="form-group col-md-4">
                                                                <label>Confirme Senha</label>
                                                                <input type="password" class="form-control" name="password_confirmation">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="user-config" role="tabpanel" aria-labelledby="user-config-tab">
                                        <div class="row pb-4">
                                            <div class="form-group col-md-4">
                                                <label>Tema</label>
                                                <select class="form-control" name="style_template">
                                                    <option value="1" {{ (old('style_template') ?? $user->style_template) == 1 ? 'selected' : '' }}>Claro (Light)</option>
                                                    <option value="3" {{ (old('style_template') ?? $user->style_template) == 3 ? 'selected' : '' }}>Escuro (Dark)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Salvar</button>
                            </div>
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
