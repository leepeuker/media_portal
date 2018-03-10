<?php

namespace Models;

use PDO;
use \Utilities\Token;
use \Utilities\Mail;
use \Utilities\Flash;
use \Core\Config;
use \Core\Model;
use \Core\View;
use \Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;

/**
 * User model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
{

    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];

    /**
     * Class constructor
     *
     * @param array $data Initial property values (optional)
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    /**
     * Save the user model with the current property values
     *
     * @return boolean True if the user was saved, false otherwise
     */
    public function save()
    {
        if ($this->validate()) {

            if (empty($this->password)) {
                $generator = new ComputerPasswordGenerator();
                $generator
                    ->setUppercase()
                    ->setLowercase()
                    ->setNumbers()
                    ->setSymbols(false)
                    ->setLength(12);

                $this->password = $generator->generatePassword();
            }
            
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();

            $sql = 'INSERT INTO users (email, password_hash, activation_hash)
                    VALUES (:email, :password_hash, :activation_hash)';
            $db = static::getDB(Config::get('DB_NAME_USER'));
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    /**
     * Validate current property values
     *
     * @return boolean True if no error occurred, false otherwise
     */
    public function validate()
    {
        $noErrors = true;

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            Flash::addMessage('Invalid email address.', Flash::ERROR);
            $noErrors = false;
        }

        if (static::emailExists($this->email, $this->id ?? null)) {
            Flash::addMessage('Email address already taken.', Flash::ERROR);
            $noErrors = false;
        }

        if(!$this->validatePassword()) {
            $noErrors = !$noErrors ?: false;
        }

        return $noErrors;
    }

    /**
     * Validate current property password
     *
     * @return boolean True if no error occurred, false otherwise
     */
    public function validatePassword()
    {
        $noErrors = true;

        // Password
        if (isset($this->password)) {

            if (strlen($this->password) < 8) {

                Flash::addMessage('Password has to be at least 8 characters.', Flash::ERROR);
                $noErrors = false;
            }
        }
        return $noErrors;
    }

    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     * @param string $ignore_id Return false anyway if the record found has this ID
     * @return boolean True if a record already exists with the specified email, false otherwise
     */
    public static function emailExists($email, $ignore_id = null)
    {
        $user = static::findByEmail($email);

        if ($user) {

            if ($user->id != $ignore_id) {
                
                return true;
            }
        }

        return false;
    }

    /**
     * Find a user model by email address
     *
     * @param string $email email address to search for
     * @return mixed User object if found, false otherwise
     */
    public static function findByEmail($email)
    {
        $sql = 'SELECT * 
                FROM users 
                WHERE email = :email';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Delete user
     *
     * @param string $id User ID
     * @return void
     */
    public static function delete($id)
    {
        $sql = 'DELETE FROM users 
                WHERE ID = :id';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Authenticate a user by email and password. User account has to be active.
     *
     * @param string $email email address
     * @param string $password password
     * @return mixed The user object or false if authentication fails
     */
    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);

        if ($user && $user->is_active) {

            if (password_verify($password, $user->password_hash)) {

                return $user;
            }

        }

        return false;
    }

    /**
     * Find a user model by ID
     *
     * @param string $id The user ID
     * @return mixed User object if found, false otherwise
     */
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Remember the login by inserting a new unique token into the remembered_logins table
     * for this user record
     *
     * @return boolean True if the login was remembered successfully, false otherwise
     */
    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();
        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions to the user specified
     *
     * @param string $email The email address
     * @return void
     */
    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);

        if ($user) {

            if ($user->startPasswordReset()) {

                $user->sendPasswordResetEmail();
            }

        } else {

            Flash::addMessage('Unkown email address.', Flash::ERROR);
            return false;
        }
    }

    /**
     * Start the password reset process by generating a new token and expiry
     *
     * @return void
     */
    protected function startPasswordReset()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->password_reset_token = $token->getValue();
        $expiry_timestamp = time() + 60 * 60 * 2;  // 2 hours from now

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions in an email to the user
     *
     * @return void
     */
    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

        $text = View::getTemplate('User/Password/email_reset.txt', ['url' => $url]);
        $html = View::getTemplate('User/Password/email_reset.html', ['url' => $url]);

        if(Mail::send($this->email, 'Password reset', $text, $html)) {

            Flash::addMessage('Password reset email was sent.');

        } else {

            Flash::addMessage('Email could not be sent, please try again.', Flash::ERROR);
        }
    }

    /**
     * Find a user model by password reset token and expiry
     *
     * @param string $token Password reset token sent to user
     * @return mixed User object if found and the token hasn't expired, null otherwise
     */
    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {

            // Check password reset token hasn't expired
            if (strtotime($user->password_reset_expires_at) > time()) {

                return $user;
            }
        }
    }

    /**
     * Reset the password
     *
     * @param string $password The new password
     * @return boolean True if the password was updated successfully, false otherwise
     */
    public function resetPassword($password)
    {
        $this->password = $password;

        if ($this->validatePassword()) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password_hash = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                    WHERE id = :id';
            $db = static::getDB(Config::get('DB_NAME_USER'));
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
                                          
            return $stmt->execute();
        }
        return false;
    }

    /**
     * Send an email to the user containing the activation link
     *
     * @return void
     */
    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        $text = View::getTemplate('User/Signup/email_activation.txt', [ 'url' => $url, "user" => $this ]);
        $html = View::getTemplate('User/Signup/email_activation.html', [ 'url' => $url, "user" => $this ]);

        Mail::send($this->email, 'Account activation', $text, $html);
    }

    /**
     * Activate the user account with the specified activation token
     *
     * @param string $value Activation token from the URL
     * @return void
     */
    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $sql = 'UPDATE users
                SET is_active = 1, activation_hash = null
                WHERE activation_hash = :hashed_token';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();
    }
    
    /**
     * Update the user profile
     *
     * @param array $data Data from the edit profile form
     * @return boolean True if the data was updated, false otherwise
     */
    public function updateProfile($data)
    {
        $this->email = $data['email'];

        // Only validate and update the password if a value provided
        if ($data['password'] != '') {
            $this->password = $data['password'];
        }

        if ($this->validate()) {

            $sql = 'UPDATE users
                    SET email = :email';

            // Add password if it's set
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";

            $db = static::getDB(Config::get('DB_NAME_USER'));
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            // Add password if it's set
            if (isset($this->password)) {

                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            }

            $stmt->execute();

            return $stmt->rowCount(); 
        }

        return false;
    }

    
    /**
     * Update the users last login
     *
     * @return void
     */
    public function updateLastLogin() {

        $timestamp = date("Y-m-d H:i:s");

        $sql = 'UPDATE users
                SET lastLogin = :timestamp
                WHERE id = :id';           
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':timestamp', $timestamp, PDO::PARAM_STR);

        $stmt->execute();

        $this->updateLoginCount();
    }

    /**
     * Increment the users login count
     *
     * @return void
     */
    private function updateLoginCount() {

        $sql = 'UPDATE users
                SET loginCount =
                    loginCount + 1
                WHERE id = :id';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Get all users
     *
     * @return mixed User objects if found, null otherwise
     */
    public static function getAll()
    {
        $sql = 'SELECT * FROM users';
        $db = static::getDB(Config::get('DB_NAME_USER'));
        $stmt = $db->prepare($sql);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
