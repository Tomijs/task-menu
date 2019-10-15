<?php
    
    namespace Tests\Feature;

    use Tests\TestCase;
    use Illuminate\Foundation\Testing\WithFaker;
    use Illuminate\Foundation\Testing\RefreshDatabase;

    class MenuTest extends TestCase
    {
        /**
         * A basic feature test example.
         *
         * @return void
         */
        public function testView()
        {
            $response = $this->get('/api/menus/1');
            
            $response->assertStatus(200);
        }
    }
