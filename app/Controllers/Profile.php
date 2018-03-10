<?php

namespace Controllers;

use \Core\View;
use \Utilities\Auth;
use \Utilities\Flash;

/**
 * Profile controller
 *
 * PHP version 7.0
 */
class Profile extends Authenticated
{

    /**
     * Before filter - called before each action method
     *
     * @return void
     */
    protected function before()
    {
        parent::before();

        $this->user = Auth::getUser();
    }

    /**
     * Show the profile
     *
     * @return void
     */
    public function showAction()
    {
        View::renderTemplate('User/Profile/edit.html', [
            'user' => $this->user
        ]);
    }

    /**
     * Update the profile
     *
     * @return void
     */
    public function updateAction()
    {
        if ($this->user->updateProfile($_POST) === 1) {

            Flash::addMessage('Changes saved');

        } elseif(!Flash::isErrorMsg()) {

            Flash::addMessage('Nothing changed', 'info');
        }

        $this->redirect('/profile'); 
    }
}
