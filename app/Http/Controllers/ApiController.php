<?php
    
    namespace App\Http\Controllers;

    class ApiController extends Controller
    {
        /**
         * Display error on accessing unregistered routes.
         *
         * @return \Illuminate\Http\Response
         */
        public function resourceNotFound()
        {
            return response()->json([
                'message' => __('route.resource_not_found')
            ], 404);
        }
    }
