<?php

namespace Core;

use \Utilities\Auth;
use \Utilities\Flash;
use \Utilities\Log;

/**
 * Base controller
 *
 * PHP version 7.0
 */
abstract class Controller
{

    /**
     * Parameters from the matched route
     * @var Request
     */
    protected $request = null;

    /**
     * Parameters from the datatable request
     * @var array
     */
    protected $datatablesReq = [];

    /**
     * Class constructor
     * 
     * @param Request $request Request object
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Used to execute before and after methods on action method. 
     * Action methods need to be named with an "Action" suffix.
     *
     * @param string $name Method name
     * @param array  $args Arguments passed to the method
     * @return void
     */
    public function __call($name, $args)
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {

            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    /**
     * Before filter - called before any action method.
     *
     * @return void
     */
    protected function before() {}

    /**
     * After filter - called after any action method.
     *
     * @return void
     */
    protected function after() {}

    /**
     * Redirect to a different page
     *
     * @access public
     * @param string $url  The relative URL
     * @return void
     */
    public function redirect($url)
    {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303);
        exit;
    }

    /**
     * Require the user to be logged in before giving access to the requested page.
     *
     * @access public
     * @return void
     */
    public function requireLogin()
    {
        if (!$this->user = Auth::getUser()) {

            Flash::addMessage('Please login to access that page.', Flash::WARNING);

            $this->redirect('/');
        }
    }

    /**
     * Require the user not to be logged in before giving access to the requested page.
     *
     * @access public
     * @return void
     */
    public function requireNoLogin()
    {
        if (Auth::getUser()) {

            $this->redirect('/movies');
        }
    }

    /**
     * Start a download
     * 
     * @access protected
     * @param string $file File location 
     * @param string $contentType MIME type of file
     * @return void
     */
    protected function startDownload($file)
    {			
        set_time_limit(0);
        $mimeType = $this->getMimeType($file);

        if (file_exists($file)) {

            Log::download($file);

            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . trim(shell_exec('stat -c %s '.escapeshellarg($file))));
            
            $fileOpend = fopen($file,"rb");

            while(!feof($fileOpend)) {

                session_write_close();
                echo fread($fileOpend, 1024*8);
                flush();
            }

        } else {

            View::renderTemplate('Error/downloadError.html');
        }
    }

    /**
     * Get mime type
     * 
     * @access protected
     * @param string $file File location
     * @return string Mime type of file
     */
    protected function getMimeType($file) 
    {
        $info = new \SplFileInfo($file);

        switch($info->getExtension()) {

            case 'mp4':
                $mimeType = 'video/mp4';
            break;

            case 'm4v':
                $mimeType = 'video/x-m4';
            break;

            case 'mkv':
                $mimeType = 'video/x-matroska';
            break;

            case 'avi':
                $mimeType = 'video/x-msvideo';
            break;

            default:
                $mimeType = 'video';
            break;
        }

        return $mimeType;
    }

    /**
     * Get mime type
     * 
     * @access protected
     * @param string $file File location 
     * @return string Mime type of file
     */
    protected function convertDatatablesRequest() 
    {
        $this->datatablesReq['orderColNumber'] = $_POST['order'][0]['column'];

        $this->datatablesReq['orderColDirection'] = $_POST['order'][0]['dir'];

        $this->datatablesReq['orderColName'] = $_POST['columns'][$this->datatablesReq['orderColNumber']]['data'];

        $this->datatablesReq['search'] = $_POST['search']['value'];

        $this->datatablesReq['length'] = $_POST['length'];
        
        $this->datatablesReq['start'] = $_POST['start'];

        $this->datatablesReq['draw'] = $_POST['draw'];
    }
}
