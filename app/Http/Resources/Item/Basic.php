<?php
    
    namespace App\Http\Resources\Item;

    use Illuminate\Http\Resources\Json\JsonResource;
    
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    
    use App\Http\Resources\Item\Basic as ItemResource;

    class Basic extends JsonResource
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
            $resourceData = [
                'id'   => $this->id,
                'name' => $this->name
            ];
            
            return $resourceData;
        }
    }
