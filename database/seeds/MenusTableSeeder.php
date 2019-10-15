<?php
    
    use Illuminate\Database\Seeder;
    
    use App\Models\Menu;

    class MenusTableSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            factory(Menu::class, 50)->create();
        }
    }
