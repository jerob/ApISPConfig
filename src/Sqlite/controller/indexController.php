<?php
namespace ApISPConfig\Controller;

class IndexController {

	public function indexAction() {
		try{
		    $pdo = new PDO('sqlite:'.dirname(__FILE__).'/database.sqlite');
		    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ERRMODE_WARNING | ERRMODE_EXCEPTION | ERRMODE_SILENT

		    # INSERT
		    $sql = 'INSERT INTO test ( name ) VALUES ( :name )';
			$sth = $pdo->prepare( $sql );
			$sth->bindParam(':name', microtime());
			$sth->execute( );

			# SELECT
			$sql = 'SELECT * FROM test ORDER BY ID DESC';
			$sth = $pdo->prepare( $sql );
			$sth->execute();
			$res = $sth->fetchAll(PDO::FETCH_ASSOC);

			# VIEW
			echo '<table>';
			echo '<tr><th>ID</th><th>Name</th></tr>';
			foreach($res as $row) {
				echo '<tr><td>' . $row['id'] . '</td><td>' . $row['name'] . '</td></tr>';
			}
			echo '</table>';

		} catch(Exception $e) {
		    echo "Error SQLite : ".$e->getMessage();
		    die();
		}
	}
}