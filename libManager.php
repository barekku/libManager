<?php
namespace Library;

class libManager extends \Database_Mysql
{
    private $db;
    
    function __construct($dbOpt) {
        $this->db = parent::create($dbOpt['host'], $dbOpt['user'], $dbOpt['pass'])
              // Выбор базы данных
              ->setDatabaseName($dbOpt['database'])
              // Выбор кодировки
              ->setCharset($dbOpt['charset']);
    }

    private function getRequest(){
        
        return true;
    }
    
    public function getAutors(){
        $res = $this->db->query('SELECT * FROM autors');
        $ret = array();
        while ($data = $res->fetch_assoc()) {
            $ret[]=$data;
        }
        return $ret;
    }
    
    public function getBooks(){
        $res = $this->db->query('
            SELECT books.name, autors.fullName FROM books
            LEFT OUTER JOIN autorship ON books.pid=autorship.bookID AND autorship.bookID=1
            LEFT OUTER JOIN books ON autorship.
        ');
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