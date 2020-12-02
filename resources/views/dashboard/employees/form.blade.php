@extends('adminlte::page')

@if (isset($data->id))
    @section('title', __('adminlte::adminlte.dashboard.employees.edit') . ' | ' . $data->fullname)
@else
    @section('title', __('adminlte::adminlte.dashboard.employees.create'))
@endif

@section('content_header')
    @if (isset($data->id))
        <h1 class="m-0 text-dark">{{ __('adminlte::adminlte.dashboard.employees.edit') }}</h1>
    @else
        <h1 class="m-0 text-dark">{{ __('adminlte::adminlte.dashboard.employees.create') }}</h1>
    @endif
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                {{
                    Form::open([
                        'url' => isset($data->id) ? route('employees.update', $data->id) : route('employees.store'),
                        'files' => true,
                        'id' => 'employees-form',
                        'method' => isset($data->id) ? 'PUT' : 'POST'
                    ])
                }}
                <div class="card-body">

                    <!-- Photo -->
                    <div class="form-group">
                        @if (isset($data->photo) && !empty($data->photo))
                            <div class="text-left mb-3">
                                {{
                                    Html::image(url("/assets/images/avatars/{$data->photo}"), null,
                                        ['class' => 'img-circle elevation-2', 'style' => 'width: 100px',
                                        'data-angle' => 0]
                                    )
                                }}
                                <div class="mt-2">
                                    <a href="javascript:void(0)" data-id="{{ $data->id }}" class="rotate-photo">
                                        <i class="fas fa-fw fa-undo"></i>
                                        {{ __('adminlte::adminlte.dashboard.employees.rotate_photo') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        {{ Form::label('choosePhoto', __('adminlte::adminlte.dashboard.employees.choose_photo')) }}
                        <div class="input-group">
                            <div class="custom-file">
                                {{ Form::file('photo', ['class' => 'custom-file-input', 'id' => 'photo']) }}
                                {{ Form::label('photo', __('adminlte::adminlte.dashboard.employees.choose_photo'), ['class' => 'custom-file-label']) }}
                            </div>
                        </div>
                        <small class="float-right mt-2 text-muted">
                            {{ __('adminlte::adminlte.dashboard.employees.upload_rules')}}
                        </small>
                    </div>

                    <!-- Full name -->
                    <div class="form-group">
                        {{ Form::label('fullname', __('adminlte::adminlte.dashboard.employees.fullname')) }}
                        {{ Form::text('fullname', $data->fullname ?? Request::old('fullname'), ['class' =>
                        'form-control', 'id' => 'fullname']) }}
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        {{ Form::label('phone', __('adminlte::adminlte.dashboard.employees.phone')) }}
                        <input type="text" name="phone" class="form-control" id="phone"
                               value="{!! $data->phone ?? Request::old('phone') !!}"
                               placeholder="+38 (067) 000 00 00"
                               data-inputmask='"mask": "+38 (999) 999 99 99"' data-mask
                        >
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        {{ Form::label('email', __('adminlte::adminlte.dashboard.employees.email')) }}
                        {{ Form::email('email', $data->email ?? Request::old('email'), ['class' => 'form-control', 'id'
                         => 'email']) }}
                    </div>

                    <!-- Position -->
                    @if (isset($positions) && !empty($positions))
                        <div class="form-group">
                            <label>{{ __('adminlte::adminlte.dashboard.employees.position') }}</label>
                            <select class="form-control" name="position_id" id="position_id">
                                <option></option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position['id'] }}"
                                            {{
                                                ( (isset($data->position_id) && $position['id'] ===
                                                $data->position_id) || Request::old('position_id') == $position['id'] ) ?
                                                'selected' : ''
                                             }}>
                                        {{ $position['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                <!-- Salary -->
                    <div class="form-group">
                        {{ Form::label('salary', __('adminlte::adminlte.dashboard.employees.salary')) }}
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            {{ Form::number('salary', $data->salary ?? Request::old('salary'), [
                                'class' => 'form-control',
                                'id' => 'salary',
                                'step' => '0.005',
                                'min' => 0,
                                'max' => 500
                                ]) }}
                        </div>
                    </div>

                    <!-- Head -->
                    @if (isset($employees) && !empty($employees))
                        <div class="form-group">
                            {{ Form::label('head_employee_id', __('adminlte::adminlte.dashboard.employees.head')) }}
                            <select class="form-control select2" name="head_employee_id" id="head_employee_id">
                                @if (isset($data->head))
                                    <option value="{{ intval($data->head->id) }}">
                                        {!! $data->head->fullname !!}
                                    </option>
                                @else
                                    <option></option>
                                @endif
                                @foreach ($employees as $employee)
                                    <option value="{{ intval($employee['id']) }}">
                                        {!! $employee['fullname'] !!}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                <!-- Employment at -->
                    <div class="form-group">
                        {{ Form::label('employment_at', __('adminlte::adminlte.dashboard.employees.employment_at')) }}
                        <div class="input-group">
                            <div class="input-group-prepend">
                                      <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                      </span>
                            </div>
                            {{ Form::text('employment_at', $data->employment_at ?? Request::old('employment_at'),
                                ['class' => 'form-control float-right', 'id' => 'employment_at',
                                'data-daterangepicker']) }}
                        </div>
                    </div>

                    @if (isset($data->id))
                        <div class="row">
                            <div class="form-group col-6">
                                {{ Form::label('created_at', __('adminlte::adminlte.dashboard.employees.created_at')) }}
                                {{ Form::text('created_at', $data->created_at, ['class' => 'form-control', 'disabled']) }}
                            </div>
                            <div class="form-group col-6">
                                {{ Form::label('updated_at', __('adminlte::adminlte.dashboard.employees.updated_at')) }}
                                {{ Form::text('updated_at', $data->updated_at, ['class' => 'form-control', 'disabled']) }}
                            </div>
                            <div class="form-group col-6">
                                {{ Form::label('admin_created_id', __('adminlte::adminlte.dashboard.employees.admin_created_id')) }}
                                {{ Form::text('admin_created_id', $data->admin_created_id, ['class' => 'form-control', 'disabled']) }}
                            </div>
                            <div class="form-group col-6">
                                {{ Form::label('admin_updated_id', __('adminlte::adminlte.dashboard.employees.admin_updated_id')) }}
                                {{ Form::text('admin_updated_id', $data->admin_updated_id, ['class' => 'form-control', 'disabled']) }}
                            </div>
                        </div>
                    @endif

                </div>

                <div class="card-footer">
                    {{ Form::submit(__('adminlte::adminlte.dashboard.employees.save'), ['class' => 'btn btn-primary float-right ml-3']) }}
                    {{ Html::link( url('dashboard/employees'), __('adminlte::adminlte.dashboard.employees.cancel'),
                    ['class' => 'btn btn-danger float-right']) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@stop

@section('adminlte_js')
    <script>
        $(function () {

            /* Вывод ошибок */
            @if($errors->any())
            @foreach ($errors->all() as $error)
            toastr.error('{!! $error !!}');
            @endforeach
            @endif

            /* Изминение вида поля выбора фото */
            bsCustomFileInput.init();

            /* Выпадающиий список с поиском для выбора начальника */
            $('.select2').select2({
                ajax: {
                    url: '<?php echo url('/dashboard/employees/list-ajax'); ?>',
                    data: function (params) {
                        return {
                            q: params.term,
                            id: '{{ $data->id ?? '' }}'
                        };
                    },
                    dataType: 'json',
                    processResults: function (data) {
                        return {
                            results: data.items
                        };
                    }
                }
            });

            /* Маска для ввода номера телефона */
            $('[data-mask]').inputmask();

            /* Календарь */
            $('[data-daterangepicker]').daterangepicker({
                locale: {
                    format: 'DD.MM.YY',
                },
                singleDatePicker: true,
                showDropdowns: true
            });

            /* Валидация формы */
            $('#employees-form').validate({
                rules: {
                    fullname: {
                        required: true,
                        minlength: 2,
                        maxlength: 255
                    },
                    phone: {
                        required: true,
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    position_id: {
                        required: true
                    },
                    salary: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 500
                    },
                    head_employee_id: {
                        required: true,
                    },
                    employment_at: {
                        required: true,
                    }
                },
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });

            /* Поворот фотографии */
            $('.rotate-photo').on('click', function (e) {
                var $self = $(this);
                var $photo = $self.parent().parent().find('img');
                $.ajax({
                    url: '{{ route('employees.rotate-photo') }}',
                    data: {_token: '{{ csrf_token() }}', id: $self.data('id')},
                    dataType: 'json',
                    type: 'POST',
                    success: function (response) {
                        if (response.rotated === true) {
                            let angle = +$photo.data('angle') - 90;
                            $photo.css({'transform': `rotate(${angle}deg)`}).data('angle', angle);
                        }
                    }
                });
            });

        });
    </script>
@stop