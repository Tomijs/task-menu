<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Collection;
    
    use App\Services\ItemService;
    
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Http\Resources\Item\WithDescendants\Extended as ItemExtendedWithDescendantsResource;

    class ItemChildrenController extends Controller
    {
        protected $itemService;
        
        protected $itemRepository;
        
        /**
         * Create a new controller instance.
         *
         * @param \App\Services\ItemService                            $itemService
         * @param \App\Repositories\Interfaces\MenuRepositoryInterface $menuRepository
         * @param \App\Repositories\Interfaces\ItemRepositoryInterface $itemRepository
         */
        public function __construct(ItemService $itemService, ItemRepositoryInterface $itemRepository)
        {
            $this->itemService = $itemService;
            
            $this->itemRepository = $itemRepository;
        }
        
        /**
         * Store a newly created resource in storage.
         *
         * @param                           $itemId
         * @param  \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\Response
         */
        public function store($itemId, Request $request)
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
            
            
            $menu = $this->itemRepository->menu($item);
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.could_not_retrieve_menu')]], 500);
            }
            
            
            /**
             * Check if provided data is valid for creating Items.
             */
            $itemDepth = $this->itemRepository->depth($item);
            
            $itemsDataValid = $this->itemService->isItemsCratingDataValid($menu, $request->all(), $itemDepth);
            
            if ($itemsDataValid !== true) {
                return response()->json($itemsDataValid['data'], $itemsDataValid['statusCode']);
            }
            
            
            /**
             * Create Items.
             */
            $items = $this->itemService->createItems($menu, $item, $request->all());
            
            if (!($items instanceof Collection)) {
                return response()->json($items['data'], $items['statusCode']);
            }
            
            
            return response()->json(ItemExtendedWithDescendantsResource::collection($items), 201);
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
            
            
            $items = $this->itemRepository->children($item);
            
            if (!$items) {
                return response()->json(['errors' => [__('item.could_not_retrieve_item_children')]], 500);
            }
            
            
            return response()->json(ItemExtendedWithDescendantsResource::collection($items), 200);
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
            
            
            $deleteItemDescendantsResponse = $this->itemRepository->deleteDescendants($item);
            
            if (!$deleteItemDescendantsResponse) {
                return response()->json(['errors' => [__('item.could_not_delete_item_descendants')]], 500);
            }
            
            
            return response()->noContent();
        }
    }
