<?php 
namespace Winnipass\AiSql;
use Winnipass\AiSql\Databases\MySQL;
use Winnipass\AiSql\LLM\Llama;
use Winnipass\AiSql\Utils\StringParser;

class AI
{

    private $dbInstance;

    public function __construct() {}

    public function dbConnect() 
    {
        $host = 'localhost';
        $username = 'root';
        $password = '12345678';
        $dbName = 'mojo_finance';

        $this->dbInstance = (new MySQL($host, $username, $password, $dbName))->connect();
    }

    public function ask()
    {
        $this->dbConnect();
        $schema = $this->dbInstance->getSchema();

        $question = "Can you generate a only single SQL SELECT query with relational joins if required
        without any description in the output just a raw SQL command from the statement below \n
        Get all user accounts \n
        Using the table schema below.\n
        $schema";

        // $question = "Can you generate a only single SQL SELECT query with relational joins if required
        // without any description in the output just a raw SQL command from the statement below \n
        // How any transactions have been generated for user accounts \n
        // Using the table schema below.\n
        // $schema";

        $promptResponse = (new Llama())->setApiUrl("http://localhost:11434/api/generate")
            ->setModel("llama3")
            ->setTemperature(0)
            ->setPrompt($question)
            ->setStream(false)
            ->setFormat("json")
            ->queryLLM();


        if (isset($promptResponse['response'])) {
            $sql = (new StringParser())->extractSql($promptResponse['response']);
            $results = $this->dbInstance->query($sql);
            $this->dbInstance->disconnect();
            $formattedResults = json_encode($results);
            
            $question = "Can you help me generate a report of the List of all accounts by account type (savings or checking) from this query response $formattedResults";
            //$question = "Can you help me generate a report of the List of all accounts by account type (savings or checking) with their balances and transactions from this query response $formattedResults";

            $promptResponse = (new Llama())->setApiUrl("http://localhost:11434/api/generate")
                ->setModel("llama3")
                ->setTemperature(0)
                ->setPrompt($question)
                ->setStream(false)
                ->setFormat("json")
                ->queryLLM();
            
            if (isset($promptResponse['response'])) {
                print_r(($promptResponse['response']));
            }
        }
    }

}

require 'vendor/autoload.php';

(new AI())->ask();