<?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class CreateItemsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('menu_id')->nullable(false)->unsigned();
                $table->nestedSet();
                $table->string('name', 30)->nullable(false);
                $table->timestamps();
        
                $table->foreign('menu_id')->references('id')->on('menus')->onUpdate('restrict')->onDelete('restrict');
            });
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('items');
        }
    }
