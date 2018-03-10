<?php

namespace Controllers;

use \Core\View;
use \Models\Comic;

/**
 * Comics controller
 *
 * PHP version 7.0
 */
class Comics extends Authenticated
{
    
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Comics/index.html');
    }

    /**
     * Send JSON formated datatables AJAX response for the comics table
     *
     * @return void
     */
    public function showAction()
    {
        $this->convertDatatablesRequest();

        $comics = Comic::getMany(
            $this->datatablesReq['search'],
            $this->datatablesReq['start'],
            $this->datatablesReq['length'],
            $this->datatablesReq['orderColName'],
            $this->datatablesReq['orderColDirection']
        );

        $comicsJSON = array(
            'draw' => $this->datatablesReq['draw'], 
            'recordsTotal' => Comic::getCount(), 
            'recordsFiltered' => Comic::getCount($this->datatablesReq['search']), 
            'data' => (array) $comics
        );

        header('Content-Type: application/json');
        echo json_encode($comicsJSON);
    }

    /**
     * Add a new comic
     *
     * @return void
     */
    public function newAction()
    {
        Comic::new($_POST);
        
        $this->redirect('/admin');
    }

    /**
     * Delete a comic
     *
     * @return void
     */
    public function deleteAction()
    {
        Comic::delete($_POST['id']);
        
        $this->redirect('/comics');
    }
}