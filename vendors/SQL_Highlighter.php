<?
/*********************************************************************
 * Highlighter class - highlights SQL with preg and some compromises
 * @Author dzver <dzver@abv.bg>
 * @Copyright GNU v 3.0  
 *********************************************************************/
   
class SQL_Highlighter {
  /*
    protected $colors - key order is important because of highlighting < and >
    chars and not encoding them to &lt; and &gt;
  */
  protected $colors = Array('chars' => 'grey', 'keywords' => 'blue', 'joins' => 'gray', 'functions' => 'violet', 'constants' => 'red');
  /*
    lists are not complete.
  */
  protected $words = Array (
  'keywords' =>
    array('SELECT', 'UPDATE', 'INSERT', 'DELETE', 'REPLACE', 'INTO', 'CREATE', 'ALTER', 'TABLE', 'DROP', 'TRUNCATE', 'FROM',
    'ADD', 'CHANGE', 'COLUMN', 'KEY',
    'WHERE', 'ON', 'CASE', 'WHEN', 'THEN', 'END', 'ELSE', 'AS', 
    'USING', 'USE', 'INDEX', 'CONSTRAINT', 'REFERENCES', 'DUPLICATE',
    'LIMIT', 'OFFSET', 'SET', 'SHOW', 'STATUS', 
    'BETWEEN', 'AND', 'IS', 'NOT', 'OR', 'XOR', 'INTERVAL', 'TOP',
    'GROUP BY', 'ORDER BY', 'DESC', 'ASC', 'COLLATE', 'NAMES', 'UTF8', 'DISTINCT', 'DATABASE',
    'CALC_FOUND_ROWS', 'SQL_NO_CACHE', 'MATCH', 'AGAINST', 'LIKE', 'REGEXP', 'RLIKE',
    'PRIMARY', 'AUTO_INCREMENT', 'DEFAULT', 'IDENTITY', 'VALUES', 'PROCEDURE', 'FUNCTION', 
	'TRAN', 'TRANSACTION', 'COMMIT', 'ROLLBACK', 'SAVEPOINT', 'TRIGGER', 'CASCADE',
	'DECLARE', 'CURSOR', 'FOR', 'DEALLOCATE'
    ),
  'joins' => array('JOIN', 'INNER', 'OUTER', 'FULL', 'NATURAL', 'LEFT', 'RIGHT'),
  'chars' => '/([\\.,\\(\\)<>:=`]+)/i',
  'functions' => array(
    'MIN', 'MAX', 'SUM', 'COUNT', 'AVG', 'CAST', 'COALESCE', 'CHAR_LENGTH', 'LENGTH', 'SUBSTRING',
    'DAY', 'MONTH', 'YEAR', 'DATE_FORMAT', 'CRC32', 'CURDATE', 'SYSDATE', 'NOW', 'GETDATE',
    'FROM_UNIXTIME', 'FROM_DAYS', 'TO_DAYS', 'HOUR', 'IFNULL', 'ISNULL', 'NVL', 'NVL2',
    'INET_ATON', 'INET_NTOA', 'INSTR', 'FOUND_ROWS',
    'LAST_INSERT_ID', 'LCASE', 'LOWER', 'UCASE', 'UPPER',
    'LPAD','RPAD','RTRIM','LTRIM',
    'MD5','MINUTE', 'ROUND',
    'SECOND', 'SHA1', 'STDDEV', 'STR_TO_DATE', 'WEEK'),
   'constants' => '/(\'[^\']*\'|[0-9]+)/i'
  );

  /* 
    $colors must be blank or 
    Array('chars' => '', 'keywords' => '', 'joins' => '', 'functions' => '', 'constants' => '')
  */
  function __construct($colors = 0) {
    if ($colors) $this->colors = $colors;
  }

  public function highlight($sql)
  {
    $sql = str_replace('\\\'', '\\&#039;', $sql);
    foreach($this->colors as $key=>$color)
    {
      if (in_array($key, Array('constants', 'chars'))) {
        $regexp = $this->words[$key];
      }
      else {
        $regexp = '/\\b(' . join("|", $this->words[$key]) . ')\\b/i';
      }
      $sql = preg_replace($regexp, '<span style="color:'.$color."\">$1</span>", $sql);
    }
    return $sql;
  }
}
