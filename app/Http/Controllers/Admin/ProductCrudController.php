<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Storage;


/**
 * Class ProductCrudController
 */
class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product');
        CRUD::setEntityNameStrings('product', 'products');
    }

    protected function setupListOperation()
    {
        CRUD::column('product_name')->label('Product Name')->type('text');
        CRUD::column('priceUSD')->label('Price (USD)')->type('number');
        CRUD::column('category_id')->label('Category')->type('select')
            ->entity('category')->attribute('name');
        CRUD::column('seller_id')->label('Seller')->type('select')
            ->entity('seller')->attribute('name');
      

        CRUD::column('image')->label('Image')
        ->type('custom_html') 
        ->escaped(false) 
        ->value(function ($entry) {
            if ($entry->image) {
                
                $imageUrl = Storage::disk('s3')->url("digital/{$entry->image}");
                return "<img src='{$imageUrl}' width='100' height='100' style='border-radius:5px;'/>";
            }
            return '-';
        });
    
    
        

        CRUD::column('created_at')->label('Created At')->type('datetime');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductRequest::class);

        CRUD::field('product_name')->label('Product Name')->type('text');
        CRUD::field('product_detail')->label('Product Detail')->type('textarea');
        CRUD::field('priceUSD')->label('Price (USD)')->type('number')->attributes(['step' => '0.01']);
        
        CRUD::field('category_id')->label('Category')->type('select')
            ->entity('category')->model('App\Models\Category')->attribute('name');

        CRUD::field('seller_id')->label('Seller')->type('select')
            ->entity('seller')->model('App\Models\User')->attribute('name');

            CRUD::field('image')->label('Product Image')->type('upload')
            ->disk('s3') 
            ->upload(true)
            ->withFiles([
                'disk' => 's3', 
                'path' => 'products',
                'visibility' => 'public'
            ]);
        
        
      
    

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
