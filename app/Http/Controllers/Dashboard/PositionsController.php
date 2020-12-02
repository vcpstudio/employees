<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{Position, Employee};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{View, Auth};
use Datatables, Validator;
use Carbon\Carbon;

class PositionsController extends Controller
{
    /**
     * Константы переводов
     */
    const TRANS_DASH_POS_ADD_OK = 'adminlte::adminlte.dashboard.positions.has_been_added';
    const TRANS_DASH_POS_UPDATE_OK = 'adminlte::adminlte.dashboard.positions.has_been_updated';
    const TRANS_DASH_POS_DEL_OK = 'adminlte::adminlte.dashboard.positions.has_been_deleted';
    const TRANS_DASH_POS_HAS_EMP = 'adminlte::adminlte.dashboard.positions.has_num_employees';
    const TRANS_DASH_POS_INVALID_NEW_POS_ID = 'adminlte::adminlte.dashboard.positions.invalid_new_position_id';

    /**
     * Вывод списка вакансий в виде таблицы
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Position::query();

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('updated_at', function ($position) {
                    return date('d.m.y', strtotime($position->updated_at));
                })
                ->addColumn('action', function ($position) {
                    return View::make('dashboard.positions.table-parts.action-buttons',
                        ['id' => $position->id]
                    );
                })
                ->setTotalRecords($data->count())
                ->make(true);
        }

        return View::make('dashboard.positions.index');
    }

    /**
     * Метод вывода формы добавления вакансий
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return View::make('dashboard.positions.form');
    }

    /**
     * Метод добавления вакансии в базу
     * @param Request $request
     * @param Position $position
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255'
        ]);

        $validated['admin_created_id'] = Auth::id();
        $validated['admin_updated_id'] = Auth::id();

        $position->create($validated);

        return redirect()
            ->route('positions.index')
            ->with('success', trans(self::TRANS_DASH_POS_ADD_OK));
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Метод вывода формы редактирования вакансии
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Position $position)
    {
        return View::make('dashboard.positions.form', ['data' => $position]);
    }

    /**
     * Метод обновления данных вакансии в базе
     * @param Request $request
     * @param Position $position
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255'
        ]);

        $validated['admin_updated_id'] = Auth::id();
        $validated['updated_at'] = Carbon::now();

        $position->update($validated);

        return redirect()
            ->route('positions.index')
            ->with('success', trans(self::TRANS_DASH_POS_UPDATE_OK));
    }

    /**
     * Метод удаления вакансии из базы
     * @param Request $request
     * @param Position $position
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, Position $position)
    {
        $newPositionId = (int)$request->get('new_position_id');
        $employeesNum = Employee::select(['id'])->where('position_id', '=', $newPositionId)->count();

        $rules = [
            'new_position_id' => ($employeesNum < 1 ? 'sometimes|nullable|' : 'required|') .
                'integer|exists:App\Models\Position,id|not_in:' . $position->id,
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => trans(self::TRANS_DASH_POS_INVALID_NEW_POS_ID)
            ]);
        }

        if ($employeesNum > 0) {
            Employee::where('position_id', '=', $position->id)
                ->update(['position_id' => $newPositionId]);
        }

        if ($position->delete()) {
            return response()->json([
                'result' => true,
                'message' => trans(self::TRANS_DASH_POS_DEL_OK)
            ]);
        }
    }

    /**
     * AJAX метод получения списка вакансий по поисковому запросу в формате JSON
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAjax(Request $request)
    {
        $items = [];
        if ($request->ajax()) {
            $q = $request->get('q');
            $id = $request->get('id');
            if (strlen($q) >= 2) {
                $items = Position::select([
                    'id',
                    'name AS text'
                ])->where([
                    ['name', 'like', '%' . $q . '%'],
                    ['id', '<>', $id]
                ])
                    ->orderBy('name', 'ASC')
                    ->limit(25)->get()->toArray();
            }
        }
        return response()->json([
            'items' => $items,
        ]);
    }

    /**
     * AJAX метод получения кол-ва сотрудников по Id должности
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function employeesNumByPositionId(Request $request)
    {
        $employeesNum = 0;
        $positionId = $request->get('position_id');
        if ($request->ajax() && $positionId) {
            $employeesNum = Employee::select(['id'])->where('position_id', '=', $positionId)->count();
        }
        return response()->json([
            'employees_num' => $employeesNum,
            'text' => trans(self::TRANS_DASH_POS_HAS_EMP, ['employees_num' => $employeesNum])
        ]);
    }

}
