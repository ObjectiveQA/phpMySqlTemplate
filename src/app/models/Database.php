<?php
class Database
{
    protected $connection = null;

    public function __construct()
    {
        if (getenv('TEST_ENV') === 'true') {
            $dbDatabaseName = DB_DATABASE_NAME . 'Test';
        } else {
            $dbDatabaseName = DB_DATABASE_NAME;
        }
        $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, $dbDatabaseName);
    
        if (mysqli_connect_errno()) {
            throw new Exception("Could not connect to database.");   
        }		
    }

    private function buildDelete($table, $where)
    {
        $whereSql = $this->buildWhere($where);

        return "DELETE FROM $table $whereSql;";
    }

    private function buildInsert($table, $columns, $rows, $insertId)
    {
        $columnsContent = implode(', ', array_map(fn($column) => "`$column->name`", $columns));
        $columnsString = "($columnsContent)";

        $rowStrings = array();

        foreach($rows as $row) {
            $datumStrings = array();

            foreach($columns as $column) {
                array_push($datumStrings, $this->prepareSqlValue($row->{$column->name}, $column->type));
            }

            $rowContent = implode(', ', $datumStrings);
            array_push($rowStrings, "($rowContent)");
        }

        $initialRowsValue = implode(', ', $rowStrings);

        if (str_contains($initialRowsValue, '<INSERT_ID>')) {
            if (!$insertId) {
                throw new Exception("Could not insert as id null, initial rows value: $initialRowsValue");
            } else {
                $rowsValue = str_replace('<INSERT_ID>', $insertId, $initialRowsValue);
            }
        } else {
            $rowsValue = $initialRowsValue;
        }

        return "INSERT INTO $table $columnsString VALUES $rowsValue;";
    }

    private function buildSelect($table, $columns, $where)
    {
        if ($columns && count($columns) > 0) {
            $columnsString = implode(', ', $columns);
        } else {
            $columnsString = '*';
        }

        $query = "SELECT $columnsString FROM $table";

        if ($where) {
            $whereSql = $this->buildWhere($where);
            return "$query $whereSql;";
        }

        return $query;
    }

    private function buildUpdate($table, $columns, $row, $where)
    {
        $setStrings = array();
        foreach($columns as $column) {
            $datum = $this->prepareSqlValue($row->{$column->name}, $column->type);
            array_push($setStrings, "$column->name = $datum");
        }
        $setString = implode(', ', $setStrings);

        return "UPDATE $table SET $setString WHERE $where->column = $where->value;";
    }

    private function buildQuery($queryData, $insertId = null)
    {
        switch ($queryData->statementType) {
            case 'delete':
                return $this->buildDelete($queryData->table, $queryData->where);
            case 'insert':
                return $this->buildInsert($queryData->table, $queryData->columns, $queryData->rows, $insertId);
            case 'select':
                return $this->buildSelect($queryData->table, $queryData->columns, $queryData->where);
            case 'update':
                return $this->buildUpdate($queryData->table, $queryData->columns, $queryData->row, $queryData->where);
            default:
                $statementType = $queryData->statementType;
                throw new Exception("Statement type '$statementType' not handled.");
        }
    }

    private function buildWhere($where)
    {
        if (property_exists($where, 'valueArray') && $where->valueArray) {
            $operator = 'IN';
            $values = array_map(fn($datum) => $this->prepareSqlValue($datum, $where->valueType), $where->value);
            $valueContent = implode(', ', $values);
            $value = "($valueContent)";
        } else {
            $operator = '=';
            $value = $this->prepareSqlValue($where->value, $where->valueType);
        }

        return "WHERE $where->column $operator $value";
    }

    protected function select($queryData)
    {
        $query = $this->buildQuery($queryData);

        $stmt = $this->connection->prepare($query);
        if($stmt === false) {
            throw New Exception("Unable to do prepared statement: " . $query);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);			
        $stmt->close();

        return $result;
    }

    protected function executeQueries($queryObjects)
    {
        try {
            $this->connection->begin_transaction();

            $insertId = null;

            foreach($queryObjects as $queryObject) {
                $querySql = $this->buildQuery($queryObject, $insertId);
                $stmt = $this->connection->prepare($querySql);
                    
                if($stmt === false) {
                    throw New Exception("Unable to do prepared statement: " . $querySql);
                }
                $stmt->execute();
                
                if (property_exists($queryObject, 'storeId') && $queryObject->storeId) {
                    $insertId = $this->connection->insert_id;
                }
            }

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();

            throw $e;
        }
    }

    private function prepareSqlValue($rawValue, $dataType)
    {
        switch ($dataType) {
            case 'text':
                return "'$rawValue'";
            case 'number':
                return $rawValue;
            default:
                throw new Exception("Type '$dataType' not handled.");
        }
    }
}
