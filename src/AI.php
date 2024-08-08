<?php 
namespace Winnipass\AiSql;

require 'vendor/autoload.php';
use Winnipass\AiSql\Databases\MySQL;
use Winnipass\AiSql\LLM\Llama;

class AI extends BaseAI
{ 
    public function __construct() 
    {
        parent::__construct();
        $this->llm = new Llama();
    }

    public function ask(string $userQuestion)
    {
        $this->dbConnect(
            (new MySQL($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']))
        );

        $this->setUserQuestion($userQuestion)
            ->generateInitialPrompt()
            ->promptLLM()
            ->makeSqlQueryPrompt()
            ->queryDbWithPromptResponse()
            ->generateReport();
    }
}

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


if ($argc > 1) {
    $userQuestion = $argv[1];
    (new AI())->ask($userQuestion);
} else {
    die('No question provided');
}