<?php
    
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Menu extends Model
    {
        const MAX_NAME_LENGTH  = 30;
        const MIN_MAX_DEPTH    = 0;
        const MIN_MAX_CHILDREN = 0;
        
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name',
            'max_depth',
            'max_children'
        ];
        
        /**
         * Get the Item records associated with the Menu.
         */
        public function items()
        {
            return $this->hasMany('App\Models\Item');
        }
    }
