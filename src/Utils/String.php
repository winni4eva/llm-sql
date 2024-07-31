<?php 
namespace Winnipass\AiSql\Utils;


class String
{
    public static function extractSql($string)
    {
        $pattern = '/```sql(.*?)```/s';
        preg_match($pattern, $string, $matches);

        if (isset($matches[1])) {
            $sqlCommand = trim($matches[1]);
            var_dump('Generated SQL: ',$sqlCommand);
            return $sqlCommand;
        }

        return '';
    }
}