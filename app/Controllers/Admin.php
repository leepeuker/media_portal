<?php

namespace Controllers;

use \Core\View;
use \Models\User;
use \Utilities\Log;

/**
 * Login controller
 *
 * PHP version 7.0
 */
class Admin extends Authenticated
{

    /**
     * Before filter - called before any action method.
     *
     * @return void
     */
    protected function before()
    {
        parent::before();

        if($this->user->email != 'lee.peuker@gmail.com') {

            View::renderTemplate("Error/404.html");
            exit;
        }
    }
    
    /**
     * Show the login page
     *
     * @return void
     */
    public function indexAction()
    {
        $users = User::getAll();

        View::renderTemplate('Admin/index.html', [
            'users' => $users,
            'downloads' => Log::getDownload()
        ]);
    }

    /**
     * Delete user profile
     *
     * @return void
     */
    public function deleteUserAction()
    {
        User::delete($this->request->params('id'));

        $this->redirect('/admin');
    }
}
