<?php

namespace Controllers;

use \Core\View;
use \Models\Tvshow;

/**
 * Tvshows controller
 *
 * PHP version 7.0
 */
class Tvshows extends Authenticated
{
    
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Tvshows/index.html');
    }  

    /**
     * Show tvshow list
     *
     * @return void
     */
    public function showAction()
    {
        $this->convertDatatablesRequest();

        $tvshows = Tvshow::getMany(
            $this->datatablesReq['search'],
            $this->datatablesReq['start'],
            $this->datatablesReq['length'],
            $this->datatablesReq['orderColName'],
            $this->datatablesReq['orderColDirection']
        );

        $tvshowJSON = array(
            'draw' => $this->datatablesReq['draw'], 
            'recordsTotal' => Tvshow::getCount(), 
            'recordsFiltered' => Tvshow::getCount($this->datatablesReq['search']), 
            'data' => (array) $tvshows
        );

        header('Content-Type: application/json');
        echo json_encode($tvshowJSON);
    }

    /**
     * Show tvshow list
     *
     * @return void
     */
    public function episodesAction()
    {
        $seasons = Tvshow::getEpisodes($this->request->params('id'));

        View::renderTemplate('Tvshows/episodes.html', [
            'seasons' => $seasons
        ]);
    }

    /**
     * Download movie
     *
     * @return void
     */
    public function downloadAction()
    {
        $fileLocation = Tvshow::getFileLocation($this->request->params('id'));
        
        $this->startDownload($fileLocation);
    }
}
