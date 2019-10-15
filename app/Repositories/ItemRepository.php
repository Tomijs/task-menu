<?php
    
    namespace App\Repositories;

    use Illuminate\Database\Eloquent\Collection;
    
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Models\Menu;
    use App\Models\Item;

    class ItemRepository implements ItemRepositoryInterface
    {
        /**
         * Gets Item by ID
         *
         * @param int $id
         *
         * @return collection
         */
        public function get(int $id)
        {
            return Item::find($id);
        }
        
        /**
         * Gets Item by ID with depth
         *
         * @param \App\Models\Menu $menu
         * @param int              $id
         *
         * @return collection
         */
        public function getWithDepth(Menu $menu, int $id)
        {
            return Item::scoped(['menu_id' => $menu->id])
                       ->withDepth()
                       ->find($id);
        }
        
        /**
         * Gets first Item
         *
         * @return null|collection
         */
        public function first()
        {
            return Item::first();
        }
        
        /**
         * Gets deepest Item from provided Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function deepestFromItem(Item $item)
        {
            return Item::scoped(['menu_id' => $this->menu($item)->id])
                       ->whereDescendantOrSelf($item)
                       ->withDepth()
                       ->orderBy('depth', 'DESC')
                       ->first();
        }
        
        /**
         * Gets Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return collection
         */
        public function getByMenu(Menu $menu)
        {
            return Item::scoped(['menu_id' => $menu->id])
                       ->get();
        }
        
        /**
         * Gets Items by Menu ID with depth column
         *
         * @param \App\Models\Menu $menu
         *
         * @return collection
         */
        public function getByMenuWithDepth(Menu $menu)
        {
            return Item::scoped(['menu_id' => $menu->id])
                       ->withDepth()
                       ->get();
        }
        
        /**
         * Gets root Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return collection
         */
        public function rootByMenu(Menu $menu)
        {
            return Item::scoped(['menu_id' => $menu->id])
                       ->whereIsRoot()
                       ->get();
        }
        
        /**
         * Gets specific layer Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @param                  $layerNo
         *
         * @return collection
         */
        public function layerByMenu(Menu $menu, $layerNo)
        {
            return Item::scoped(['menu_id' => $menu->id])
                       ->withDepth()
                       ->get()
                       ->where('depth', ($layerNo - 1));
        }
        
        /**
         * Gets a random Item
         *
         * @return collection
         */
        public function random()
        {
            return Item::all()
                       ->random();
        }
        
        /**
         * Gets a random Item by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return collection
         */
        public function randomByMenu(Menu $menu)
        {
            return Item::where('menu_id', $menu->id)
                       ->get()
                       ->random();
        }
        
        /**
         * Gets Menu of Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function menu(Item $item)
        {
            return $item->menu;
        }
        
        /**
         * Gets parent Item of Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function parent(Item $item)
        {
            return $item->parent;
        }
        
        /**
         * Gets children Items of Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function children(Item $item)
        {
            return $item->children;
        }
        
        /**
         * Gets Item children count
         *
         * @param \App\Models\Item $item
         *
         * @return int
         */
        public function childrenCount(Item $item)
        {
            return $this->children($item)
                        ->count();
        }
        
        /**
         * Gets children Item count in specific layer by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @param                  $layerNo
         *
         * @return int
         */
        public function layerChildrenCountByMenu(Menu $menu, $layerNo)
        {
            $items = $this->layerByMenu($menu, $layerNo);
            
            if (is_null($items) || $items->isEmpty()) {
                return 0;
            }
            
            return $items->count();
        }
        
        /**
         * Gets descendant Items of Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function descendants(Item $item)
        {
            return $item->descendants;
        }
        
        /**
         * Gets Item descendants count
         *
         * @param \App\Models\Item $item
         *
         * @return int
         */
        public function descendantsCount(Item $item)
        {
            return $this->descendants($item)
                        ->count();
        }
        
        /**
         * Gets depth in Menu
         *
         * @param \App\Models\Item $item
         *
         * @return int
         */
        public function depth(Item $item)
        {
            $item = $this->getWithDepth($this->menu($item), $item->id);
            
            return is_null($item) ? 0 : ($item->depth + 1);
        }
        
        /**
         * Gets depth of deepest Item from provided Item
         *
         * @param \App\Models\Item $item
         *
         * @return int
         */
        public function depthOfDeepestFromItem(Item $item)
        {
            $deepest = $this->deepestFromItem($item);
            
            if (is_null($deepest)) {
                return 0;
            } else {
                return ($deepest->depth + 1);
            }
        }
        
        /**
         * Check if one Item is descendant of other Item
         *
         * @param \App\Models\Item $item
         * @param \App\Models\Item $parentItem
         *
         * @return boolean
         */
        public function isItemDescendantOfItem(Item $item, Item $parentItem)
        {
            return $item->isDescendantOf($parentItem);
        }
        
        /**
         * Creates new Item
         *
         * @param \App\Models\Menu $menu
         * @param array            $data
         *
         * @return collection|boolean
         */
        public function create(Menu $menu, array $data)
        {
            $item = new Item();
            
            $item->fill($data);
            $item->menu()
                 ->associate($menu);
            
            $response = $item->save();
            
            if ($response) {
                return $item;
            } else {
                return $response;
            }
        }
        
        /**
         * Creates new Item and make it as root Item of Menu.
         *
         * @param \App\Models\Menu $menu
         * @param array            $data
         *
         * @return collection|boolean
         */
        public function createAsRoot(Menu $menu, array $data)
        {
            $item = $this->create($menu, $data);
            
            if (!$item) {
                return false;
            }
            
            $response = $this->makeRoot($item);
            
            if ($response) {
                return $item;
            } else {
                return $response;
            }
        }
        
        /**
         * Creates new Item and make it as sub Item of other Item.
         *
         * @param \App\Models\Menu $menu
         * @param \App\Models\Item $parentItem
         * @param array            $data
         *
         * @return collection|boolean
         */
        public function createAsSub(Menu $menu, Item $parentItem, array $data)
        {
            $subItem = $this->create($menu, $data);
            
            if (!$subItem) {
                return false;
            }
            
            $response = $this->makeSub($parentItem, $subItem);
            
            if ($response) {
                return $subItem;
            } else {
                return $response;
            }
        }
        
        /**
         * Make Item as root Item of Menu.
         *
         * @param \App\Models\Item $item
         *
         * @return boolean
         */
        public function makeRoot(Item $item)
        {
            return $item->saveAsRoot();
        }
        
        /**
         * Make Item as sub Item of other Item.
         *
         * @param \App\Models\Item $parentItem
         * @param \App\Models\Item $subItem
         *
         * @return boolean
         */
        public function makeSub(Item $parentItem, Item $subItem)
        {
            return $parentItem->appendNode($subItem);
        }
        
        /**
         * Updates an Item
         *
         * @param \App\Models\Item $item
         * @param array            $data
         *
         * @return boolean
         */
        public function update(Item $item, array $data)
        {
            return $item->fill($data)
                        ->save();
        }
        
        /**
         * Change Menu of an Item
         *
         * @param \App\Models\Item $item
         * @param \App\Models\Menu $menu
         *
         * @return boolean
         */
        public function changeMenu(Item $item, Menu $menu)
        {
            $changeItemMenuResponse = $item->menu()
                                           ->associate($menu)
                                           ->save();
            
            if (!$changeItemMenuResponse) {
                return false;
            }
            
            foreach ($this->children($item) as $itemChild) {
                $changeItemChildMenuResponse = $this->changeMenu($itemChild, $menu);
                
                if (!$changeItemChildMenuResponse) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Fix tree of the Menu
         *
         * @param \App\Models\Menu $menu
         *
         * @return boolean
         */
        public function fixTree(Menu $menu)
        {
            return Item::scoped(['menu_id' => $menu->id])
                       ->fixTree();
        }
        
        /**
         * Change parent Item of an Item
         *
         * @param \App\Models\Item $item
         * @param \App\Models\Item $parentItem
         *
         * @return boolean
         */
        public function changeParentItem(Item $item, Item $parentItem = null)
        {
            if (is_null($parentItem)) {
                return $this->makeRoot($item);
            } else {
                return $parentItem->appendNode($item);
            }
        }
        
        /**
         * Deletes Item by ID
         *
         * @param \App\Models\Item $item
         *
         * @return boolean
         * @throws \Exception
         */
        public function delete(Item $item)
        {
            return $item->delete();
        }
        
        /**
         * Deletes Item descendants
         *
         * @param \App\Models\Item $item
         *
         * @return boolean
         * @throws \Exception
         */
        public function deleteDescendants(Item $item)
        {
            $items = $this->children($item);
            
            if (!$items) {
                return false;
            }
            
            foreach ($items as $item) {
                $itemDeleteResponse = $this->delete($item);
                
                if (!$itemDeleteResponse) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Deletes Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return boolean
         * @throws \Exception
         */
        public function deleteByMenu(Menu $menu)
        {
            $items = $this->rootByMenu($menu);
            
            if (!$items) {
                return false;
            }
            
            foreach ($items as $item) {
                $itemDeleteResponse = $this->delete($item);
                
                if (!$itemDeleteResponse) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Deletes specific layer Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @param                  $layerNo
         *
         * @return boolean
         * @throws \Exception
         */
        public function deleteLayerByMenu(Menu $menu, $layerNo)
        {
            $items = $this->layerByMenu($menu, $layerNo);
            
            if (!$items) {
                return false;
            }
            
            return $this->deleteLayer($items);
        }
        
        /**
         * Deletes specific layer Items by Menu ID
         *
         * @param \Illuminate\Database\Eloquent\Collection $items
         *
         * @return boolean
         * @throws \Exception
         */
        public function deleteLayer(Collection $items)
        {
            foreach ($items as $item) {
                $itemParent = $this->parent($item);
                $itemChildren = $this->children($item);
                
                foreach ($itemChildren as $itemChild) {
                    if (is_null($itemParent)) {
                        $changingParentResponse = $this->makeRoot($itemChild);
                    } else {
                        $changingParentResponse = $this->makeSub($itemParent, $itemChild);
                    }
                    
                    if (!$changingParentResponse) {
                        return false;
                    }
                }
                
                $itemDeleteResponse = $this->delete($item);
                
                if (!$itemDeleteResponse) {
                    return false;
                }
            }
            
            return true;
        }
    }
