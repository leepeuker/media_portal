<?php

namespace Controllers;

use \Core\View;
use \Utilities\Flash;
use \Models\User;

/**
 * Password controller
 *
 * PHP version 7.0
 */
class Password extends \Core\Controller
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
     * Show the forgotten password page
     *
     * @return void
     */
    public function forgotAction()
    {
        View::renderTemplate('User/Password/forgot.html');
    }

    /**
     * Send the password reset link to the supplied email
     *
     * @return void
     */
    public function requestResetAction()
    {
        User::sendPasswordReset($_POST['email']);

        if(Flash::isErrorMsg()) {
            $this->redirect('/password/forgot');
        }

        $this->redirect('/');
    }

    /**
     * Show the reset password form
     *
     * @return void
     */
    public function resetAction()
    {
        $token = $this->request->params('token');

        $user = $this->getUserOrExit($token);

        View::renderTemplate('User/Password/reset.html', [
            'token' => $token
        ]);
    }

    /**
     * Reset the user's password
     *
     * @return void
     */
    public function resetPasswordAction()
    {
        $token = $_POST['token'];

        $user = $this->getUserOrExit($token);

        if ($user->resetPassword($_POST['password'])) {

            Flash::addMessage('New password succesfully set.');
            $this->redirect('/');
        
        } else {

            View::renderTemplate('User/Password/reset.html', [
                'token' => $token,
                'user' => $user
            ]);
        }
    }

    /**
     * Find the user model associated with the password reset token, or end the request with a message
     *
     * @param string $token Password reset token sent to user
     * @return mixed User object if found and the token hasn't expired, null otherwise
     */
    protected function getUserOrExit($token)
    {
        $user = User::findByPasswordReset($token);

        if ($user) {

            return $user;

        } else {

            Flash::addMessage('Password reset link invalid or expired. <a href="/password/forgot">Request new one.</a>', Flash::ERROR);
            View::renderTemplate('User/Password/reset.html', [
                'token' => $token
            ]);
            exit;

        }
    }
}
