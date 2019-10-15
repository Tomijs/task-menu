<?php
    
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Kalnoy\Nestedset\NodeTrait;

    class Item extends Model
    {
        use NodeTrait;
        
        const MAX_NAME_LENGTH = 30;
        
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name',
            'menu_id'
        ];
        
        protected function getScopeAttributes()
        {
            return ['menu_id'];
        }
        
        /**
         * Get the Menu record associated with the Item.
         */
        public function menu()
        {
            return $this->belongsTo('App\Models\Menu');
        }
    }
