<?php
    
    namespace App\Http\Resources\Menu;

    use Illuminate\Http\Resources\Json\JsonResource;

    class Basic extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param  \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request)
        {
            return [
                'id'           => $this->id,
                'name'         => $this->name,
                'max_depth'    => $this->max_depth,
                'max_children' => $this->max_children
            ];
        }
    }
