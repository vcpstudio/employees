<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->unsignedInteger('position_id');
            $table->date('employment_at');
            $table->string('phone', 50);
            $table->string('email');
            $table->unsignedInteger('head_employee_id')->nullable();
            $table->decimal('salary', 8, 3);
            $table->string('photo', 255)->nullable();
            $table->unsignedInteger('admin_created_id');
            $table->unsignedInteger('admin_updated_id')->nullable();
            $table->timestamps();

            $table->foreign('position_id', 'fk_position_id')
                ->references('id')
                ->on('positions')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index(['head_employee_id', 'id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
