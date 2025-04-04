<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('user', 'users');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('name')->type('text')->label('Full Name');
        CRUD::column('email')->type('email')->label('Email');
        CRUD::column('created_at')->type('datetime')->label('Registered At');
        CRUD::column('updated_at')->type('datetime')->label('Updated At');
        CRUD::column('role')->type('select_from_array')->label('Role')
            ->options([
                'admin' => 'Admin',
                'user' => 'User',
                // Add other roles as needed
            ]);
        CRUD::column('google_id')->type('text')->label('Google ID');
        CRUD::column('card_number')->type('text')->label('Card Number');
        CRUD::removeColumn('password'); // Hide password from the list
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(UserRequest::class);

        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => 'Full Name'
        ]);

        CRUD::addField([
            'name' => 'email',
            'type' => 'email',
            'label' => 'Email'
        ]);

        CRUD::addField([
            'name' => 'password',
            'type' => 'password',
            'label' => 'Password'
        ]);
        CRUD::addField([
            'name' => 'google_id',
            'type' => 'text',
            'label' => 'Google ID'
        ]);
        // Add the role field
        CRUD::addField([
            'name' => 'role',
            'type' => 'select_from_array',
            'label' => 'Role',
            'options' => [
                'admin' => 'Admin',
                'user' => 'User',
                // Add other roles as needed
            ],
            'allows_null' => false,
            'default' => 'user', // Set default role to user
        ]);
    
        CRUD::addField([
            'name' => 'card_number',
            'type' => 'text',
            'label' => 'Card Number'
        ]);
    }
    
  

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @return void
     */
    protected function setupUpdateOperation()
    {
        CRUD::setValidation(UserRequest::class);

        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => 'Full Name'
        ]);

        CRUD::addField([
            'name' => 'email',
            'type' => 'email',
            'label' => 'Email'
        ]);

        CRUD::addField([
            'name' => 'password',
            'type' => 'password',
            'label' => 'New Password (Leave blank to keep current password)',
            'attributes' => [
                'autocomplete' => 'new-password'
            ]
        ]);
        CRUD::addField([
            'name' => 'google_id',
            'type' => 'text',
            'label' => 'Google ID'
        ]);
        // Add the role field
        CRUD::addField([
            'name' => 'role',
            'type' => 'select_from_array',
            'label' => 'Role',
            'options' => [
                'admin' => 'Admin',
                'user' => 'User',
                // Add other roles as needed
            ],
            'allows_null' => false,
        ]);
        CRUD::addField([
            'name' => 'card_number',
            'type' => 'text',
            'label' => 'Card Number'
        ]);
      
    }

    /**
     * Hash password before storing user.
     */
    public function store()
    {
        $request = $this->crud->validateRequest();

        if ($request->filled('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        return $this->traitStore();
    }

    /**
     * Hash password before updating user.
     */
    public function update()
    {
        $request = $this->crud->validateRequest();

        if ($request->filled('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        } else {
            $request->request->remove('password');
        }

        return $this->traitUpdate();
    }
}
