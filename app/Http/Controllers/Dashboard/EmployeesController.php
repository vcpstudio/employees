<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{Position, Employee};
use Illuminate\Http\Request;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Support\Facades\{View, DB, Auth};
use DataTables, Validator, DateTime, Image, Exception;
use Carbon\Carbon;

class EmployeesController extends Controller
{
    /**
     * Константы переводов
     */
    const TRANS_DASH_EMP_NOT_FOUND = 'adminlte::adminlte.dashboard.employees.not_found';
    const TRANS_DASH_EMP_DEL_OK = 'adminlte::adminlte.dashboard.employees.has_been_deleted';
    const TRANS_DASH_EMP_DEL_NOT_SEL_NEW_HEAD = 'adminlte::adminlte.dashboard.employees.not_selected_new_head';
    const TRANS_DASH_EMP_INVALID_ID_NEW_HEAD = 'adminlte::adminlte.dashboard.employees.invalid_id_new_head';
    const TRANS_DASH_EMP_HAS_SUBORDINATES = 'adminlte::adminlte.dashboard.employees.has_subordinates';
    const TRANS_DASH_EMP_UPDATE_OK = 'adminlte::adminlte.dashboard.employees.has_been_updated';
    const TRANS_DASH_EMP_ADD_OK = 'adminlte::adminlte.dashboard.employees.has_been_added';
    const TRANS_DASH_EMP_UPLOAD_PHOTO_ERROR = 'adminlte::adminlte.dashboard.employees.upload_photo_error';
    const TRANS_DASH_EMP_PHOTO_SIZE_ERROR = 'adminlte::adminlte.dashboard.employees.photo_size_error';

