@extends('adminlte::page')

@section('title', __('adminlte::adminlte.dashboard.employees.employees_list'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('adminlte::adminlte.dashboard.employees.employees_list') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <table id="employeesTable" class="table table-bordered table-hover" width="100%">
                <thead>
                <tr>
                    <th class="text-center">{{ __('adminlte::adminlte.dashboard.employees.photo') }}</th>
                    <th class="text-center">{{ __('adminlte::adminlte.dashboard.employees.id') }}</th>
                    <th>{{ __('adminlte::adminlte.dashboard.employees.fullname') }}</th>
                    <th>{{ __('adminlte::adminlte.dashboard.employees.position') }}</th>
                    <th>{{ __('adminlte::adminlte.dashboard.employees.phone') }}</th>
                    <th>{{ __('adminlte::adminlte.dashboard.employees.email') }}</th>
                    <th class="text-center">{{ __('adminlte::adminlte.dashboard.employees.employment_at') }}</th>
                    <th class="text-center">{{ __('adminlte::adminlte.dashboard.employees.salary') }}</th>
                    <th class="text-center">{{ __('adminlte::adminlte.dashboard.employees.action') }}</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@stop

@section('adminlte_js')
    <script>
        var employeesTable;
        $(function () {

            @if ($errors->any())
            @foreach ($errors->all() as $error)
            toastr.error('{!! $error !!}');
            @endforeach
            @endif

            @if ($message = Session::get('success'))
            toastr.success('{!! $message !!}');
            @endif

                employeesTable = $('#employeesTable').DataTable({
                order: [[1, 'desc']],
                resetDisplay: false,
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{ route('employees.index') }}",
                columns: [
                    {
                        data: 'photo',
                        name: 'photo',
                        orderable: false,
                        searchable: false,
                        class: 'text-center',
                        width: '40px'
                    },
                    {
                        data: 'id',
                        name: 'id',
                        class: 'text-center',
                        width: '40px',
                    },
                    {data: 'fullname', name: 'fullname'},
                    {data: 'position_name', name: 'positions.name'},
                    {data: 'phone', name: 'phone', 'width': '120px'},
                    {data: 'email', name: 'email', 'width': '200px'},
                    {
                        data: 'employment_at',
                        name: 'employment_at',
                        class: 'text-center',
                        width: '150px'
                    },
                    {data: 'salary', name: 'salary', class: 'text-center', 'width': '70px'},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-center',
                        width: '80px'
                    },
                ]
            });
        });

        function deleteEmployee(id) {
            Swal.fire({
                title: "{{ __('adminlte::adminlte.dashboard.employees.confirm_delete_title') }}",
                text: "{{ __('adminlte::adminlte.dashboard.employees.confirm_delete_text') }}",
                input: 'select',
                inputOptions: [],
                confirmButtonText: "{{ __('adminlte::adminlte.dashboard.employees.delete') }}",
                cancelButtonText: "{{ __('adminlte::adminlte.dashboard.employees.cancel') }}",
                showCancelButton: true,
                showCloseButton: true,
                onBeforeOpen() {
                    var $swal2ConfirmBtn = $('.swal2-confirm');
                    var $swal2TextElem = $('#swal2-content');
                    var $swal2SelectElem = $('.swal2-select');
                    $.ajax({
                        url: '<?php echo route('employees.subordinates-num'); ?>',
                        data: {head_employee_id: id},
                        dataType: 'json',
                        success: function (response) {
                            if (response.subordinates_num > 0) {
                                $swal2ConfirmBtn.hide();
                                $swal2TextElem.append(' ' + response.text);
                                $swal2SelectElem.select2({
                                    placeholder: '{{ __('adminlte::adminlte.dashboard.employees.find_new_head') }}',
                                    ajax: {
                                        url: '<?php echo route('employees.list-ajax'); ?>',
                                        data: function (params) {
                                            return {
                                                q: params.term,
                                                id: id
                                            };
                                        },
                                        dataType: 'json',
                                        processResults: function (data) {
                                            return {
                                                results: data.items
                                            };
                                        }
                                    }
                                }).on('select2:select', function (e) {
                                    var data = e.params.data;
                                    if (data.selected === true) {
                                        $swal2ConfirmBtn.show();
                                    }
                                });
                            } else {
                                $swal2SelectElem.hide();
                            }
                        }
                    });
                }
            }).then(function (result) {
                if (result.value !== undefined) {
                    $.ajax({
                        url: '{{ url('/dashboard/employees') }}/' + id,
                        type: 'DELETE',
                        data: {_token: '{{ csrf_token() }}', new_head_employee_id: result.value},
                        success: function (response) {
                            if (response.result === true) {
                                toastr.success(response.message);
                                employeesTable.ajax.reload(null, false);
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            });
        }
    </script>
@stop