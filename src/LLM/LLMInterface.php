<?php 
namespace Winnipass\AiSql\LLM;
interface LLMInterface
{
    public function setApiUrl($apiUrl);
    public function setModel($model);
    public function setTemperature($temperature);
    public function setPrompt($prompt);
    public function setStream($stream);
    public function setOptions($options);
    public function setFormat($format);
    public function queryLLM();
}