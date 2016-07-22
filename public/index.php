<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

header('Content-type: application/json; charset=utf-8');

require '../app/flight/Flight.php';

Flight::set('flight.log_errors', true);

$pdo_attributes = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_CASE => PDO::CASE_LOWER,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
    //PDO::ATTR_EMULATE_PREPARES => false
);

Flight::register('db', 'PDO', array(
    'mysql:host=localhost;dbname=u563_library',
    'u563_library',
    'ts4KVak7Ft',
    $pdo_attributes
) );

Flight::set('limit', abs(intval($_GET['per_page'])) ?: 30);
Flight::set('offset', abs(intval($_GET['page'])) * Flight::get('limit'));


Flight::route('/search', function() {
    $items = array();
    $resultCount = 0;

    $searchQuery = trim($_GET['q']);

    if(!empty($searchQuery)){
        $sth = Flight::db()->prepare(
        "SELECT *,
        (
            MATCH (`author`) AGAINST (:searchQuery IN BOOLEAN MODE)*10000 +
            MATCH (`title`)  AGAINST (concat('\"', :searchQuery, '\"') IN BOOLEAN MODE)*1000 +
            MATCH (`title`)  AGAINST (:searchQuery IN BOOLEAN MODE)*100 +
            MATCH (`title`)  AGAINST (concat(:searchQuery, '*') IN BOOLEAN MODE) * 10 +
            MATCH (`body`)   AGAINST (:searchQuery)
        ) as `relevance`
        FROM `books`
        WHERE MATCH (`author`) AGAINST (:searchQuery IN BOOLEAN MODE)
           OR MATCH (`title`)  AGAINST (concat('\"', :searchQuery, '\"') IN BOOLEAN MODE)
           OR MATCH (`title`)  AGAINST (:searchQuery IN BOOLEAN MODE)
           OR MATCH (`title`)  AGAINST(concat(:searchQuery, '*') IN BOOLEAN MODE)
           OR MATCH (`body`)   AGAINST (:searchQuery)
        ORDER BY `relevance` desc
        LIMIT :offset, :limit
        ");

        $sth->bindValue(':searchQuery', Flight::db()->quote($searchQuery), PDO::PARAM_STR);
        $sth->bindValue(':offset', Flight::get('offset'), PDO::PARAM_INT);
        $sth->bindValue(':limit', Flight::get('limit'), PDO::PARAM_INT);
        $sth->execute();

        foreach ($sth->fetchAll() as $raw) {
            $book = new Book($raw);

            array_push($items, $book);
        }

        $count_sth = Flight::db()->prepare(
            "SELECT count(*)
            FROM `books`
            WHERE MATCH (`author`) AGAINST (:searchQuery IN BOOLEAN MODE)
               OR MATCH (`title`)  AGAINST (concat('\"', :searchQuery, '\"') IN BOOLEAN MODE)
               OR MATCH (`title`) AGAINST (:searchQuery IN BOOLEAN MODE)
               OR MATCH (`title`) AGAINST(concat(:searchQuery, '*') IN BOOLEAN MODE)
               OR MATCH (`body`) AGAINST (:searchQuery)
        ");
        $count_sth->bindValue(':searchQuery', Flight::db()->quote($searchQuery), PDO::PARAM_STR);
        $count_sth->execute();
        $resultCount = intval($count_sth->fetchColumn());

        Flight::json(array(
            'count' => $resultCount,
            'books' => $items
        ));
    } else {
        Flight::json(array(
            'error' => 'Empty search query'
        ));
    }
});

/**
 * Get recent books
 */
Flight::route('/books', function(){

    $items = array();

    $sth = Flight::db()->prepare("SELECT * from `books` order by `created` desc limit :offset, :limit");

    $sth->bindValue(':offset', Flight::get('offset'), PDO::PARAM_INT);
    $sth->bindValue(':limit', Flight::get('limit'), PDO::PARAM_INT);

    $sth->execute();

    foreach ($sth->fetchAll() as $raw) {
        $book = new Book($raw);

        array_push($items, $book);
    }

    Flight::json($items);
});

/**
 * Get books related to book(id)
 */
Flight::route('/books/@id/related', function($id){

    $items = array();

    $bookQuery = Flight::db()->prepare("SELECT * FROM `books` WHERE `id`=:id LIMIT 1");
    $bookQuery->bindValue(':id', intval($id), PDO::PARAM_INT);
    $bookQuery->execute();

    if($bookRaw = $bookQuery->fetch()){
        $book = new Book($bookRaw);

        $relatedBooksQuery = Flight::db()->prepare(
            "SELECT *
            FROM `books`
            WHERE (
                MATCH (`title`) AGAINST (concat(:title, '*') IN BOOLEAN MODE)
                   OR `body` LIKE concat('%621: ', :lbc, '%')
            )
            AND `id` != :id
            LIMIT :offset, :limit"
        );
        $relatedBooksQuery->bindValue(':lbc', $book->lbc, PDO::PARAM_STR);
        $relatedBooksQuery->bindValue(':title', Flight::db()->quote($book->title), PDO::PARAM_STR);
        $relatedBooksQuery->bindValue(':id', $book->id, PDO::PARAM_INT);
        $relatedBooksQuery->bindValue(':offset', Flight::get('offset'), PDO::PARAM_INT);
        $relatedBooksQuery->bindValue(':limit', Flight::get('limit'), PDO::PARAM_INT);
        $relatedBooksQuery->execute();

        foreach ($relatedBooksQuery->fetchAll() as $raw) {
            $book = new Book($raw);

            array_push($items, $book);
        }
        Flight::json($items);
    } else {
        Flight::json(array('error' => 'Book with this id is not found'));
    }
});


Flight::route('/books/categories', function($id){
    Flight::json(array(
        'роман',
        'Боевик',
        'Детектив',
        'Биографии',
        'Приключения',
        'Дети',
        'Психология',
        'История',
        'Фантастика',
        'Политика'
    ));
});

/**
 * Get all bookshelves
 */
Flight::route('/bookshelves', function(){
    $bookshelvesQuery = Flight::db()->prepare(
        "SELECT *,
        (
            select distinct count(*)
            from `books_helves`
            where `bookshelf`=`bookshelves`.`id`
        ) as 'bookCount'
        from `bookshelves`
        where `featured` = 1
        order by `id` desc
        limit 0, 5
        "
    );

    $bookshelvesQuery->execute();

    Flight::json( $bookshelvesQuery->fetchAll() );
});

/**
 * Get all bookshelves
 */
Flight::route('/bookshelves/latest', function(){
    $bookshelvesQuery = Flight::db()->prepare(
        "SELECT *,
        (
            select distinct count(*)
            from `books_helves`
            where `bookshelf`=`bookshelves`.`id`
        ) as 'bookCount'
        from `bookshelves`
        where `featured` = 0
        order by `id` desc
        limit :offset, :limit
        "
    );

    $bookshelvesQuery->bindValue(':offset', Flight::get('offset'), PDO::PARAM_INT);
    $bookshelvesQuery->bindValue(':limit', Flight::get('limit'), PDO::PARAM_INT);

    $bookshelvesQuery->execute();

    Flight::json( $bookshelvesQuery->fetchAll() );
});

/**
 * Get all information about bookshelf with id $id
 */
Flight::route('/bookshelves/@id', function($id){
    $items = array();
    $resultCount = 0;

    $id = intval($id);

    $sth = Flight::db()->prepare(
    "SELECT `books`.*
    FROM `books`
    INNER JOIN `books_helves` ON `books_helves`.`book`=`books`.`id`
        AND `books_helves`.`bookshelf`=:id
    ");

    $sth->bindValue(':id', $id, PDO::PARAM_INT);

    $sth->execute();

    foreach ($sth->fetchAll() as $raw) {

        $book = new Book($raw);

        array_push($items, $book);
    }

    Flight::json($items);
});

Flight::map('notFound', function(){
    Flight::json(array('error' => 'Undefined method called'));
});

Flight::map('error', function(Exception $e){
    Flight::json(array('error' => 'System error, you can nothing to do. Contact librarian'));

});

Flight::start();
