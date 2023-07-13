<?php
class Database
{
    protected $connection = null;

    public function __construct()
    {
        if (getenv('TEST_ENV') == 'true') {
            $dbDatabaseName = DB_DATABASE_NAME . 'Test';
        } else {
            $dbDatabaseName = DB_DATABASE_NAME;
        }
        $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, $dbDatabaseName);
    
        if (mysqli_connect_errno()) {
            throw new Exception("Could not connect to database.");   
        }		
    }

    public function delete($table, $idColumn, $id)
    {
        $query = "DELETE FROM $table WHERE $idColumn = $id";
        $stmt = $this->executeStatement($query);
        $stmt->close();
    }

    public function select($table, $where)
    {
        if ($where) {
            $query = "SELECT * FROM $table WHERE $where";
        } else {
            $query = "SELECT * FROM $table";
        }
        $stmt = $this->executeStatement($query);
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);				
        $stmt->close();
        return $result;
    }

    public function insert($table, $columns, $data)
    {
        $columns = implode(', ', array_map(fn($column) => "`$column`", $columns));
        $columnsString = "($columns)";
        $records = array_map(function($row) {
            $quotedRow = array_map(fn($datum) => "'$datum'", $row);
            $joinedRow = implode(', ', $quotedRow);
            return "($joinedRow)";
        }, $data);
        $recordsString = implode(', ', $records);
        $query = "INSERT INTO $table $columnsString VALUES $recordsString;";
        $stmt = $this->executeStatement( $query );			
        $stmt->close();
    }

    public function update($table, $idColumn, $allIds, $columns, $data)
    {
        $set = array();
        foreach($columns as $column) {
            $whens = array();
            foreach($data as $item) {
                $id = $item->{$idColumn};
                if (is_numeric($item->{$column})) {
                    $value = $item->{$column};
                } else {
                    $unquotedValue = $item->{$column};
                    $value = "'$unquotedValue'";
                }
                array_push($whens, "WHEN $idColumn = $id THEN $value");
            }
            $whensString = implode(' ', $whens);
            array_push($set, "$column = CASE $whensString END");
        }
        $allIdsString = implode(', ', $allIds);
        $setString = implode(', ', $set);
        $query = "UPDATE $table SET $setString WHERE $idColumn IN ($allIdsString);";
        $stmt = $this->executeStatement( $query );			
        $stmt->close();
    }

    private function executeStatement($query = "", $params = [])
    {
        $stmt = $this->connection->prepare($query);
        if($stmt === false) {
            throw New Exception("Unable to do prepared statement: " . $query);
        }
        if( $params ) {
            $stmt->bind_param($params[0], $params[1]);
        }
        $stmt->execute();
        return $stmt;
    }
}