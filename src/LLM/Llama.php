<?php 
namespace Winnipass\AiSql\LLM;
use Exception;
use Winnipass\AiSql\Request;
class Llama
{
    private $apiUrl = "http://localhost:11434/api/generate";
    private $model = "llama3"; //sqlcoder gpt-4
    private $temperature = 0;
    private $prompt;
    private $response;
    private $stream = false;
    private $options = [];
    private $format = "json";
    public function __construct() {}


    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }


    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }


    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function setStream($stream)
    {
        $this->stream = $stream;

        return $this;
    }


    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    public function queryLLM()
    {
        $data = json_encode(
            [
                "model"=> $this->model,
                "format"=> $this->format,
                "stream"=> $this->stream,
                "prompt"=> $this->prompt,
                "options"=> [
                    "seed"=> 123,
                    "temperature"=> $this->temperature
                ]
            ]
        );
        
        return $this->response = (new Request($this->apiUrl, $data))->call();
    }
}