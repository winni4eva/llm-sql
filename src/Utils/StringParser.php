<?php 
namespace Winnipass\AiSql\Utils;


class StringParser
{
    public function __construct() {}
    public function extractSql($string)
    {
        $pattern = '/```sql(.*?)```/s';
        preg_match($pattern, $string, $matches);

        if (isset($matches[1])) {
            $sqlCommand = trim($matches[1]);
            return $sqlCommand;
        }

        return $string;
    }
}