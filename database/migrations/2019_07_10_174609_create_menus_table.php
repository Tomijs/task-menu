<?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class CreateMenusTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('menus', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 30)->nullable(false);
                $table->integer('max_depth')->nullable(true)->default(null)->unsigned();
                $table->integer('max_children')->nullable(true)->default(null)->unsigned();
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('menus');
        }
    }
