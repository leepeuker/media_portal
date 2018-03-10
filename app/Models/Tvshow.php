<?php

namespace Models;

use PDO;
use \Core\Config;

/**
 * Tvshow model
 *
 * PHP version 7.0
 */
class Tvshow extends \Core\Model
{

    /**
     * Get multiple tvshows
     * 
     * @param string $search Searchterm for query WHERE LIKE $search
     * @param int $offset Offset for query LIMIT
     * @param int $length Number of query results
     * @param string $orderCol Order column name
     * @param string $orderDir Order direction
     * @return array Queried tvshows
     */
    public static function getMany($search, $offset, $length, $orderColName, $orderDir)
    {
        $orderColName = self::convertPropertyName($orderColName);

        $sql = "SELECT idShow AS id, c00 AS title, c01 AS plot, c05, c08 AS genre, 
                       c14 AS studio, dateAdded, rating, url
                FROM tvshow_view
                INNER JOIN art
                ON tvshow_view.idShow = art.media_id
                WHERE (c00 LIKE :searchterm OR 
                       c05 LIKE :searchterm OR 
                       c14 LIKE :searchterm)
                      AND type = 'poster'
                      AND media_type = 'tvshow'
                ORDER BY $orderColName $orderDir
                LIMIT $offset, $length";
        $db = static::getDB(Config::get('DB_NAME_KODI'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':searchterm', self::whereLike($search));

        $stmt->execute();
        $tvshows = self::convert($stmt->fetchAll(PDO::FETCH_ASSOC));

        return (array) $tvshows;
    }

    /**
     * Convert the database output. Useless informations will be filterd out.
     * Missing informations will be calculated.
     * 
     * @param array $tvshows Array containing the database ouput
     * @return array The converted tvshow array
     */
    public static function convert($tvshows)
    {
        foreach ($tvshows as &$tvshow) {

            $tvshow['premiered'] = substr($tvshow['c05'], 0, 4);

            $tvshow['cover'] = self::getCover($tvshow['url']);

            $tvshow['added'] = substr($tvshow['dateAdded'], 0, 10);
        }

        return $tvshows;
    }

    /**
     * Check if cover exists localy, if not download it
     * 
     * @param string Cover url remote server
     * @return string Cover Local file name
     */
    private static function getCover($urlCover) 
    {
        $coverFileName = substr($urlCover, 39);
        $file = BASE_DIR . 'public/images/covers_tvshows/' . $coverFileName;

        if (!file_exists($file)) {

            copy($urlCover, $file);
        }

        return $coverFileName;
    }

    /**
     * Get count of tvshows matching the search
     * 
     * @param string $search Searchterm for tvshow
     * @return int Number of matching rows
     */
    public static function getCount($search = '') 
    {
        $sql = 'SELECT COUNT(idshow)
                FROM tvshow
                WHERE c00 LIKE :searchterm OR 
                      c05 LIKE :searchterm OR 
                      c14 LIKE :searchterm';
        $db = static::getDB(Config::get('DB_NAME_KODI'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':searchterm', self::whereLike($search));

        $stmt->execute();
        $countTvshows = $stmt->fetch();

        return $countTvshows["COUNT(idshow)"];
    }

    /**
     * Get episodes of tvshow
     * 
     * @param int ID of tvshow
     * @return array Queried episodes
     */
    public static function getEpisodes($tvshowID)
    {
        $sql = "SELECT idFile, episode.c00, episode.c13, season
                FROM episode
                INNER JOIN seasons ON episode.idSeason = seasons.idSeason
                INNER JOIN tvshow ON tvshow.idShow = seasons.idShow
                WHERE tvshow.idShow = :ID";
        $db = static::getDB(Config::get('DB_NAME_KODI'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':ID', $tvshowID);

        $stmt->execute();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if(empty($seasons[$row['season']])) {

                $seasons[$row['season']] = array();
            }

            $seasons[$row['season']][$row['c13']]['name'] = $row['c00'];
            $seasons[$row['season']][$row['c13']]['idFile'] = $row['idFile'];

        }

        return (array) $seasons;
    }

    /**
     * Get file path of episode of tvshow
     * 
     * @param int $id ID of episode of tvshow
     * @return string File path of episode
     */
    public static function getFileLocation($id) 
    {
        $sql = 'SELECT c18
                FROM episode
                WHERE episode.idFile = :id';
        $db = static::getDB(Config::get('DB_NAME_KODI'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id);

        $stmt->execute();

        if($fileInfos = $stmt->fetch()) {

            return '/mnt/tvshows/' . substr($fileInfos['c18'], 27);;
        }
    }

    /**
     * Convert property name to mysql column name
     *
     * @param string $name Name of property
     * @return string Name of mysql column
     */
    private static function convertPropertyName($name) 
    {
        switch ($name) {

            case 'title': return 'c00';

            case 'premiered': return 'c05';

            case 'added': return 'dateAdded';

            default: return $name;
        }
    }
}