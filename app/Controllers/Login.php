<?php

namespace Controllers;

use \Core\View;
use \Models\User;
use \Utilities\Auth;
use \Utilities\Flash;

/**
 * Login controller
 *
 * PHP version 7.0
 */
class Login extends \Core\Controller
{

    /**
     * Before filter - called before any action method.
     *
     * @return void
     */
    protected function before() 
    {
        $this->requireNoLogin();
    }
    
    /**
     * Show the login page
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('User/login.html');
    }

    /**
     * Log in a user
     *
     * @return void
     */
    public function createAction()
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);
        
        $remember_me = isset($_POST['remember_me']);

        if ($user) {

            Auth::login($user, $remember_me);

            $this->redirect('/movies/index');

        } else {

            Flash::addMessage('Wrong password or email!', Flash::ERROR);

            $this->redirect('/');
        }
    }

    /**
     * Show a "logged out" flash message and redirect to the homepage. Necessary to use the flash messages
     * as they use the session and at the end of the Logout method (destroyAction) the session is destroyed
     * so a new action needs to be called in order to use the session.
     *
     * @return void
     */
    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout successful, bye bye!', Flash::INFO);

        $this->redirect('/');
    }
}
