<?php
    
    namespace App\Services;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Illuminate\Database\Eloquent\Collection as EloquentCollection;
    
    use App\Repositories\Interfaces\MenuRepositoryInterface;
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Models\Menu;
    use App\Models\Item;

    class ItemService extends Controller
    {
        /**
         * Key name of sub Items in data array/object.
         */
        const SUB_ITEMS_KEY = 'children';
        
        protected $menuRepository;
        protected $itemRepository;
        
        /**
         * Create a new controller instance.
         *
         * @param \App\Repositories\Interfaces\MenuRepositoryInterface $menuRepository
         * @param \App\Repositories\Interfaces\ItemRepositoryInterface $itemRepository
         */
        public function __construct(MenuRepositoryInterface $menuRepository, ItemRepositoryInterface $itemRepository)
        {
            $this->menuRepository = $menuRepository;
            $this->itemRepository = $itemRepository;
        }
        
        /**
         * Check if two parent Items are the same.
         *
         * @param \App\Models\Item|null $parentItem1
         * @param \App\Models\Item|null $parentItem2
         *
         * @return bool
         */
        public function areParentItemsTheSame(Item $parentItem1 = null, Item $parentItem2 = null)
        {
            /**
             * if both are the same Item objects
             * or both of them are null
             */
            return ((($parentItem1 instanceof Item && $parentItem2 instanceof Item) && $parentItem1->is($parentItem2)) || (is_null($parentItem1) && is_null($parentItem2)));
        }
        
        /**
         * Check if provided Item data is valid.
         *
         * @param \App\Models\Menu $menu
         * @param array            $itemData
         * @param int              $depth              of parent object
         * @param int              $layerChildrenCount of parent object
         *
         * @return boolean|array True if valid or array with status code and errors if otherwise.
         */
        public function isItemDataValid(Menu $menu, array $itemData, int $depth = 0, int $layerChildrenCount = 0)
        {
            if (!empty($itemData)) {
                if (!is_null($menu->max_depth) && ($depth + 1) > $menu->max_depth) {
                    return [
                        'statusCode' => 400,
                        'data'       => ['errors' => [__('menu.max_depth', ['maxDepth' => $menu->max_depth])]]
                    ];
                }
                
                
                if (!is_null($menu->max_children) && ($layerChildrenCount + 1) > $menu->max_children) {
                    return [
                        'statusCode' => 400,
                        'data'       => ['errors' => [__('menu.max_children', ['maxChildren' => $menu->max_children])]]
                    ];
                }
                
                
                $itemContentDataValid = $this->isItemContentDataValid($itemData);
                
                if ($itemContentDataValid !== true) {
                    return $itemContentDataValid;
                }
            }
            
            
            return true;
        }
        
        /**
         * Check if Items and its descendants can fit into the Menu by respecting Depth restriction.
         *
         * This might produce wrong results if some of the items are not changing its Menu and/or parent Item.
         * In case of Menu changing, both - Menu and parent Item - must be changed.
         * In case of parent Item changing, Menu can stay the same.
         *
         * @param \App\Models\Menu               $menu
         * @param \Illuminate\Support\Collection $items
         * @param \App\Models\Item               $parentItem
         *
         * @return boolean|array True if can or array with status code and errors if otherwise.
         */
        public function canItemsAndDescendantsFitIntoMenuByDepth(Menu $menu, Collection $items, Item $parentItem = null)
        {
            if (!$items->isEmpty()) {
                if (is_null($parentItem)) {
                    $parentDepth = 0;
                } else {
                    $parentDepth = $this->itemRepository->depth($parentItem);
                }
                
                
                $maxLayerCount = 0;
                
                foreach ($items as $item) {
                    $itemDepth = $this->itemRepository->depth($item);
                    $deepestItemDepth = $this->itemRepository->depthOfDeepestFromItem($item);
                    
                    $itemLayerCount = ($deepestItemDepth - $itemDepth);
                    
                    if ($itemLayerCount > $maxLayerCount) {
                        $maxLayerCount = $itemLayerCount;
                    }
                }
                
                $layerDepth = (($parentDepth + 1) + $maxLayerCount);
                
                
                if (!is_null($menu->max_depth) && $layerDepth > $menu->max_depth) {
                    return false;
                }
            }
            
            
            return true;
        }
        
        /**
         * Check if Items and its descendants can fit into the Menu by respecting Max Children restriction.
         *
         * This might produce wrong results if some of the items are not changing its Menu and/or parent Item.
         * In case of Menu changing, both - Menu and parent Item - must be changed.
         * In case of parent Item changing, Menu can stay the same.
         *
         * @param \App\Models\Menu               $menu
         * @param \Illuminate\Support\Collection $items
         * @param \App\Models\Item|null          $parentItem
         * @param int                            $depth
         *
         * @return boolean|array True if can or array with status code and errors if otherwise.
         */
        public function canItemsAndDescendantsFitIntoMenuByChildrenCount(Menu $menu, Collection $items, Item $parentItem = null, int $depth = null)
        {
            if (!$items->isEmpty()) {
                if (is_null($depth)) {
                    if (is_null($parentItem)) {
                        $depth = 1;
                    } else {
                        $depth = ($this->itemRepository->depth($parentItem) + 1);
                    }
                }
                
                
                $layerChildrenCount = $this->itemRepository->layerChildrenCountByMenu($menu, $depth);
                $itemsCount = 0;
                
                foreach ($items as $item) {
                    if (!$this->itemRepository->menu($item)
                                              ->is($menu) || $depth != $this->itemRepository->depth($item)) {
                        $itemsCount++;
                    }
                }
                
                if (!is_null($menu->max_children) && ($layerChildrenCount + $itemsCount) > $menu->max_children) {
                    return false;
                }
                
                
                foreach ($items as $item) {
                    $canItemsAndDescendantsFitIntoMenuByChildrenCount = $this->canItemsAndDescendantsFitIntoMenuByChildrenCount($menu, $this->itemRepository->children($item), $item, ($depth + 1));
                    
                    if (!$canItemsAndDescendantsFitIntoMenuByChildrenCount) {
                        return false;
                    }
                }
            }
            
            
            return true;
        }
        
        /**
         * Check if provided Item content data is valid.
         *
         * @param array $itemContentData
         *
         * @return boolean|array True if valid or array with status code and errors if otherwise.
         */
        public function isItemContentDataValid(array $itemContentData)
        {
            if (!empty($itemContentData)) {
                $validFields = Arr::only($itemContentData, [
                    'name'
                ]);
                
                $itemValidator = Validator::make($validFields, [
                    'name' => [
                        'required',
                        'string',
                        'max:' . Item::MAX_NAME_LENGTH
                    ]
                ]);
                
                if ($itemValidator->fails()) {
                    return [
                        'statusCode' => 400,
                        'data'       => [
                            'itemName' => $validFields['name'],
                            'errors'   => $itemValidator->errors()
                        ]
                    ];
                }
            }
            
            return true;
        }
        
        /**
         * Check if provided Items updating data is valid.
         *
         * @param \App\Models\Item $item
         * @param \App\Models\Menu $menu
         * @param \App\Models\Item $parentItem
         * @param array            $itemData
         *
         * @return boolean|array True if valid or array with status code and errors if otherwise.
         */
        public function isItemUpdatingDataValid(Item $item, array $itemData, Menu $menu, Item $parentItem = null)
        {
            $currentParentItem = $this->itemRepository->parent($item);
            
            if (!$this->itemRepository->menu($item)
                                      ->is($menu)) {
                /**
                 * Item is changing its Menu therefore its parent as well
                 */
                $itemChangingParent = true;
            }
            
            if (is_null($parentItem)) {
                /**
                 * This is root Item
                 */
                
                if (!is_null($currentParentItem)) {
                    /**
                     * Item is changing its parent Item
                     */
                    $itemChangingParent = true;
                }
            } else {
                if ($item->is($parentItem)) {
                    return [
                        'statusCode' => 500,
                        'data'       => ['errors' => [__('item.updating_item_parent_to_itself_is_not_allowed')]]
                    ];
                }
                
                if (!$this->areParentItemsTheSame($currentParentItem, $parentItem)) {
                    /**
                     * Item is changing its parent Item
                     */
                    $itemChangingParent = true;
                    
                    if (!$this->itemRepository->menu($parentItem)
                                              ->is($menu)) {
                        return [
                            'statusCode' => 500,
                            'data'       => ['errors' => [__('menu.parent_item_doesnt_belong')]]
                        ];
                    }
                    
                    /**
                     * Check if new parent Item is not child of current Item
                     */
                    if ($this->itemRepository->isItemDescendantOfItem($parentItem, $item)) {
                        return [
                            'statusCode' => 400,
                            'data'       => ['errors' => [__('item.new_parent_item_is_a_descendant_of_current_item')]]
                        ];
                    }
                }
            }
            
            if (isset($itemChangingParent) && $itemChangingParent) {
                if (!$this->canItemsAndDescendantsFitIntoMenuByDepth($menu, collect([$item]), $parentItem)) {
                    return [
                        'statusCode' => 400,
                        'data'       => ['errors' => [__('menu.max_depth', ['maxDepth' => $menu->max_depth])]]
                    ];
                }
                
                if (!$this->canItemsAndDescendantsFitIntoMenuByChildrenCount($menu, collect([$item]), $parentItem)) {
                    return [
                        'statusCode' => 400,
                        'data'       => ['errors' => [__('menu.max_children', ['maxChildren' => $menu->max_children])]]
                    ];
                }
            }
            
            /**
             * Check if Item content data is valid.
             */
            $itemDataValid = $this->isItemContentDataValid($itemData);
            
            if ($itemDataValid !== true) {
                return $itemDataValid;
            }
            
            return true;
        }
        
        /**
         * Check if provided Items data is valid.
         *
         * @param \App\Models\Menu $menu
         * @param array            $itemsData
         * @param int              $depth
         * @param int|null         $layerChildrenCount
         *
         * @return boolean|array True if valid or array with status code and errors if otherwise.
         */
        public function isItemsCratingDataValid(Menu $menu, array $itemsData, int $depth = 0, int $layerChildrenCount = null)
        {
            if (is_null($layerChildrenCount)) {
                $layerChildrenCount = $this->itemRepository->layerChildrenCountByMenu($menu, ($depth + 1));
            }
            
            foreach ($itemsData as $itemData) {
                $itemDataValid = $this->isItemDataValid($menu, $itemData, $depth, $layerChildrenCount++);
                
                if ($itemDataValid !== true) {
                    return $itemDataValid;
                }
                
                if (isset($itemData[self::SUB_ITEMS_KEY])) {
                    $itemsDataValid = $this->isItemsCratingDataValid($menu, $itemData[self::SUB_ITEMS_KEY], ($depth + 1));
                    
                    if ($itemsDataValid !== true) {
                        return $itemsDataValid;
                    }
                }
            }
            
            return true;
        }
        
        /**
         * Check if provided Items data is valid.
         *
         * @param \App\Models\Menu                         $menu
         * @param int                                      $layerNo
         * @param \Illuminate\Database\Eloquent\Collection $items
         *
         * @return boolean|array True if valid or array with status code and errors if otherwise.
         */
        public function canMenuLayerItemsBeDeleted(Menu $menu, int $layerNo, EloquentCollection $items)
        {
            /**
             * Check if Max Children restriction is not violated.
             */
            if ($layerNo <= 1) {
                $menuChildrenCount = $this->itemRepository->rootByMenu($menu)
                                                          ->count();
                
                foreach ($items as $item) {
                    $menuChildrenCount--;
                    
                    $itemChildren = $this->itemRepository->children($item);
                    
                    $menuChildrenCount += $itemChildren->count();
                    
                    if (!is_null($menu->max_children) && $menuChildrenCount > $menu->max_children) {
                        return false;
                    }
                }
            } else {
                $parentsItemCount = [];
                
                foreach ($items as $item) {
                    $itemParent = $this->itemRepository->parent($item);
                    $itemChildren = $this->itemRepository->children($item);
                    
                    if (!array_key_exists(strval($itemParent->id), $parentsItemCount)) {
                        $parentsItemCount[strval($itemParent->id)] = $this->itemRepository->children($itemParent)
                                                                                          ->count();
                    }
                    
                    $parentsItemCount[strval($itemParent->id)] += ($itemChildren->count() - 1);
                    
                    if (!is_null($menu->max_children) && $parentsItemCount[strval($itemParent->id)] > $menu->max_children) {
                        return false;
                    }
                }
            }
            
            return true;
        }
        
        /**
         * Create Item from valid data array. Data array must be validated before calling this function.
         *
         * @param \App\Models\Menu      $menu
         * @param \App\Models\Item|null $parentItem
         * @param array                 $itemData
         *
         * @return collection|array collection if created or array with status code and errors if otherwise.
         */
        public function createItem(Menu $menu, Item $parentItem = null, array $itemData)
        {
            $validFields = Arr::only($itemData, [
                'name'
            ]);
            
            if (is_null($parentItem)) {
                $item = $this->itemRepository->createAsRoot($menu, $validFields);
            } else {
                $item = $this->itemRepository->createAsSub($menu, $parentItem, $validFields);
            }
            
            if (!$item) {
                return [
                    'statusCode' => 500,
                    'data'       => [
                        'itemName' => $validFields['name'],
                        'errors'   => __('item.could_not_save')
                    ]
                ];
            }
            
            return $item;
        }
        
        /**
         * Create Items from valid data array. Data array must be validated before calling this function.
         *
         * @param \App\Models\Menu      $menu
         * @param \App\Models\Item|null $parentItem
         * @param array                 $itemsData
         *
         * @return Illuminate\Database\Eloquent\Collection|array collection if all Items created or array with status code and errors if otherwise.
         */
        public function createItems(Menu $menu, Item $parentItem = null, array $itemsData)
        {
            $createdItems = [];
            
            foreach ($itemsData as $itemData) {
                $item = $this->createItem($menu, $parentItem, $itemData);
                
                if (!($item instanceof Item)) {
                    return $item;
                }
                
                if (isset($itemData[self::SUB_ITEMS_KEY]) && !empty($itemData[self::SUB_ITEMS_KEY])) {
                    $subItems = $this->createItems($menu, $item, $itemData[self::SUB_ITEMS_KEY]);
                    
                    if (!($subItems instanceof Collection)) {
                        return $subItems;
                    } else {
                        $item->createdDescedants = $subItems;
                    }
                }
                
                array_push($createdItems, $item);
            }
            
            return collect($createdItems);
        }
        
        /**
         * Update Item from valid data array. Data array, Menu and parent Item must be validated before calling this function.
         *
         * @param \App\Models\Item      $item
         * @param \App\Models\Menu      $menu
         * @param \App\Models\Item|null $parentItem
         * @param array                 $itemData
         *
         * @return collection|array collection if created or array with status code and errors if otherwise.
         */
        public function updateItem(Item $item, Menu $menu, Item $parentItem = null, array $itemData)
        {
            /**
             * Update Item content
             */
            $validFields = Arr::only($itemData, [
                'name'
            ]);
            
            $updateItemResponse = $this->itemRepository->update($item, $validFields);
            
            if (!$updateItemResponse) {
                return [
                    'statusCode' => 500,
                    'data'       => ['errors' => [__('item.could_not_update')]]
                ];
            }
            
            /**
             * Change Menu of the Item
             */
            $currentItemMenu = $this->itemRepository->menu($item);
            
            if (!$currentItemMenu->is($menu)) {
                $changeItemMenuResponse = $this->itemRepository->changeMenu($item, $menu);
                
                if (!$changeItemMenuResponse) {
                    return [
                        'statusCode' => 500,
                        'data'       => ['errors' => [__('item.could_not_change_menu')]]
                    ];
                }
                
                /**
                 * Fix old and new Menu Item trees because Items were moved
                 */
                $this->itemRepository->fixTree($currentItemMenu);
                $this->itemRepository->fixTree($menu);
            }
            
            /**
             * Change parent Item of the Item
             */
            if (!$this->areParentItemsTheSame($this->itemRepository->parent($item), $parentItem)) {
                $changeItemParentItemResponse = $this->itemRepository->changeParentItem($item, $parentItem);
                
                if (!$changeItemParentItemResponse) {
                    return [
                        'statusCode' => 500,
                        'data'       => ['errors' => [__('item.could_not_change_parent_item')]]
                    ];
                }
            }
            
            return true;
        }
    }
