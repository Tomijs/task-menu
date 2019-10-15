<?php
    
    namespace App\Repositories\Interfaces;

    use App\Models\Menu;

    interface MenuRepositoryInterface
    {
        /**
         * Gets Menu by ID
         *
         * @param int $id
         *
         * @return collection
         */
        public function get(int $id);
        
        /**
         * Gets first Menu
         *
         * @return null|collection
         */
        public function first();
        
        /**
         * Gets a random Menu
         *
         * @return collection
         */
        public function random();
        
        /**
         * Gets Item depth of Menu
         *
         * @param \App\Models\Menu $menu
         *
         * @return int
         */
        public function depth(Menu $menu);
        
        /**
         * Gets Item children count in first layer of Menu
         *
         * @param \App\Models\Menu $menu
         *
         * @return int
         */
        public function firstLayerChildrenCount(Menu $menu);
        
        /**
         * Gets number of the most Item children in any layer of Menu
         *
         * @param \App\Models\Menu $menu
         *
         * @return int
         */
        public function mostChildrenCountInAnyLayer(Menu $menu);
        
        /**
         * Creates new Menu
         *
         * @param array $data
         *
         * @return collection|boolean
         */
        public function create(array $data);
        
        /**
         * Updates a Menu
         *
         * @param \App\Models\Menu $menu
         * @param array            $data
         *
         * @return boolean
         */
        public function update(Menu $menu, array $data);
        
        /**
         * Deletes Menu by ID
         *
         * @param \App\Models\Menu $menu
         *
         * @return boolean
         * @throws \Exception
         */
        public function delete(Menu $menu);
    }
