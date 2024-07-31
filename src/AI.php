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

        //$userQuestion = "Get all savings accounts with their balances";
        $userQuestion = "Get all user account transactions with their details";

        $question = "Can you generate a prompt to request for only a single MySQL SQL SELECT query with relational joins if required
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

}

require 'vendor/autoload.php';

(new AI())->ask();