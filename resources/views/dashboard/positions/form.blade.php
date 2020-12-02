@extends('adminlte::page')

@if (isset($data->id))
    @section('title', __('adminlte::adminlte.dashboard.positions.edit') . ' | ' . $data->name)
@else
    @section('title', __('adminlte::adminlte.dashboard.positions.create'))
@endif

@section('content_header')
    @if (isset($data->id))
        <h1 class="m-0 text-dark">{{ __('adminlte::adminlte.dashboard.positions.edit') }}</h1>
    @else
        <h1 class="m-0 text-dark">{{ __('adminlte::adminlte.dashboard.positions.create') }}</h1>
    @endif
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                {{
                    Form::open([
                        'url' => isset($data->id) ? route('positions.update', $data->id) : route('positions.store'),
                        'files' => true,
                        'id' => 'positions-form',
                        'method' => isset($data->id) ? 'PUT' : 'POST'
                    ])
                }}
                <div class="card-body">
                    <div class="row">
                        <!-- Name -->
                        <div class="form-group col-12">
                            {{ Form::label('name', __('adminlte::adminlte.dashboard.positions.name')) }}
                            {{ Form::text('name', $data->name ?? Request::old('name'), ['class' =>
                            'form-control', 'id' => 'name', 'maxlength' => 255]) }}
                        </div>
                    </div>
                    @if (isset($data->id))
                        <div class="row">
                            <div class="form-group col-6">
                                {{ Form::label('created_at', __('adminlte::adminlte.dashboard.positions.created_at')) }}
                                {{ Form::text('created_at', $data->created_at, ['class' => 'form-control', 'disabled']) }}
                            </div>
                            <div class="form-group col-6">
                                {{ Form::label('updated_at', __('adminlte::adminlte.dashboard.positions.updated_at')) }}
                                {{ Form::text('updated_at', $data->updated_at, ['class' => 'form-control', 'disabled']) }}
                            </div>
                            <div class="form-group col-6">
                                {{ Form::label('admin_created_id', __('adminlte::adminlte.dashboard.positions.admin_created_id')) }}
                                {{ Form::text('admin_created_id', $data->admin_created_id, ['class' => 'form-control', 'disabled']) }}
                            </div>
                            <div class="form-group col-6">
                                {{ Form::label('admin_updated_id', __('adminlte::adminlte.dashboard.positions.admin_updated_id')) }}
                                {{ Form::text('admin_updated_id', $data->admin_updated_id, ['class' => 'form-control', 'disabled']) }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    {{ Form::submit(__('adminlte::adminlte.dashboard.positions.save'), ['class' => 'btn btn-primary float-right ml-3']) }}
                    {{ Html::link( route('positions.index'), __('adminlte::adminlte.dashboard.positions.cancel'),
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

            @if($errors->any())
            @foreach ($errors->all() as $error)
            toastr.error('{!! $error !!}');
            @endforeach
            @endif

            inputValueState('name', 'float-right mt-2 text-muted');

            $('#positions-form').validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 2,
                        maxlength: 255
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

        });

        /**
         * @param string elementId
         * @param string classes
         */
        function inputValueState(elementId, classes) {
            var $input = $('#' + elementId);
            if ($input.length < 1) {
                return;
            }
            var $stateContainer = $('<small />', {class: classes});
            var $lengthContainer = $('<span />', {class: 'length', text: 0});
            var $maxLengthContainer = $('<span />', {class: 'maxLength', text: 0});
            var $stateContainerContent = $stateContainer
                .append($lengthContainer)
                .append(' / ')
                .append($maxLengthContainer);
            $input.after($stateContainerContent);
            $input.on('focus blur keyup keydown contextmenu paste input', function (e) {
                var $self = $(this);
                var $length = $self.val().length;
                var $maxLength = $self.attr('maxlength') ?? 255;
                var allowedLength = Math.ceil($maxLength - $length);
                if (allowedLength <= 1) {
                    $self.val(function () {
                        return $self.val().substr(0, $maxLength);
                    });
                }
                $lengthContainer.text($length);
                $maxLengthContainer.text(allowedLength);
            }).keyup();
        }

    </script>
@stop