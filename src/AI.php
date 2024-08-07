<?php 
namespace Winnipass\AiSql;
use Winnipass\AiSql\Databases\MySQL;
use Winnipass\AiSql\LLM\Llama;
use Winnipass\AiSql\Utils\StringParser;

class AI
{

    private $dbInstance; 

    public function __construct() {}

    public function ask(string $userQuestion)
    {
        $this->dbConnect();

        $question = $this->generateInitialPrompt($userQuestion);

        $promptResponse = $this->promptLLM($question);

        $promptResponse = $this->makeSqlQueryPrompt($promptResponse);

        $queryResults = $this->queryDbWithPromptResponse($promptResponse);

        $this->generateReport($queryResults, $userQuestion);
    }

    private function promptLLM(string $question): array
    {
        return (new Llama())->setApiUrl("http://localhost:11434/api/generate")
            ->setModel("llama3")
            ->setTemperature(0)
            ->setPrompt($question)
            ->setStream(false)
            ->setFormat("json")
            ->queryLLM();
    }

    private function dbConnect(): void 
    {
        $this->dbInstance = (new MySQL($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']))->connect();
    }

    private function generateInitialPrompt(string $userQuestion): string 
    {
        $schema = $this->dbInstance->getSchema();
        $dbConnection = $_ENV['DB_CONNECTION'];

        $question = "Can you generate a prompt to request for only a single $dbConnection SQL SELECT query with relational joins if required
        for the question below. \n
        $userQuestion based on the schema below
        $schema";

        return $question;
    }

    private function makeSqlQueryPrompt(array $promptResponse): array 
    {
        if (isset($promptResponse['response'])) {
            $response = $this->promptLLM($promptResponse['response']);
            var_dump('SQL PROMPT RESPONSE: ', $response['response']);
            return $response;
        } else {
            die('Error: Prompt Response ' . $promptResponse['error']);
        }
    }

    private function queryDbWithPromptResponse(array $promptResponse): string | null
    {
        if (isset($promptResponse['response'])) {
            $sql = (new StringParser())->extractSql($promptResponse['response']);
    
            $results = $this->dbInstance->query($sql);
            $this->dbInstance->disconnect();

            if ($results)
                return json_encode($results);

            die("Error generating query, kindly rephrase your question");
        
        }
        
        die('Error: Prompt Response ' . $promptResponse['error']);
    }

    private function generateReport(string $queryResults, string $userQuestion): void 
    {
        $question = "Can you help me generate a detailed report based on the question $userQuestion, from this query response $queryResults";

        $promptResponse = $this->promptLLM($question);
        
        if (isset($promptResponse['response'])) {
            print_r($promptResponse['response']);
        } else {
            die('Error: Prompt Response ' . $promptResponse['error']);
        }
    }

}

require 'vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


if ($argc > 1) {
    $userQuestion = $argv[1];
    (new AI())->ask($userQuestion);
} else {
    die('No question provided');
}