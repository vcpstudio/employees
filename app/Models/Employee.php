<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    /**
     * Имя таблицы сотрудников
     * @var string
     */
    protected $table = 'employees';

    /**
     * Имя первичного ключа в таблице сотрудников
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Заполняемые поля в таблице сотрудников
     * @var array
     */
    protected $fillable = [
        'fullname',
        'position_id',
        'employment_at',
        'phone',
        'email',
        'head_employee_id',
        'salary',
        'photo',
        // Поля которые не редактируются админом, но заполняются
        'admin_created_id',
        'admin_updated_id'
    ];

    public function position()
    {
        return $this->belongsTo('App\Models\Position', 'position_id', 'id');
    }

    public function head()
    {
        return $this->belongsTo('App\Models\Employee', 'head_employee_id', 'id');
    }
}