<?php

namespace Controllers;

use \Core\View;
use \Models\Movie;

/**
 * Movies controller
 *
 * PHP version 7.0
 */
class Movies extends Authenticated
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Movies/index.html');
    }   

    /**
     * Show movie list
     *
     * @return void
     */
    public function showAction()
    {
        $this->convertDatatablesRequest();

        $movies = Movie::getMany(
            $this->datatablesReq['search'],
            $this->datatablesReq['start'],
            $this->datatablesReq['length'],
            $this->datatablesReq['orderColName'],
            $this->datatablesReq['orderColDirection']
        );

        $moviesJSON = array(
            'draw' => $this->datatablesReq['draw'], 
            'recordsTotal' => Movie::getCount(), 
            'recordsFiltered' => Movie::getCount($this->datatablesReq['search']), 
            'data' => (array) $movies
        );

        header('Content-Type: application/json');
        echo json_encode($moviesJSON);
    }

    /**
     * Download movie
     *
     * @return void
     */
    public function downloadAction()
    {
        $fileLocation = Movie::getFileLocation($this->request->params('id'));

        $this->startDownload($fileLocation);
    }
}
