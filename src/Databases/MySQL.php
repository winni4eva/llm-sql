<?php 
namespace Winnipass\AiSql\Databases;

use PDO;
use PDOException;

class MySQL
{
    protected $conn;
    public function __construct(protected $host, protected $username, protected $password, protected $dbName) {}

    public function connect()
    {
        $dsn = "mysql:host=$this->host;dbname=$this->dbName";

        $this->conn = new PDO($dsn, $this->username, $this->password);
    }

    public function disconnect()
    {
        $this->conn = null;
    }

    public function query($query)
    {
        try {
            if ($query) {
                $stmt = $this->conn->query($query);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->disconnect();
    
                return $result;
            }
            die("Error executing SQL query: $query");
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function getSchema()
    {
        $schema = [];
        $tablesResult = $this->conn->query("SHOW TABLES");

        if (!$tablesResult) {
            die("Error fetching tables: " . $this->conn->errorInfo()[2]);
        }

        while ($tableRow = $tablesResult->fetch(PDO::FETCH_NUM)) {
            $tableName = $tableRow[0];
            $columnsResult = $this->conn->query("SHOW COLUMNS FROM $tableName");

            if (!$columnsResult) {
                die("Error fetching columns for table $tableName: " . $this->conn->errorInfo()[2]);
            }

            $columns = [];
            while ($columnRow = $columnsResult->fetch(PDO::FETCH_ASSOC)) {
                $columns[] = $columnRow;
            }

            $schema[$tableName] = $columns;
        }

        return $this->formatSchemaForPrompt($schema);
    }

    private function formatSchemaForPrompt($schema)
    {
        $formattedSchema = "Database Schema:\n\n";

        foreach ($schema as $tableName => $columns) {
            $formattedSchema .= "Table: $tableName\n";
            $formattedSchema .= "Columns:\n";

            foreach ($columns as $column) {
                $formattedSchema .= "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
            }

            $formattedSchema .= "\n";
        }

        return $formattedSchema;  
    }
}