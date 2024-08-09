<?php 
namespace Winnipass\AiSql;

use Winnipass\AiSql\Databases\DBInterface;
use Winnipass\AiSql\LLM\LLMInterface;
use Winnipass\AiSql\Utils\StringParser;


abstract class BaseAI
{
    protected DBInterface $dbInstance;
    protected $promptResponse;
    protected $queryResults;
    protected string $userQuestion;
    protected LLMInterface $llm;

    protected string $prompt;

    public function __construct(protected $model = null)
    {
        $this->model = $model ?? $_ENV['LLM_MODEL'];
    }

    protected function promptLLM(): self
    {
        //var_dump('######## Current Prompt : ' . $this->prompt . '########');
        $this->promptResponse = $this->llm->setApiUrl($_ENV['LLM_API_URL'] ?? "http://localhost:11434/api/generate")
            ->setModel($this->model)
            ->setTemperature(0)
            ->setPrompt($this->prompt)
            ->setStream(false)
            ->setFormat("json")
            ->queryLLM();
        
        return $this;
    }

    protected function dbConnect(DBInterface $db): void 
    {
        $this->dbInstance = $db->connect();
    }

    protected function generateInitialPrompt(): self 
    {
        $schema = $this->dbInstance->getSchema();
        $dbConnection = $_ENV['DB_CONNECTION'];
        $dbConnectionVersion = $_ENV['DB_CONNECTION_VERSION'];

        $this->prompt = "Can you generate a prompt to request for only a single $dbConnection Version $dbConnectionVersion 
        Compatible SQL SELECT query with relational joins if required
        for the question below. \n
        $this->userQuestion based on only the TABLES, COLUMNS and relationships in the schema below
        $schema";

        return $this;
    }

    protected function makeSqlQueryPrompt(): self
    {
        if (isset($this->promptResponse['response'])) {
            $this->prompt = $this->promptResponse['response'];
            $this->promptLLM();
            var_dump('SQL PROMPT RESPONSE: ', $this->prompt);
        } else {
            die('Error: Prompt Response Make SQL');
        }
        return $this;
    }

    protected function queryDbWithPromptResponse(): self
    {
        if (isset($this->promptResponse['response'])) {
            $sql = (new StringParser())->extractSql($this->promptResponse['response']);
            
            try {
                $results = $this->dbInstance->query($sql);
            } catch (\Throwable $th) {
                var_dump("\n Error with the sugested query : \n" . $th->getMessage() . "\n Retrying with new prompt");
                $response = $this->promptResponse['response'];

                $this->prompt = "This error was returned : " . $th->getMessage() . "  \n
                While running the query below : \n
                $sql \n
                Can you help rewrite to fix the errors and do not repeat the same query above again without applying the new fix.";

                var_dump(" \n New prompt". $this->prompt);

                $this->promptLLM();
                $this->queryDbWithPromptResponse();
            }
            
            $this->dbInstance->disconnect();

            if ($results) {
                $this->queryResults = json_encode($results);
            } else {
                die("Error generating query, kindly rephrase your question");
            }
        
        } else {
            die('Error: Prompt Response Query DB ' . $this->promptResponse['error']);
        }

        return $this;
    }

    protected function generateReport(): void 
    {
        $this->prompt = "Can you help me generate a detailed report based on the question $this->userQuestion, from this query response $this->queryResults";

        $this->promptLLM();
        
        print_r($this->promptResponse['response']);
    }

    public function setUserQuestion(string $userQuestion): self
    {
        $this->userQuestion = $userQuestion;

        return $this;
    }
}