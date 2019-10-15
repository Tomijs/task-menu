<?php
    
    namespace App\Repositories;

    use App\Repositories\Interfaces\MenuRepositoryInterface;
    
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Models\Menu;
    use App\Models\Item;

    class MenuRepository implements MenuRepositoryInterface
    {
        protected $itemRepository;
        
        /**
         * Create a new repository instance.
         *
         * @param \App\Repositories\Interfaces\ItemRepositoryInterface $itemRepository
         */
        public function __construct(ItemRepositoryInterface $itemRepository)
        {
            $this->itemRepository = $itemRepository;
        }
        
        /**
         * Gets Menu by ID
         *
         * @param int $id
         *
         * @return collection
         */
        public function get(int $id)
        {
            return Menu::find($id);
        }
        
        /**
         * Gets first Menu
         *
         * @return null|collection
         */
        public function first()
        {
            return Menu::first();
        }
        
        /**
         * Gets a random Menu
         *
         * @return collection
         */
        public function random()
        {
            return Menu::all()
                       ->random();
        }
        
        /**
         * Gets Item depth of Menu
         *
         * @param \App\Models\Menu $menu
         *
         * @return item
         */
        public function depth(Menu $menu)
        {
            $menuDepth = Item::scoped(['menu_id' => $menu->id])
                             ->whereIsLeaf()
                             ->withDepth()
                             ->orderBy('depth', 'DESC')
                             ->first();
            
            return is_null($menuDepth) ? 0 : ($menuDepth->depth + 1);
        }
        
        /**
         * Gets Item children count in first layer of Menu
         *
         * @param \App\Models\Menu $menu
         *
         * @return int
         */
        public function firstLayerChildrenCount(Menu $menu)
        {
            return Item::scoped(['menu_id' => $menu->id])
                       ->whereIsRoot()
                       ->withDepth()
                       ->count();
        }
        
        /**
         * Gets number of the most Item children in any layer of Menu
         *
         * @param \App\Models\Menu $menu
         *
         * @return int
         */
        public function mostChildrenCountInAnyLayer(Menu $menu)
        {
            $items = $this->itemRepository->getByMenuWithDepth($menu);
            
            if ($items->isEmpty()) {
                return 0;
            }
            
            $menuLayerChildrenCount = [];
            
            foreach ($items as $item) {
                $depth = ($item->depth + 1);
                
                if (!array_key_exists(strval($depth), $menuLayerChildrenCount)) {
                    $menuLayerChildrenCount[strval($depth)] = 0;
                }
                
                $menuLayerChildrenCount[strval($depth)]++;
            }
            
            return max($menuLayerChildrenCount);
        }
        
        /**
         * Creates new Menu
         *
         * @param array $data
         *
         * @return collection|boolean
         */
        public function create(array $data)
        {
            $menu = new Menu();
            
            $response = $menu->fill($data)
                             ->save();
            
            if ($response) {
                return $menu;
            } else {
                return $response;
            }
        }
        
        /**
         * Updates a Menu
         *
         * @param \App\Models\Menu $menu
         * @param array            $data
         *
         * @return boolean
         */
        public function update(Menu $menu, array $data)
        {
            return $menu->fill($data)
                        ->save();
        }
        
        /**
         * Deletes Menu by ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return boolean
         * @throws \Exception
         */
        public function delete(Menu $menu)
        {
            return $menu->delete();
        }
    }
