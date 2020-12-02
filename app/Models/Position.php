<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    /**
     * Имя таблицы вакансий
     * @var string
     */
    protected $table = 'positions';

    /**
     * Имя первичного ключа в таблице вакансий
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Заполняемые поля в таблице вакансий
     * @var array
     */
    protected $fillable = [
        'name',
        // Поля которые не редактируются админом, но заполняются
        'admin_created_id',
        'admin_updated_id'
    ];

}