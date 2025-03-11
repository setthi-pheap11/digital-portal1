<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
        CRUD::column('image')->label('Image')->type('image');
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

        // Image upload field (stores in /storage/app/public/products)
        CRUD::field('image')->label('Product Image')->type('upload')
            ->upload(true)->disk('public')->prefix('products'); // Saves in /storage/app/public/products
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
