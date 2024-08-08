<?php 
namespace Winnipass\AiSql\Databases;
interface DBInterface
{
    public function connect();
    public function disconnect();
    public function query($query);
    public function getSchema();
}