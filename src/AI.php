<?php 
namespace Winnipass\AiSql;
use Winnipass\AiSql\Databases\MySQL;
use Winnipass\AiSql\LLM\Llama;
use Winnipass\AiSql\Utils\StringParser;

class AI
{

    private $dbInstance;

    private $dbConnection; 

    public function __construct() {}

    public function ask($userQuestion)
    {
        $schema = $this->dbInstance->getSchema();

        $question = "Can you generate a prompt to request for only a single $this->dbConnection SQL SELECT query with relational joins if required
        for the question below. \n
        $userQuestion based on the schema below
        $schema";

        $promptResponse = $this->promptLLM($question);


        if (isset($promptResponse['response'])) {
            $promptResponse = $this->promptLLM($promptResponse['response']);
            if (isset($promptResponse['response'])) {
                $sql = (new StringParser())->extractSql($promptResponse['response']);

                $results = $this->dbInstance->query($sql);

                $this->dbInstance->disconnect();

                $formattedResults = json_encode($results);
            
                $question = "Can you help me generate a detailed report based on the question $userQuestion, from this query response $formattedResults";

                $promptResponse = $this->promptLLM($question);
                
                if (isset($promptResponse['response'])) {
                    print_r(($promptResponse['response']));
                } else {
                    die('Error: Prompt Response ' . $promptResponse['error']);
                }
            } else {
                die('Error: Prompt Response ' . $promptResponse['error']);
            }
        } else {
            die('Error: Prompt Response ' . $promptResponse['error']);
        }
    }

    public function dbConnect() 
    {
        $host = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $dbName = $_ENV['DB_DATABASE'];

        $this->dbInstance = (new MySQL($host, $username, $password, $dbName))->connect();

        return $this;
    }

    private function promptLLM($question)
    {
        return (new Llama())->setApiUrl("http://localhost:11434/api/generate")
            ->setModel("llama3")
            ->setTemperature(0)
            ->setPrompt($question)
            ->setStream(false)
            ->setFormat("json")
            ->queryLLM();
    }

    public function setDbConnection($dbConnection)
    {
        $this->dbConnection = $dbConnection;

        return $this;
    }

}

require 'vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$userQuestion = "Get all account types, with their user details and balances";
//$userQuestion = "Get all user account transactions";

(new AI())->setDbConnection($_ENV['DB_CONNECTION'])->dbConnect()->ask($userQuestion);