<?php
    
    namespace App\Repositories\Interfaces;

    use Illuminate\Database\Eloquent\Collection;
    
    use App\Models\Menu;
    use App\Models\Item;

    interface ItemRepositoryInterface
    {
        /**
         * Gets Item by ID
         *
         * @param int $id
         *
         * @return collection
         */
        public function get(int $id);
        
        /**
         * Gets Item by ID with depth
         *
         * @param \App\Models\Menu $menu
         * @param int              $id
         *
         * @return collection
         */
        public function getWithDepth(Menu $menu, int $id);
        
        /**
         * Gets first Item
         *
         * @return null|collection
         */
        public function first();
        
        /**
         * Gets deepest Item from provided Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function deepestFromItem(Item $item);
        
        /**
         * Gets Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return collection
         */
        public function getByMenu(Menu $menu);
        
        /**
         * Gets Items by Menu ID with depth column
         *
         * @param \App\Models\Menu $menu
         *
         * @return collection
         */
        public function getByMenuWithDepth(Menu $menu);
        
        /**
         * Gets root Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return collection
         */
        public function rootByMenu(Menu $menu);
        
        /**
         * Gets specific layer Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @param                  $layerNo
         *
         * @return collection
         */
        public function layerByMenu(Menu $menu, $layerNo);
        
        /**
         * Gets a random Item
         *
         * @return collection
         */
        public function random();
        
        /**
         * Gets a random Item by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return collection
         */
        public function randomByMenu(Menu $menu);
        
        /**
         * Gets Menu of Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function menu(Item $item);
        
        /**
         * Gets parent Item of Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function parent(Item $item);
        
        /**
         * Gets children Items of Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function children(Item $item);
        
        /**
         * Gets Item children count
         *
         * @param \App\Models\Item $item
         *
         * @return int
         */
        public function childrenCount(Item $item);
        
        /**
         * Gets children Item count in specific layer by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @param                  $layerNo
         *
         * @return int
         */
        public function layerChildrenCountByMenu(Menu $menu, $layerNo);
        
        /**
         * Gets descendant Items of Item
         *
         * @param \App\Models\Item $item
         *
         * @return collection
         */
        public function descendants(Item $item);
        
        /**
         * Gets Item descendants count
         *
         * @param \App\Models\Item $item
         *
         * @return int
         */
        public function descendantsCount(Item $item);
        
        /**
         * Gets depth in Menu
         *
         * @param \App\Models\Item $item
         *
         * @return int
         */
        public function depth(Item $item);
        
        /**
         * Gets depth of deepest Item from provided Item
         *
         * @param \App\Models\Item $item
         *
         * @return int
         */
        public function depthOfDeepestFromItem(Item $item);
        
        /**
         * Check if one Item is descendant of other Item
         *
         * @param \App\Models\Item $item
         * @param \App\Models\Item $parentItem
         *
         * @return boolean
         */
        public function isItemDescendantOfItem(Item $item, Item $parentItem);
        
        /**
         * Creates new Item
         *
         * @param \App\Models\Menu $menu
         * @param array            $data
         *
         * @return collection|boolean
         */
        public function create(Menu $menu, array $data);
        
        /**
         * Creates new Item and make it as root Item of Menu.
         *
         * @param \App\Models\Menu $menu
         * @param array            $data
         *
         * @return collection|boolean
         */
        public function createAsRoot(Menu $menu, array $data);
        
        /**
         * Creates new Item and make it as sub Item of other Item.
         *
         * @param \App\Models\Menu $menu
         * @param \App\Models\Item $parentItem
         * @param array            $data
         *
         * @return collection|boolean
         */
        public function createAsSub(Menu $menu, Item $parentItem, array $data);
        
        /**
         * Make Item as root Item of Menu.
         *
         * @param \App\Models\Item $item
         *
         * @return boolean
         */
        public function makeRoot(Item $item);
        
        /**
         * Make Item as sub Item of other Item.
         *
         * @param \App\Models\Item $parentItem
         * @param \App\Models\Item $subItem
         *
         * @return boolean
         */
        public function makeSub(Item $parentItem, Item $subItem);
        
        /**
         * Updates an Item
         *
         * @param \App\Models\Item $item
         * @param array            $data
         *
         * @return boolean
         */
        public function update(Item $item, array $data);
        
        /**
         * Change Menu of an Item
         *
         * @param \App\Models\Item $item
         * @param \App\Models\Menu $menu
         *
         * @return boolean
         */
        public function changeMenu(Item $item, Menu $menu);
        
        /**
         * Fix tree of the Menu
         *
         * @param \App\Models\Menu $menu
         *
         * @return boolean
         */
        public function fixTree(Menu $menu);
        
        /**
         * Change parent Item of an Item
         *
         * @param \App\Models\Item $item
         * @param \App\Models\Item $parentItem
         *
         * @return boolean
         */
        public function changeParentItem(Item $item, Item $parentItem);
        
        /**
         * Deletes Item by ID
         *
         * @param \App\Models\Item $item
         *
         * @return boolean
         * @throws \Exception
         */
        public function delete(Item $item);
        
        /**
         * Deletes Item descendants
         *
         * @param \App\Models\Item $item
         *
         * @return boolean
         * @throws \Exception
         */
        public function deleteDescendants(Item $item);
        
        /**
         * Deletes Items by Menu ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return boolean
         * @throws \Exception
         */
        public function deleteByMenu(Menu $menu);
        
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
        public function deleteLayerByMenu(Menu $menu, $layerNo);
        
        /**
         * Deletes specific layer Items by Menu ID
         *
         * @param \Illuminate\Database\Eloquent\Collection $items
         *
         * @return boolean
         * @throws \Exception
         */
        public function deleteLayer(Collection $items);
    }
