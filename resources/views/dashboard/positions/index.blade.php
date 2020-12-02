@extends('adminlte::page')

@section('title', __('adminlte::adminlte.dashboard.positions.positions_list'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('adminlte::adminlte.dashboard.positions.positions_list') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <table id="positionsTable" class="table table-bordered table-hover" width="100%">
                <thead>
                <tr>
                    <th class="text-center">{{ __('adminlte::adminlte.dashboard.positions.id') }}</th>
                    <th>{{ __('adminlte::adminlte.dashboard.positions.name') }}</th>
                    <th class="text-center">{{ __('adminlte::adminlte.dashboard.positions.last_update') }}</th>
                    <th class="text-center">{{ __('adminlte::adminlte.dashboard.positions.action') }}</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@stop

@section('adminlte_js')
    <script>
        var positionsTable;
        $(function () {

            @if ($errors->any())
            @foreach ($errors->all() as $error)
            toastr.error('{!! $error !!}');
            @endforeach
            @endif

            @if ($message = Session::get('success'))
            toastr.success('{!! $message !!}');
            @endif

                positionsTable = $('#positionsTable').DataTable({
                order: [[1, 'asc']],
                resetDisplay: false,
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{ route('positions.index') }}",
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        class: 'text-center',
                        width: '40px',
                    },
                    {data: 'name', name: 'name'},
                    {data: 'updated_at', name: 'updated_at', width: '100px', class: 'text-center'},
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

        function deletePosition(id) {
            Swal.fire({
                title: "{{ __('adminlte::adminlte.dashboard.positions.confirm_delete_title') }}",
                text: "{{ __('adminlte::adminlte.dashboard.positions.confirm_delete_text') }}",
                input: 'select',
                inputOptions: [],
                confirmButtonText: "{{ __('adminlte::adminlte.dashboard.positions.delete') }}",
                cancelButtonText: "{{ __('adminlte::adminlte.dashboard.positions.cancel') }}",
                showCancelButton: true,
                showCloseButton: true,
                onBeforeOpen() {
                    var $swal2ConfirmBtn = $('.swal2-confirm');
                    var $swal2TextElem = $('#swal2-content');
                    var $swal2SelectElem = $('.swal2-select');
                    $.ajax({
                        url: '<?php echo route('positions.employees-num'); ?>',
                        data: {position_id: id},
                        dataType: 'json',
                        success: function (response) {
                            if (response.employees_num > 0) {
                                $swal2ConfirmBtn.hide();
                                $swal2TextElem.append(' ' + response.text);
                                $swal2SelectElem.select2({
                                    placeholder: '{{ __('adminlte::adminlte.dashboard.positions.find_new_position') }}',
                                    ajax: {
                                        url: '<?php echo route('positions.list-ajax'); ?>',
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
                        url: '{{ url('/dashboard/positions') }}/' + id,
                        type: 'DELETE',
                        data: {_token: '{{ csrf_token() }}', new_position_id: result.value},
                        success: function (response) {
                            if (response.result === true) {
                                toastr.success(response.message);
                                positionsTable.ajax.reload(null, false);
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            })
        }
    </script>
@stop