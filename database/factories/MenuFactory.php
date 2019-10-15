<?php
    
    /* @var $factory \Illuminate\Database\Eloquent\Factory */
    
    use Faker\Generator as Faker;
    
    use App\Models\Menu;

    $factory->define(Menu::class, function (Faker $faker) {
        do {
            $name = $faker->sentence($faker->numberBetween(1, 2));
        } while (strlen($name) > Menu::MAX_NAME_LENGTH);
        
        return [
            'name'         => $name,
            'max_depth'    => ($faker->boolean(10) ? null : $faker->numberBetween(1, 10)),
            'max_children' => ($faker->boolean(10) ? null : $faker->numberBetween(1, 25))
        ];
    });
