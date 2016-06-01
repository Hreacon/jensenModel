<?php

// VERSION 1.1.1
// 1.1 - Added convertFileArray function.
// 1.1.1 - Add getListFromTableWhereColumn
// Written by:
// Nicholas Jensen

class jensenModel {
    private $pdo;
    private $mysqlSelectAll = "SELECT * FROM ";

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private function escapeInput($input) {
        return $this->pdo->quote($input);
    }

    private function checkInput($input) {
        // check db input, make safe for db. Input should have no ; or '
        // assume whoever is doing this is bad, and just chop the end off the input string

        // check for semicolon
        $index = strpos($input, ';', 0);
        // remove all after semicolon
        if($index > 0)
            $input = substr($input,0,$index);

        // check for '
        $index = strpos($input, "'", 0);
        if($index > 0)
            $input = substr($input, 0, $index);

        return $input;
    }

    public function selectQuery($query) {
        $info = $this->preparedQuery($query);
        // TODO error handle
        return $info->fetchAll(PDO::FETCH_ASSOC);
    }

    public function query($query) {
        return $this->pdo->query($query);
    }

    public function preparedQuery($query, $values = '') {
        //var_dump($query);
        $stmt = $this->pdo->prepare($query);
        if(is_array($values))
            $output = $stmt->execute($values);
        else $output = $stmt->execute();
        if($output)
            $output = $stmt;

        return $output;
    }

    public function getAllFromTable($table, $page = 0, $perPage=30) {
        $table = $this->checkInput($table);
        $page = $this->checkInput($page);
        $perPage = $this->checkInput($perPage);
        $sql = $this->mysqlSelectAll . $table . " LIMIT ". ($page*$perPage) .", $perPage";
        return $this->selectQuery($sql);
    }

    public function getResultFromTableWithId( $table, $id ) {
        $id = $this->escapeInput($id);
        $result = $this->selectQuery($this->mysqlSelectAll . $table. " WHERE id = $id");
        return $result;
    }
    
    public function getListFromTableWhereColumn($table, $column, $value) {
      $column = $this->checkInput($column);
      $value = $this->checkInput($value);
      return $this->selectQuery($this->mysqlSelectAll . $table. " WHERE $column = $value");
    }

    public function search($table, $field, $term) {
        $table = $this->checkInput($table);
        $field = $this->checkInput($field);
        $term = $this->escapeInput($term);
        $query = $this->mysqlSelectAll . $table . " WHERE $field like $term";
        return $this->selectQuery($query);
    }
    public function searchTable($table, $field, $term) {
        return $this->search($table,$field,"$".$term."%");
    }

    public function searchTableTermFirst($table, $field, $term) {
        return $this->search($table,$field,$term."%");
    }

    // PARAM: $table - string, the table to work with
    // PARAM: $info - array, the column names in the database
    // Defaults the id field to null
    // RETURN: formatted sql insert query
    public function makeInsert($table, $info) {
        $sql1 = 'id, ';
        $sql2 = 'null, ';
        $sql = "INSERT INTO " . $this->checkInput($table) . "(";
        // make sure info is an array or it will throw errors
        if(is_array($info)) {
            foreach ($info as $field) {
                $sql1 .=  $field . ', ';
                $sql2 .= '?, ';
            }
        }
        $sql .= substr($sql1, 0, strlen($sql1)-2) . ') VALUES ('.substr($sql2, 0, strlen($sql2)-2) . ');';
        return $sql;
    }

    // PARAM: $table - string, name of table to work on
    // PARAM: $fields - array of fields in the table to work with
    // PARAM: $id - the id of the row to update
    // RETURN: Prepared SQL query for table and fields
    public function makeUpdate($table, $fields, $id) {
        $sql = "UPDATE ". $this->checkInput($table) . " SET ";
        if(is_array($fields)) {
            foreach($fields as $field) {
                $sql .= '`'.$field.'`'. "= ?, ";
            }
        }
        $sql = substr($sql, 0, strlen($sql)-2);
        $sql .= " WHERE id = " . $this->checkInput($id);
        return $sql;
    }

    // PARAM: $table - string, the table to work on
    // PARAM: $fields - array, column names
    // PARAM: $info - array, values to put into the table in the same order as the fields array
    public function insertIntoTable($table, $fields, $values) {
        $sql = $this->makeInsert($table, $fields);
        return $this->preparedQuery($sql, $values);
    }

    // info is a key->value pair of column name -> column info
    // PARAM: $table - string, table to work with
    // PARAM: $fields - array, column names of the table to work with
    // PARAM: $info - array, in order of $fields
    // PARAM: $id - int, id of row to interact with
    public function updateTable($table, $fields, $info, $id) {
        $sql = $this->makeUpdate($table, $fields, $id);
//        echo $sql;
        return $this->preparedQuery($sql, $info);
    }

    // this function takes an array of values and an array of column names in the same order
    public function makeInfo($keys, $values) {
        $info = [];
        $index = 0;
        if(is_array($values)) {
            foreach($values as $value) {
                $info[$keys[$index]] = $value;
                $index++;
            }
        }
        return $info;
    }
    public function deleteFromTableWithId($table, $id) {
        $sql = "DELETE FROM " . $this->checkInput($table) . " WHERE id = ?";
        return $this->preparedQuery($sql, [$id]);
    }

    // converts the files array of
    // name-0,1 tmp-0,1
    //to 0-name,tmp 1-name, tmp
    function convertFilesArray($files) {
      $output = [];
      for($i=0;$i<count($files['name']);$i++){ // loop through files
        while($file = current($files)) { // loop through array indicies
          $output[$i][key($files)] = $file[$i];
          next($files);
        }
      }
      return $output;
    }
} // end class jensenModel

?>
