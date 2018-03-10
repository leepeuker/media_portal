<?php

namespace Models;

use PDO;
use \Core\Config;

/**
 * Comic model
 *
 * PHP version 7.0
 */
class Comic extends \Core\Model
{

    /**
     * Get multiple comics
     * 
     * @param string $search Searchterm for query WHERE LIKE
     * @param int $offset Offset for query LIMIT
     * @param int $length Number of query results
     * @param string $orderCol Order column name
     * @param string $orderDir Order direction
     *
     * @return array Queried comics metadata
     */
    public static function getMany($search, $offset, $length, $orderCol, $orderDir)
    {
        $sql = "SELECT *
                FROM comics_physical
                WHERE title LIKE :searchterm OR year LIKE :searchterm OR publisher LIKE :searchterm
                ORDER BY $orderCol $orderDir
                LIMIT $offset, $length";
        $db = static::getDB(Config::get('DB_NAME_MEDIA'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':searchterm', self::whereLike($search));

        $stmt->execute();
        $comics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $comics;
    }

    /**
     * Get count of comics matching the search
     * 
     * @param string $search Searchterm for query WHERE LIKE
     * @return int Number of matching rows
     */
    public static function getCount($search = '') 
    {

        $sql = 'SELECT COUNT(ID)
                FROM comics_physical
                WHERE (title LIKE :searchterm OR 
                    year LIKE :searchterm OR 
                    publisher LIKE :searchterm)';
        $db = static::getDB(Config::get('DB_NAME_MEDIA'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':searchterm', self::whereLike($search));

        $stmt->execute();
        $countMovies = $stmt->fetch();

        return $countMovies["COUNT(ID)"];
    }

    /**
     * Create new comic
     * 
     * @param array Post form data
     * @return void
     */
    public static function new($comicData) 
    {

        $comicCoverDir = BASE_DIR . 'public/images/covers_comics/';
        $comicCoverFileName = basename($_POST['coverFileName']) . '.' . pathinfo($_FILES['fileCover']['name'], PATHINFO_EXTENSION);

        $sql = 'INSERT INTO comics_physical (cover, title, description, year, price)
                VALUES (:cover, :name, :description, :year, :price)';
        $db = static::getDB(Config::get('DB_NAME_MEDIA'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':cover', $comicCoverFileName, PDO::PARAM_STR);
        $stmt->bindValue(':name', $comicData['title'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $comicData['plot'], PDO::PARAM_STR);
        $stmt->bindValue(':year', $comicData['year']);
        $stmt->bindValue(':price', $comicData['price']);

        $stmt->execute();

        $comicCoverFile = $comicCoverDir . $comicCoverFileName;
        move_uploaded_file($_FILES['fileCover']['tmp_name'], $comicCoverFile);
    }

    /**
     * Delete a comic
     * 
     * @param int $id Comic id
     * @return void
     */
    public static function delete($id) 
    {
        $sql = 'DELETE FROM comics_physical 
                WHERE id = :id';
        $db = static::getDB(Config::get('DB_NAME_MEDIA'));
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
    }
}