<?php
namespace Binarika;

class LibManager extends \Database_Mysql
{
    private $db;
    private $prefix = '';
    private $pagination = false;
    
    function __construct($dbOpt) {
        $this->db = parent::create($dbOpt['host'], $dbOpt['user'], $dbOpt['pass'])
              // Выбор базы данных
              ->setDatabaseName($dbOpt['database'])
              // Выбор кодировки
              ->setCharset($dbOpt['charset']);
        
        if(isset($dbOpt['prefix'])){$this->prefix = $dbOpt['prefix'];}
        if(isset($dbOpt['pagination'])){
            $this->pagination = $dbOpt['pagination'];
        }else{echo 'suka';}
echo "<pre>";
var_dump($dbOpt);
echo "</pre>";
    }
    
    
    private function returnArray($res){
        while ($data = $res->fetch_assoc()) {
            $ret[]=$data;
        }
        return $ret;
    }

    private function getRequest(){
        
        return true;
    }
    
    public function getAutors(){
        $res = $this->db->query("SELECT * FROM {$this->prefix}authors");
        $ret = array();
        while ($data = $res->fetch_assoc()) {
            $ret[]=$data;
        }
        return $ret;
    }
    
    public function getBooks($page=1,$numPage=10,$order='ASC'){
        $start = $numPage*($page-1);
        $pageString = "LIMIT {$start},{$numPage}";
        $res = $this->db->query("
            SELECT
                {$this->prefix}books.name AS book,
                GROUP_CONCAT({$this->prefix}authors.fullName) AS author
            FROM {$this->prefix}books
            JOIN {$this->prefix}authorship ON {$this->prefix}books.pid = {$this->prefix}authorship.bookID
            JOIN {$this->prefix}authors ON {$this->prefix}authors.pid = {$this->prefix}authorship.authorID
            GROUP BY {$this->prefix}books.pid
            ORDER BY {$this->prefix}books.name {$order}
            {$pageString}
        ");
//            echo $res;
        return $this->returnArray($res);
    }
    
//    public function order($name){
//        
//    }

    public static function createDB($opts){
        $db = parent::create($opts['host'], $opts['user'], $opts['pass'])
              // Выбор базы данных
              ->setDatabaseName($opts['database'])
              // Выбор кодировки
              ->setCharset($opts['charset']);

        $query = "
            CREATE TABLE IF NOT EXISTS `{$opts['prefix']}authorship` (
              `bookID` int(11) NOT NULL,
              `authorID` int(11) NOT NULL,
              KEY `bookID` (`bookID`),
              KEY `authorID` (`authorID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        $db->query($query);

        $query = "
            CREATE TABLE IF NOT EXISTS `{$opts['prefix']}authors` (
              `pid` int(11) NOT NULL AUTO_INCREMENT,
              `fullName` varchar(400) NOT NULL,
              PRIMARY KEY (`pid`),
              UNIQUE KEY `fullName` (`fullName`(255))
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ";
        $db->query($query);

        $query = "
            CREATE TABLE IF NOT EXISTS `{$opts['prefix']}books` (
              `pid` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(200) NOT NULL,
              PRIMARY KEY (`pid`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;            
        ";
        $db->query($query);
          
        return true;
    }
    
    public static function insertData($opts, $data){
        $db = parent::create($opts['host'], $opts['user'], $opts['pass'])
              // Выбор базы данных
              ->setDatabaseName($opts['database'])
              // Выбор кодировки
              ->setCharset($opts['charset']);
        
        self::createDB($opts);

//        $aid = $db->query('SELECT `pid` from `tst_authors` where `fullName`="Лев Толстой"')->getOne();
//        if(!$aid){echo 'y';}else{echo 'n';}
        
        foreach ($data as $book){
            $db->query("INSERT INTO `{$opts['prefix']}books` SET `name`='?s'",$book['book']);
            $bid = $db->getLastInsertId();
            echo "{$bid}<br>";
            foreach ($book['authors'] as $author){
                $aid = $db->query('SELECT `pid` from `tst_authors` where `fullName`="?s"',$author)->getOne();
                if(!$aid){
                    $db->query("INSERT INTO `{$opts['prefix']}authors` SET `fullName`='?s' ON DUPLICATE KEY UPDATE fullName=fullName",$author);
                    $aid = $db->getLastInsertId();
                }
                $db->query("INSERT INTO `{$opts['prefix']}authorship` SET ?Ai",array('bookID'=>$bid,'authorID'=>$aid));
            }
            echo "Book added<br>";
            flush();
        }
    }
}

//all book on author
//SELECT
//    autors.fullName,
//    GROUP_CONCAT(books.name)
//FROM autors
//JOIN authorship ON autors.pid = authorship.autorID
//JOIN books ON books.pid = authorship.bookID
//GROUP BY autors.pid

//all authors on book
//SELECT
//    books.name,
//    GROUP_CONCAT(autors.fullName),
//    COUNT(autors.fullName)
//FROM books
//JOIN authorship ON books.pid = authorship.bookID
//JOIN autors ON autors.pid = authorship.autorID
//GROUP BY books.pid

//all books with num authors
//SELECT
//    books.name,
//    GROUP_CONCAT(autors.fullName),
//    COUNT(autors.fullName) as cnt
//FROM books
//JOIN authorship ON books.pid = authorship.bookID
//JOIN autors ON autors.pid = authorship.autorID
//GROUP BY books.pid HAVING cnt>1