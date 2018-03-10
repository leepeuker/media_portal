<?php

namespace Controllers;

use \Core\View;
use \Models\User;
use \Utilities\Auth;
use \Utilities\Flash;

/**
 * Logout controller
 *
 * PHP version 7.0
 */
class Logout extends Authenticated
{

    /**
     * Log out a user
     *
     * @return void
     */
    public function destroyAction()
    {
        Auth::logout();

        $this->redirect('/login/show-logout-message');
    }
}
