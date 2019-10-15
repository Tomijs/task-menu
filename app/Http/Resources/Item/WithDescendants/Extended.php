<?php
    
    namespace App\Http\Resources\Item\WithDescendants;

    use Illuminate\Http\Resources\Json\JsonResource;
    
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Http\Resources\Item\Basic as ItemExtendedResource;
    use App\Http\Resources\Item\WithDescendants\Basic as ItemExtendedWithDescendantsResource;

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
            $resourceData = (new ItemExtendedResource($this->resource))->resolve();
            
            
            if (!isset($this->createdDescedants) || $this->createdDescedants->isEmpty()) {
                $descendants = $this->itemRepository->children($this->resource);
            } else {
                $descendants = $this->createdDescedants;
            }
            
            if (!$descendants->isEmpty()) {
                $resourceData['children'] = ItemExtendedWithDescendantsResource::collection($descendants);
            }
            
            
            return $resourceData;
        }
    }