    /**
     * Вывод списка сотрудников в виде таблицы
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('employees')
                ->join('positions', 'employees.position_id', '=', 'positions.id')
                ->select([
                    'employees.id AS id',
                    'employees.photo AS photo',
                    'employees.fullname AS fullname',
                    'positions.name AS position_name',
                    'employees.employment_at AS employment_at',
                    'employees.phone AS phone',
                    'employees.email AS email',
                    'employees.salary AS salary',
                ]);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($employee) {
                    return View::make('dashboard.employees.table-parts.action-buttons',
                        ['id' => $employee->id]
                    );
                })
                ->editColumn('photo', function ($employee) {
                    if ($employee->photo) {
                        return View::make('dashboard.employees.table-parts.image-avatar',
                            ['photo' => $employee->photo]
                        );
                    } else {
                        $initials = function ($str, $length = 2) {
                            $ret = '';
                            foreach (explode(' ', $str) as $key => $word) {
                                $ret .= mb_substr($word, 0, 1, "UTF-8");
                                if (($key + 1) >= $length) {
                                    break;
                                }
                            }
                            return $ret;
                        };
                        return View::make('dashboard.employees.table-parts.text-avatar',
                            ['initials' => $initials($employee->fullname)]
                        );
                    }
                })
                ->editColumn('phone', function ($employee) {
                    return isset($employee->phone) ? '<a href="tel:' . $employee->phone . '">' . ($employee->phone) . '</a>' : '-';
                })
                ->editColumn('email', function ($employee) {
                    return isset($employee->email) ? '<a href="mailto:' . $employee->email . '">' . ($employee->email) .
                        '</a>' : '-';
                })
                ->editColumn('employment_at', function ($employee) {
                    return date('d.m.y', strtotime($employee->employment_at));
                })
                ->rawColumns(['photo', 'phone', 'email', 'action'])
                ->setTotalRecords($data->count())
                ->make(true);
        }

        return View::make('dashboard.employees.index');
    }

    /**
     * Метод вывода формы добавления сотрудников
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return View::make('dashboard.employees.form', [
            'positions' => Position::all(['id', 'name'])->toArray(),
            'employees' => Employee::select(['id', 'fullname'])->limit(10)->get(),
        ]);
    }

    /**
     * Метод добавления сотрудника в базу
     * @param EmployeeRequest $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();
        unset($validated['photo']);

        $validated['admin_created_id'] = Auth::id();
        $validated['admin_updated_id'] = Auth::id();

        $newEmployee = $employee->create($validated);

        if ($this->storePhoto($newEmployee) === false) {
            return redirect()
                ->route('employees.edit', $newEmployee->id)
                ->withErrors(trans(self::TRANS_DASH_EMP_UPLOAD_PHOTO_ERROR));
        }

        return redirect()
            ->route('employees.index')
            ->with('success', trans(self::TRANS_DASH_EMP_ADD_OK));
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
     * Метод вывода формы редактирования сотрудников
     * @param $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if (!($data = Employee::with(['position', 'head'])->find($id))) {
            return redirect()
                ->route('employees.index')
                ->withErrors(trans(self::TRANS_DASH_EMP_NOT_FOUND));
        }

        $data->employment_at = date('d.m.y', strtotime($data->employment_at));

        return View::make('dashboard.employees.form', [
            'data' => $data,
            'positions' => Position::all(['id', 'name'])->toArray(),
            'employees' => Employee::select(['id', 'fullname'])->limit(10)->get(),
        ]);
    }

    /**
     * Метод обновления данных сотрудника в базе
     * @param EmployeeRequest $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();
        unset($validated['photo']);

        if ($this->storePhoto($employee) === false) {
            return redirect()
                ->back()
                ->withErrors(trans(self::TRANS_DASH_EMP_UPLOAD_PHOTO_ERROR));
        }

        $employmentAtDate = DateTime::createFromFormat('d.m.y', $validated['employment_at']);
        $validated['employment_at'] = $employmentAtDate->format('Y-m-d');

        $validated['admin_updated_id'] = Auth::id();
        $validated['updated_at'] = Carbon::now();

        $employee->update($validated);
        $employee->touch();

        return redirect()
            ->route('employees.index')
            ->with('success', trans(self::TRANS_DASH_EMP_UPDATE_OK));
    }

    /**
     * Метод удаления сотрудника из базы
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, Employee $employee)
    {
        $newHeadEmployeeId = (int) $request->get('new_head_employee_id');
        $subordinatesNum = Employee::select(['id'])->where('head_employee_id', '=', $employee->id)->count();

        $rules = [
            'new_head_employee_id' => ($subordinatesNum < 1 ? 'sometimes|nullable|' : 'required|') .
                'integer|exists:App\Models\Employee,id|not_in:' . $employee->id,
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => trans(self::TRANS_DASH_POS_INVALID_NEW_POS_ID)
            ]);
        }

        if ($subordinatesNum > 0) {
            Employee::where('head_employee_id', '=', $employee->id)
                ->update(['head_employee_id' => $newHeadEmployeeId]);
        }

        if ($employee->delete()) {
            $photoFullPath = public_path() . '/assets/images/avatars/' . $employee->photo;
            if (file_exists($photoFullPath)) {
                @unlink($photoFullPath);
            }
            return response()->json([
                'result' => true,
                'message' => trans(self::TRANS_DASH_EMP_DEL_OK)
            ]);
        }
    }

    /**
     * AJAX метод получения списка сотрудников по поисковому запросу в формате JSON
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
                $items = Employee::select([
                    'id',
                    'fullname AS text'
                ])->where([
                    ['fullname', 'like', '%' . $q . '%'],
                    ['id', '<>', $id]
                ])
                    ->orderBy('fullname', 'ASC')
                    ->limit(25)->get()->toArray();
            }
        }
        return response()->json([
            'items' => $items,
        ]);
    }

    /**
     * AJAX метод получения кол-ва подчиненных у сотрудника по Id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNumSubordinatesByHeadId(Request $request)
    {
        $subordinatesNum = 0;
        $headEmployeeId = $request->get('head_employee_id');
        if ($request->ajax() && $headEmployeeId) {
            $subordinatesNum = Employee::select(['id'])->where('head_employee_id', '=', $headEmployeeId)->count();
        }
        return response()->json([
            'subordinates_num' => $subordinatesNum,
            'text' => trans(self::TRANS_DASH_EMP_HAS_SUBORDINATES, ['subordinates' => $subordinatesNum])
        ]);
    }

    /**
     * AJAX метод для поворота фотографии на 90 градусов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rotatePhoto(Request $request) {
        $rotated = false;
        if ($request->ajax()) {
            $id = $request->get('id');
            $employeePhoto = Employee::select(['photo'])->where('id', '=', $id)->first();
            if (isset($employeePhoto->photo)) {
                $photoFullPath = public_path() . '/assets/images/avatars/' . $employeePhoto->photo;
                if (file_exists($photoFullPath)) {
                    try {
                        Image::make($photoFullPath)->rotate(90)->save($photoFullPath);
                        $rotated = true;
                    } catch (Exception $e) {
                        $rotated = false;
                    }
                }
            }
        }
        return response()->json([
            'rotated' => $rotated,
        ]);
    }

    /**
     * Метод сохранения и обработки фотографии сотрудника
     * @param Employee $employee
     * @return bool
     */
    private function storePhoto(Employee $employee)
    {
        $uploaded = null;
        if (request()->hasFile('photo') && request('photo') !== '') {
            $photo = request()->file('photo');
            $photoPath = public_path() . '/assets/images/avatars/';
            $photoName = uniqid($employee->id) . '.' . $photo->getClientOriginalExtension();
            try {
                Image::make($photo)->fit(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($photoPath . '/' . $photoName);
                $uploaded = true;
            } catch (Exception $e) {
                $uploaded = false;
            }
            if ($uploaded === true) {
                if (file_exists($photoPath . $employee->photo)) {
                    @unlink($photoPath . $employee->photo);
                }
                $employee->update(['photo' => $photoName]);
            }
        }
        return $uploaded;
    }
}
