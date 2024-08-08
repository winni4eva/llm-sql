<?php 
namespace Winnipass\AiSql;

use Winnipass\AiSql\Databases\DBInterface;
use Winnipass\AiSql\LLM\LLMInterface;
use Winnipass\AiSql\Utils\StringParser;


abstract class BaseAI
{
    protected $dbInstance;

    public function __construct(protected $model = null)
    {
        $this->model = $model ?? $_ENV['LLM_MODEL'];
    }

    protected function promptLLM(LLMInterface $llm, string $question): array
    {
        return $llm->setApiUrl($_ENV['LLM_API_URL'] ?? "http://localhost:11434/api/generate")
            ->setModel($this->model)
            ->setTemperature(0)
            ->setPrompt($question)
            ->setStream(false)
            ->setFormat("json")
            ->queryLLM();
    }

    protected function dbConnect(DBInterface $db): void 
    {
        $this->dbInstance = $db->connect();
    }

    protected function generateInitialPrompt(string $userQuestion): string 
    {
        $schema = $this->dbInstance->getSchema();
        $dbConnection = $_ENV['DB_CONNECTION'];
        $dbConnectionVersion = $_ENV['DB_CONNECTION_VERSION'];

        $question = "Can you generate a prompt to request for only a single $dbConnection Version $dbConnectionVersion 
        Compatible SQL SELECT query with relational joins if required
        for the question below. \n
        $userQuestion based on only the TABLES and relationships in the schema below
        $schema";

        return $question;
    }

    protected function makeSqlQueryPrompt(LLMInterface $llm, array $promptResponse): array 
    {
        if (isset($promptResponse['response'])) {
            $response = $this->promptLLM($llm, $promptResponse['response']);
            var_dump('SQL PROMPT RESPONSE: ', $response['response']);
            return $response;
        } else {
            die('Error: Prompt Response Make SQL ' . $promptResponse['error']);
        }
    }

    protected function queryDbWithPromptResponse(array $promptResponse): string | null
    {
        if (isset($promptResponse['response'])) {
            $sql = (new StringParser())->extractSql($promptResponse['response']);
    
            $results = $this->dbInstance->query($sql);
            $this->dbInstance->disconnect();

            if ($results)
                return json_encode($results);

            die("Error generating query, kindly rephrase your question");
        
        }
        
        die('Error: Prompt Response Query DB' . $promptResponse['error']);
    }

    protected function generateReport(string $queryResults, string $userQuestion): void 
    {
        $question = "Can you help me generate a detailed report based on the question $userQuestion, from this query response $queryResults";

        $promptResponse = $this->promptLLM($this->llm, $question);
        
        if (isset($promptResponse['response'])) {
            print_r($promptResponse['response']);
        } else {
            die('Error: Prompt Response Generate Report' . $promptResponse['error']);
        }
    }
}