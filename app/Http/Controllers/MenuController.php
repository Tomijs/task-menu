<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Facades\Cache;
    
    use App\Repositories\Interfaces\MenuRepositoryInterface;
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Http\Resources\Menu\Basic as MenuResource;
    
    use App\Models\Menu;

    class MenuController extends Controller
    {
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
                'name',
                'max_depth',
                'max_children'
            ]);
            
            $createMenuValidator = Validator::make($validFields, [
                'name'         => [
                    'required',
                    'string',
                    'max:' . Menu::MAX_NAME_LENGTH
                ],
                'max_depth'    => [
                    'nullable',
                    'integer',
                    'min:' . Menu::MIN_MAX_DEPTH
                ],
                'max_children' => [
                    'nullable',
                    'integer',
                    'min:' . Menu::MIN_MAX_CHILDREN
                ]
            ]);
            
            if ($createMenuValidator->fails()) {
                return response()->json(['errors' => $createMenuValidator->errors()], 400);
            }
            
            
            $menu = $this->menuRepository->create($validFields);
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.could_not_save')]], 500);
            }
            
            Cache::tags(['menus'])
                 ->put($menu->id, $menu);
            
            
            return response()->json(new MenuResource($menu), 201);
        }
        
        /**
         * Display the specified resource.
         *
         * @param mixed $menuId
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
                    'min:1'
                ]
            ]);
            
            if ($getMenuValidator->fails()) {
                return response()->json(['errors' => $getMenuValidator->errors()], 400);
            }
            
            
            if (Cache::tags(['menus'])
                     ->has($menuId)) {
                $menu = Cache::tags(['menus'])
                             ->get($menuId);
            } else {
                $menu = $this->menuRepository->get($menuId);
                
                if ($menu) {
                    Cache::tags(['menus'])
                         ->put($menu->id, $menu);
                }
            }
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.does_not_exist')]], 400);
            }
            
            
            return response()->json(new MenuResource($menu), 200);
        }
        
        /**
         * Update the specified resource in storage.
         *
         * @param  \Illuminate\Http\Request $request
         * @param                           $menuId
         *
         * @return \Illuminate\Http\Response
         */
        public function update($menuId, Request $request)
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
                    'min:1'
                ]
            ]);
            
            if ($getMenuValidator->fails()) {
                return response()->json(['errors' => $getMenuValidator->errors()], 400);
            }
            
            
            if (Cache::tags(['menus'])
                     ->has($menuId)) {
                $menu = Cache::tags(['menus'])
                             ->get($menuId);
            } else {
                $menu = $this->menuRepository->get($menuId);
                
                if ($menu) {
                    Cache::tags(['menus'])
                         ->put($menu->id, $menu);
                }
            }
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.does_not_exist')]], 400);
            }
            
            
            $validFields = $request->only([
                'name',
                'max_depth',
                'max_children'
            ]);
            
            $menuDepth = $this->menuRepository->depth($menu);
            $mostChildrenCountInAnyMenuLayer = $this->menuRepository->mostChildrenCountInAnyLayer($menu);
            
            $updateMenuValidator = Validator::make($validFields, [
                'name'         => [
                    'required',
                    'string',
                    'max:' . Menu::MAX_NAME_LENGTH
                ],
                'max_depth'    => [
                    'nullable',
                    'integer',
                    'min:' . $menuDepth
                ],
                'max_children' => [
                    'nullable',
                    'integer',
                    'min:' . $mostChildrenCountInAnyMenuLayer
                ]
            ]);
            
            if ($updateMenuValidator->fails()) {
                return response()->json(['errors' => $updateMenuValidator->errors()], 400);
            }
            
            
            $updateMenuResponse = $this->menuRepository->update($menu, $validFields);
            
            if (!$updateMenuResponse) {
                return response()->json(['errors' => [__('menu.could_not_update')]], 500);
            }
            
            Cache::tags(['menus'])
                 ->put($menu->id, $menu);
            
            
            return response()->json(new MenuResource($menu->fresh()), 201);
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
                    'min:1'
                ]
            ]);
            
            if ($getMenuValidator->fails()) {
                return response()->json(['errors' => $getMenuValidator->errors()], 400);
            }
            
            
            if (Cache::tags(['menus'])
                     ->has($menuId)) {
                $menu = Cache::tags(['menus'])
                             ->get($menuId);
            } else {
                $menu = $this->menuRepository->get($menuId);
                
                if ($menu) {
                    Cache::tags(['menus'])
                         ->put($menu->id, $menu);
                }
            }
            
            if (!$menu) {
                return response()->json(['errors' => [__('menu.does_not_exist')]], 400);
            }
            
            
            $deleteItemsResponse = $this->itemRepository->deleteByMenu($menu);
            
            if (!$deleteItemsResponse) {
                return response()->json(['errors' => [__('item.could_not_delete_menu_items')]], 500);
            }
            
            
            $deleteMenuResponse = $this->menuRepository->delete($menu);
            
            if (!$deleteMenuResponse) {
                return response()->json(['errors' => [__('menu.could_not_delete')]], 500);
            }
            
            Cache::tags(['menus'])
                 ->forget($menuId);
            
            
            return response()->noContent();
        }
    }
