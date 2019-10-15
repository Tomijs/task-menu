<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Collection;
    
    use App\Services\ItemService;
    
    use App\Repositories\Interfaces\MenuRepositoryInterface;
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Http\Resources\Item\WithDescendants\Basic as ItemWithDescendantsResource;

    class MenuItemController extends Controller
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
         * Store a newly created resource in storage.
         *
         * @param                           $menuId
         * @param  \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\Response
         */
        public function store($menuId, Request $request)
        {
            if (empty($request->all())) {
                return response()->json(['errors' => [__('apiRequest.invalid_or_empty_request_body')]], 406);
            }
            
            
            $getMenuValidator = Validator::make([
                'id' => $menuId
            ], [
                'id' => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:menus'
                ]
            ]);
            
            if ($getMenuValidator->fails()) {
                return response()->json(['errors' => $getMenuValidator->errors()], 400);
            }
            
            $menu = $this->menuRepository->get($menuId);
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.could_not_retrieve')]], 500);
            }
            
            
            /**
             * Check if provided data is valid for creating Items.
             */
            $itemsDataValid = $this->itemService->isItemsCratingDataValid($menu, $request->all());
            
            if ($itemsDataValid !== true) {
                return response()->json($itemsDataValid['data'], $itemsDataValid['statusCode']);
            }
            
            
            /**
             * Create Items.
             */
            $items = $this->itemService->createItems($menu, null, $request->all());
            
            if (!($items instanceof Collection)) {
                return response()->json($items['data'], $items['statusCode']);
            }
            
            
            return response()->json(ItemWithDescendantsResource::collection($items), 201);
        }
        
        /**
         * Display the specified resource.
         *
         * @param $menuId
         *
         * @return \Illuminate\Http\Response
         */
        public function show($menuId)
        {
            $getMenuValidator = Validator::make([
                'id' => $menuId
            ], [
                'id' => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:menus'
                ]
            ]);
            
            if ($getMenuValidator->fails()) {
                return response()->json(['errors' => $getMenuValidator->errors()], 400);
            }
            
            
            $menu = $this->menuRepository->get($menuId);
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.could_not_retrieve')]], 500);
            }
            
            
            $items = $this->itemRepository->rootByMenu($menu);
            
            if (!$items) {
                return response()->json(['errors' => [__('item.could_not_retrieve_menu_items')]], 500);
            }
            
            
            return response()->json(ItemWithDescendantsResource::collection($items), 200);
        }
        
        /**
         * Remove the specified resource from storage.
         *
         * @param $menuId
         *
         * @return \Illuminate\Http\Response
         * @throws \Exception
         */
        public function destroy($menuId)
        {
            $getMenuValidator = Validator::make([
                'id' => $menuId
            ], [
                'id' => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:menus'
                ]
            ]);
            
            if ($getMenuValidator->fails()) {
                return response()->json(['errors' => $getMenuValidator->errors()], 400);
            }
            
            $menu = $this->menuRepository->get($menuId);
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.could_not_retrieve')]], 500);
            }
            
            $deleteItemsResponse = $this->itemRepository->deleteByMenu($menu);
            
            if (!$deleteItemsResponse) {
                return response()->json(['errors' => [__('item.could_not_delete_menu_items')]], 500);
            }
            
            return response()->noContent();
        }
    }
