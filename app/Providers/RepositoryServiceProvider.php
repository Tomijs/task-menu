<?php
    
    namespace App\Providers;

    use Illuminate\Support\ServiceProvider;
    
    use App\Repositories\Interfaces\MenuRepositoryInterface;
    use App\Repositories\MenuRepository;
    
    use App\Repositories\Interfaces\ItemRepositoryInterface;
    use App\Repositories\ItemRepository;

    class RepositoryServiceProvider extends ServiceProvider
    {
        /**
         * Register services.
         *
         * @return void
         */
        public function register()
        {
            $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
            $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        }
        
        /**
         * Bootstrap services.
         *
         * @return void
         */
        public function boot()
        {
            //
        }
    }
