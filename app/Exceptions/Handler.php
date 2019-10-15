<?php
    
    namespace App\Exceptions;

    use Exception;
    use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
    use Illuminate\Database\Eloquent\ModelNotFoundException;

    class Handler extends ExceptionHandler
    {
        /**
         * Render an exception into an HTTP response.
         *
         * @param  \Illuminate\Http\Request $request
         * @param  \Exception               $exception
         *
         * @return \Illuminate\Http\Response
         */
        public function render($request, Exception $exception)
        {
            /**
             * Handle accessing unknown models
             */
            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'error' => __('model.entry_not_found', ['name' => str_replace('App\\', '', $exception->getModel())])
                ], 404);
            }
            
            return parent::render($request, $exception);
        }
    }
