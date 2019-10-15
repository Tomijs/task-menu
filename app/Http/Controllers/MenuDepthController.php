<?php
    
    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\Validator;
    
    use App\Repositories\Interfaces\MenuRepositoryInterface;

    class MenuDepthController extends Controller
    {
        protected $menuRepository;
        
        /**
         * Create a new controller instance.
         *
         * @param \App\Repositories\Interfaces\MenuRepositoryInterface $menuRepository
         */
        public function __construct(MenuRepositoryInterface $menuRepository)
        {
            $this->menuRepository = $menuRepository;
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
            
            $depth = $this->menuRepository->depth($menu);
            
            if (is_null($depth)) {
                return response()->json(['errors' => [__('menu.could_not_retrieve_depth')]], 500);
            }
            
            return response()->json(['depth' => $depth], 200);
        }
    }
