<?php
// Set up the SQLite database connection
//$dsn = 'sqlite:' . realpath('../../../../Chinook.db');
$dsn = 'mysql:host=localhost;dbname=mojo_finance';
$username = 'root';
$password = '12345678';
$conn = new PDO($dsn, $username, $password);

// Function to send a question to a local language model and get the SQL query response
function queryLocalLanguageModel($question) {
    $apiUrl = 'http://localhost:11434/api/generate'; // Local language model endpoint
    $data = json_encode(
        [
            "model"=> "llama3",//sqlcoder
            //"format"=> "json",
            "stream"=> false,
            "prompt"=> $question,
            "options"=> [
                "seed"=> 123,
                "temperature"=> 0
            ]
        ]
    );

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Function to execute SQL query on SQLite database
function executeSqlQuery($query, $conn) {
    try {
        if ($query) {
            // Prepare and execute the query
            $stmt = $conn->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Close the connection
            $conn = null;

            return $result;
        }
        die("Error executing SQL query: $query");
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }
}

function getDatabaseSchema($conn) {
    $schema = [];
    $tablesResult = $conn->query("SHOW TABLES");

    if (!$tablesResult) {
        die("Error fetching tables: " . $conn->errorInfo()[2]);
    }

    while ($tableRow = $tablesResult->fetch(PDO::FETCH_NUM)) {
        $tableName = $tableRow[0];
        $columnsResult = $conn->query("SHOW COLUMNS FROM $tableName");

        if (!$columnsResult) {
            die("Error fetching columns for table $tableName: " . $conn->errorInfo()[2]);
        }

        $columns = [];
        while ($columnRow = $columnsResult->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $columnRow;
        }

        $schema[$tableName] = $columns;
    }

    return $schema;
}

// Get the schema
$schema = getDatabaseSchema($conn);

// Format schema for AI prompt
function formatSchemaForPrompt($schema) {
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

$formattedSchema = formatSchemaForPrompt($schema);

// Example usage
$question = "Can you generate a only single SQL SELECT query with relational joins if required
without any description in the output just a raw SQL command from the statement below \n
Get all user accounts \n
Using the table schema below.\n
$formattedSchema";

// $question = "Can you generate a only single SQL SELECT query with relational joins if required
// without any description in the output just a raw SQL command from the statement below \n
// How any transactions are associated with user accounts \n
// Using the table schema below.\n
// $formattedSchema";

$llmResponse = queryLocalLanguageModel($question);

if (isset($llmResponse['response'])) {
    $pattern = '/```sql(.*?)```/s';

    preg_match($pattern, $llmResponse['response'], $matches);

    if (isset($matches[1])) {
        $sqlCommand = trim($matches[1]);
        var_dump('Generated SQL: ',$sqlCommand);
        echo "\n";
        $dbResults = json_encode(executeSqlQuery($sqlCommand, $conn));

        //var_dump($dbResults);
        $question = "Can you help me generate a report of the List of all accounts by account type (savings or checking) from this query response $dbResults";
        //var_dump('Generated RESULT: ',$question);

        $llmResponse = queryLocalLanguageModel($question);

        if (isset($llmResponse['response'])) {
            //echo $question;
            $report = $llmResponse['response'];
            print_r($report);
        }
    } else {
        echo "No SQL command found.";
    }
} else {
    echo "Error: Unable to get SQL query from language model.";
}

