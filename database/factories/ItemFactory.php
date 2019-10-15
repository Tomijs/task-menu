<?php
    
    /* @var $factory \Illuminate\Database\Eloquent\Factory */
    
    use Faker\Generator as Faker;
    
    use App\Repositories\Interfaces\MenuRepositoryInterface;
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Models\Menu;
    use App\Models\Item;

    const PERCENTAGE_HAVING_SUB_CHILDREN = 25;
    const MAX_SUB_CHILDREN_COUNT = 10;
    
    $menuRepository = app(MenuRepositoryInterface::class);
    $itemRepository = app(ItemRepositoryInterface::class);
    
    
    $factory->define(Item::class, function (Faker $faker) use ($menuRepository, $itemRepository) {
        do {
            if ($faker->boolean() && !is_null($menuRepository->first())) {
                $menu = $menuRepository->random();
            } else {
                $menu = factory(Menu::class)->create();
            }
        } while (!is_null($menu->max_children) && $menu->max_children <= $menuRepository->firstLayerChildrenCount($menu));
        
        do {
            $name = $faker->sentence($faker->numberBetween(1, 2));
        } while (strlen($name) > Item::MAX_NAME_LENGTH);
        
        return [
            'menu_id' => $menu->id,
            'name'    => $name
        ];
    });
    
    $factory->afterCreating(Item::class, function ($item, $faker) use ($itemRepository) {
        if ($faker->boolean(PERCENTAGE_HAVING_SUB_CHILDREN)) {
            $menu = $itemRepository->menu($item);
            
            $itemDepth = $itemRepository->depth($item);
            $itemChildrenDepth = ($itemDepth + 1);
            
            if ($itemChildrenDepth <= $menu->max_depth) {
                $layerChildrenCount = $itemRepository->layerChildrenCountByMenu($menu, $itemChildrenDepth);
                $maxLayerChildrenSpace = (is_null($menu->max_children) ? MAX_SUB_CHILDREN_COUNT : ($menu->max_children - $layerChildrenCount));
                
                if ($maxLayerChildrenSpace > 0) {
                    $newChildrenCount = ($maxLayerChildrenSpace < MAX_SUB_CHILDREN_COUNT ? $maxLayerChildrenSpace : MAX_SUB_CHILDREN_COUNT);
                    
                    for ($i = 0; $i < $faker->numberBetween(1, $newChildrenCount); $i++) {
                        $childItem = factory(Item::class)->create([
                            'menu_id' => $menu->id
                        ]);
                        
                        $itemRepository->makeSub($item, $childItem);
                    }
                }
            }
        }
    });
