<?php

namespace Models;

use PDO;
use \Core\Config;

/**
 * Movie model
 *
 * PHP version 7.0
 */
class Movie extends \Core\Model
{

    /**
     * Get multiple movies
     * 
     * @param string $search Searchterm for query WHERE LIKE $search
     * @param int $offset Offset for query LIMIT
     * @param int $length Number of query results
     * @param string $orderCol Order column name
     * @param string $orderDir Order direction
     * @return array Queried movies
     */
    public static function getMany($search, $offset, $length, $orderColName, $orderColDir)
    {
        $orderColName = self::convertPropertyName($orderColName);

        $sql = "SELECT idFile AS id, c00 AS title, c01 AS plot, c11 AS runtime, c14 AS genre, 
                       c15 AS director, c18 AS studio, c19 AS trailer, c22 AS path, 
                       premiered, strFilename, dateAdded, rating, url
                FROM movie_view
                INNER JOIN art
                ON movie_view.idMovie = art.media_id
                WHERE (c00 LIKE :searchterm OR 
                       c14 LIKE :searchterm OR 
                       c15 LIKE :searchterm OR 
                       c18 LIKE :searchterm OR 
                       premiered LIKE :searchterm)
                       AND type = 'poster'
                ORDER BY $orderColName $orderColDir
                LIMIT $offset, $length";
        $db = static::getDB(Config::get('DB_NAME_KODI'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':searchterm', self::whereLike($search));

        $stmt->execute();
        $movies = self::convert($stmt->fetchAll(PDO::FETCH_ASSOC));

        return (array) $movies;
    }

    /**
     * Convert the database output. Useless informations will be filterd out.
     * Missing informations will be calculated.
     * 
     * @param array $movies Array containing the database ouput
     * @return array The converted movie array
     */
    public static function convert($movies)
    {
        foreach ($movies as &$movie) {

            $movie['cover'] = self::getCover($movie['url']);

            $movie['premiered'] = substr($movie['premiered'], 0, 4);

            $movie['runtime'] = self::formatRuntime($movie['runtime']);

            $movie['trailer'] = substr($movie['trailer'], 57);
        
            $movie['dateAdded'] = substr($movie['dateAdded'], 0, 10);

            $movie['fileSize'] = self::calcFileSize($movie['path'], $movie['strFileName']) ?? '';
        }

        return $movies;
    }

    /**
     * Check if cover exists localy, if not download it.
     * 
     * @param string $urlCover Cover url from image.tmdb.org
     * @return string Cover file name
     */
    private static function getCover($urlCover) 
    {
        $coverFileName = substr($urlCover, 35);

        $file = BASE_DIR . 'public/images/covers_movies/' . $coverFileName;

        if (!file_exists($file)) {

            copy($urlCover, $file);
        }

        return $coverFileName;
    }

    /**
     * Format the movie runtime
     * 
     * @param string Movie runtime in seconds
     * @return string Runtime of movie in minutes
     */
    public static function formatRuntime($runtimeSeconds) 
    {
        return ctype_digit($runtimeSeconds) ? intval($runtimeSeconds / 60) : '?';
    }

    /**
     * Get the size of the movie file
     * 
     * @param string $smbPath Sambapath of movie file on NAS
     * @param string $fileName Name of the movie file
     * @return string Size of movie file in GB
     */
    public static function calcFileSize($smbPath, $fileName) 
    {
        $dirAndFile = substr($smbPath, 25);
        $fullPath = '/mnt/movies/' . $dirAndFile . $fileName;
        $fileSize = trim(shell_exec('stat -c %s '. escapeshellarg($fullPath)));
        $fileSizeGB = number_format($fileSize / 1073741824, 2);

        return $fileSizeGB;
    }

    /**
     * Get count of movies matching the search
     * 
     * @param string $search Searchterm for movies
     * @return int Number of matching rows
     */
    public static function getCount($search = '') 
    {
        $sql = 'SELECT COUNT(idMovie)
                FROM movie_view
                INNER JOIN art
                ON movie_view.idMovie = art.media_id
                WHERE (c00 LIKE :searchterm OR 
                       c14 LIKE :searchterm OR 
                       c15 LIKE :searchterm OR 
                       c18 LIKE :searchterm OR 
                       premiered LIKE :searchterm)
                    AND type = "poster"';
        $db = static::getDB(Config::get('DB_NAME_KODI'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':searchterm', self::whereLike($search));

        $stmt->execute();
        $countMovies = $stmt->fetch();

        return $countMovies["COUNT(idMovie)"];
    }

    /**
     * Get file path of movie
     * 
     * @param int $search Searchterm for movies
     * @return string File path of movie 
     */
    public static function getFileLocation($id) 
    {
        $sql = 'SELECT c22, strFilename
                FROM movie
                INNER JOIN files ON files.idFile = movie.idFile
                WHERE movie.idFile = :id';
        $db = static::getDB(Config::get('DB_NAME_KODI'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id);

        $stmt->execute();

        if($fileInfos = $stmt->fetch()) {

            return '/mnt/movies/' . substr($fileInfos['c22'], 25) . $fileInfos['strFilename'];
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

            default: return $name;
        }
    }
}