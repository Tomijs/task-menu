<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\Validator;
    
    use App\Services\ItemService;
    
    use App\Repositories\Interfaces\MenuRepositoryInterface;
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Http\Resources\Item\Basic as ItemResource;

    class MenuLayerController extends Controller
    {
        protected $itemService;
        
        protected $menuRepository;
        protected $itemRepository;
        
        /**
         * Create a new controller instance.
         *
         * @param \App\Services\ItemService                            $itemService
         *
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
         * Display the specified resource.
         *
         * @param $menuId
         * @param $layerNo
         *
         * @return \Illuminate\Http\Response
         */
        public function show($menuId, $layerNo)
        {
            $getMenuValidator = Validator::make([
                'id'      => $menuId,
                'layerNo' => $layerNo
            ], [
                'id'      => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:menus'
                ],
                'layerNo' => [
                    'required',
                    'integer',
                    'min:1'
                ]
            ]);
            
            if ($getMenuValidator->fails()) {
                return response()->json(['errors' => $getMenuValidator->errors()], 400);
            }
            
            $menu = $this->menuRepository->get($menuId);
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.could_not_retrieve')]], 500);
            }
            
            
            $items = $this->itemRepository->layerByMenu($menu, $layerNo);
            
            if (!$items) {
                return response()->json(['errors' => [__('item.could_not_retrieve_menu_items')]], 500);
            }
            
            
            return response()->json(ItemResource::collection($items), 200);
        }
        
        /**
         * Remove the specified resource from storage.
         *
         * @param $menuId
         * @param $layerNo
         *
         * @return \Illuminate\Http\Response
         * @throws \Exception
         */
        public function destroy($menuId, $layerNo)
        {
            $getMenuValidator = Validator::make([
                'id'      => $menuId,
                'layerNo' => $layerNo
            ], [
                'id'      => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:menus'
                ],
                'layerNo' => [
                    'required',
                    'integer',
                    'min:1'
                ]
            ]);
            
            if ($getMenuValidator->fails()) {
                return response()->json(['errors' => $getMenuValidator->errors()], 400);
            }
            
            $menu = $this->menuRepository->get($menuId);
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.could_not_retrieve')]], 500);
            }
            
            
            $items = $this->itemRepository->layerByMenu($menu, $layerNo);
            
            if (!$items) {
                return response()->json(['errors' => [__('item.could_not_retrieve_menu_items')]], 500);
            }
            
            
            /**
             * Check if possible to delete layer and attach children Items to the parent Items.
             */
            $canMenuLayerBeDeleted = $this->itemService->canMenuLayerItemsBeDeleted($menu, $layerNo, $items);
            
            if (!$canMenuLayerBeDeleted) {
                return response()->json(['errors' => [__('item.menu_layer_cannot_be_deleted')]], 500);
            }
            
            
            $deleteMenuLayerResponse = $this->itemRepository->deleteLayer($items);
            
            if (!$deleteMenuLayerResponse) {
                return response()->json(['errors' => [__('item.could_not_delete_menu_layer_items')]], 500);
            }
            
            
            return response()->noContent();
        }
    }
