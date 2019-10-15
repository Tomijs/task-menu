<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    
    use App\Services\ItemService;
    
    use App\Repositories\Interfaces\MenuRepositoryInterface;
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Models\Item;
    
    use App\Http\Resources\Item\WithDescendants\Extended as ItemExtendedWithDescendantsResource;

    class ItemController extends Controller
    {
        protected $itemService;
        
        protected $menuRepository;
        protected $itemRepository;
        
        /**
         * Create a new controller instance.
         *
         * @param \App\Services\ItemService                            $itemService
         * @param \App\Repositories\Interfaces\MenuRepositoryInterface $menuRepository
         * @param \App\Repositories\Interfaces\ItemRepositoryInterface $itemRepository
         */
        public function __construct(ItemService $itemService, MenuRepositoryInterface $menuRepository, ItemRepositoryInterface $itemRepository)
        {
            $this->itemService = $itemService;
            
            $this->menuRepository = $menuRepository;
            $this->itemRepository = $itemRepository;
        }
        
        /**
         * Store a newly created resource in storage.
         *
         * @param  \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            if (empty($request->all())) {
                return response()->json(['errors' => [__('apiRequest.invalid_or_empty_request_body')]], 406);
            }
            
            
            $validFields = $request->only([
                'menu_id',
                'parent_item_id',
                'name'
            ]);
            
            $itemValidator = Validator::make($validFields, [
                'menu_id'        => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:menus,id'
                ],
                'parent_item_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'exists:items,id'
                ]
            ]);
            
            if ($itemValidator->fails()) {
                return response()->json(['errors' => $itemValidator->errors()], 400);
            }
            
            
            $menu = $this->menuRepository->get($validFields['menu_id']);
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.could_not_retrieve')]], 500);
            }
            
            
            if (!isset($validFields['parent_item_id'])) {
                $parentItem = null;
            } else {
                $parentItem = $this->itemRepository->get($validFields['parent_item_id']);
                
                if (!$parentItem) {
                    return response()->json(['errors' => [__('item.could_not_retrieve')]], 500);
                } elseif (!$this->itemRepository->menu($parentItem)
                                                 ->is($menu)) {
                    return response()->json(['errors' => [__('menu.parent_item_doesnt_belong')]], 500);
                }
            }
            
            
            /**
             * Check if provided data is valid for creating Item.
             */
            if (is_null($parentItem)) {
                $layerDepth = 0;
                $layerChildrenCount = $this->menuRepository->firstLayerChildrenCount($menu);
            } else {
                $layerDepth = $this->itemRepository->depth($parentItem);
                $layerChildrenCount = $this->itemRepository->layerChildrenCountByMenu($menu, ($layerDepth + 1));
            }
            
            $itemDataValid = $this->itemService->isItemDataValid($menu, $validFields, $layerDepth, $layerChildrenCount);
            
            if ($itemDataValid !== true) {
                return response()->json($itemDataValid['data'], $itemDataValid['statusCode']);
            }
            
            
            /**
             * Create Item.
             */
            $item = $this->itemService->createItem($menu, $parentItem, $validFields);
            
            if (!($item instanceof Item)) {
                return response()->json($item['data'], $item['statusCode']);
            }
            
            
            return response()->json(new ItemExtendedWithDescendantsResource($item), 201);
        }
        
        /**
         * Display the specified resource.
         *
         * @param $itemId
         *
         * @return \Illuminate\Http\Response
         */
        public function show($itemId)
        {
            $getItemValidator = Validator::make([
                'id' => $itemId
            ], [
                'id' => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:items'
                ]
            ]);
            
            if ($getItemValidator->fails()) {
                return response()->json(['errors' => $getItemValidator->errors()], 400);
            }
            
            $item = $this->itemRepository->get($itemId);
            
            if (!$item) {
                return response()->json(['errors' => [__('item.could_not_retrieve')]], 500);
            }
            
            return response()->json(new ItemExtendedWithDescendantsResource($item), 200);
        }
        
        /**
         * Update the specified resource in storage.
         *
         * @param                           $itemId
         * @param  \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\Response
         */
        public function update($itemId, Request $request)
        {
            if (empty($request->all())) {
                return response()->json(['errors' => [__('apiRequest.invalid_or_empty_request_body')]], 406);
            }
            
            $getItemValidator = Validator::make([
                'id' => $itemId
            ], [
                'id' => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:items'
                ]
            ]);
            
            if ($getItemValidator->fails()) {
                return response()->json(['errors' => $getItemValidator->errors()], 400);
            }
            
            $item = $this->itemRepository->get($itemId);
            
            if (!$item) {
                return response()->json(['errors' => [__('item.could_not_retrieve')]], 500);
            }
            
            
            $validFields = $request->only([
                'menu_id',
                'parent_item_id',
                'name'
            ]);
            
            $itemValidator = Validator::make($validFields, [
                'menu_id'        => [
                    'nullable',
                    'integer',
                    'min:1',
                    'exists:menus,id'
                ],
                'parent_item_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'exists:items,id'
                ]
            ]);
            
            if ($itemValidator->fails()) {
                return response()->json(['errors' => $itemValidator->errors()], 400);
            }
            
            
            /**
             * Get Menu
             */
            if (!isset($validFields['menu_id'])) {
                $menu = $this->itemRepository->menu($item);
            } else {
                $menu = $this->menuRepository->get($validFields['menu_id']);
                
                if (!$menu) {
                    return response()->json(['errors' => [__('menu.could_not_retrieve')]], 500);
                }
                
                if (!isset($validFields['parent_item_id'])) {
                }
            }
            
            /**
             * Get parent Item
             */
            if (!isset($validFields['parent_item_id'])) {
                if (!$this->itemRepository->menu($item)
                                          ->is($menu)) {
                    $parentItem = null;
                } else {
                    $parentItem = $this->itemRepository->parent($item);
                }
            } else {
                $parentItem = $this->itemRepository->get($validFields['parent_item_id']);
                
                if (!$parentItem) {
                    return response()->json(['errors' => [__('item.could_not_retrieve_parent')]], 500);
                }
                
                if (!isset($validFields['menu_id']) && !$this->itemService->areParentItemsTheSame($this->itemRepository->parent($item), $parentItem)) {
                    return response()->json(['errors' => [__('item.menu_id_must_be_provided_if_changing_item_parent_from_different_menu')]], 400);
                }
            }
            
            /**
             * Check if updating Item data is valid
             */
            $itemUpdatingDataValid = $this->itemService->isItemUpdatingDataValid($item, $validFields, $menu, $parentItem);
            
            if ($itemUpdatingDataValid !== true) {
                return response()->json($itemUpdatingDataValid['data'], $itemUpdatingDataValid['statusCode']);
            }
            
            
            /**
             * Update Item.
             */
            $updateItemResponse = $this->itemService->updateItem($item, $menu, $parentItem, $validFields);
            
            if ($updateItemResponse !== true) {
                return response()->json($updateItemResponse['data'], $updateItemResponse['statusCode']);
            }
            
            
            return response()->json(new ItemExtendedWithDescendantsResource($item->fresh()), 201);
        }
        
        /**
         * Remove the specified resource from storage.
         *
         * @param $itemId
         *
         * @return \Illuminate\Http\Response
         * @throws \Exception
         */
        public function destroy($itemId)
        {
            $getItemValidator = Validator::make([
                'id' => $itemId
            ], [
                'id' => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:items'
                ]
            ]);
            
            if ($getItemValidator->fails()) {
                return response()->json(['errors' => $getItemValidator->errors()], 400);
            }
            
            $item = $this->itemRepository->get($itemId);
            
            if (!$item) {
                return response()->json(['errors' => [__('item.could_not_retrieve')]], 500);
            }
            
            
            $deleteItemResponse = $this->itemRepository->delete($item);
            
            if (!$deleteItemResponse) {
                return response()->json(['errors' => [__('item.could_not_delete')]], 500);
            }
            
            
            return response()->noContent();
        }
    }
