<?php
    
    namespace App\Http\Resources\Item;

    use Illuminate\Http\Resources\Json\JsonResource;
    
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Http\Resources\Item\Basic as ItemResource;

    class Extended extends JsonResource
    {
        protected $itemRepository;
        
        /**
         * Create a new resource instance.
         *
         * @param $resource
         */
        public function __construct($resource)
        {
            parent::__construct($resource);
            
            $this->itemRepository = app(ItemRepositoryInterface::class);
        }
        
        /**
         * Transform the resource into an array.
         *
         * @param  \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request)
        {
            $resourceData = (new ItemResource($this->resource))->resolve();
            
            
            $itemParent = $this->itemRepository->parent($this->resource);
            
            $resourceData['menu_id'] = $this->itemRepository->menu($this->resource)->id;
            $resourceData['parent_item_id'] = (isset($itemParent) ? $itemParent->id : null);
            
            
            return $resourceData;
        }
    }
