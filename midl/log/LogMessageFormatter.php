<?php
namespace midl\log;

class LogMessageFormatter
{

    /**
     * Example format:
     * [{REMOTE_ADDR} - {TIME[d.m.Y H:i:s O]}] {LOG_LEVEL} {MESSAGE} \n {BACKTRACE[5]}
     *
     * {BACKTRACE[int]} => "int" is the limit number for debug_backtrace function
     *
     * @var string
     */
    protected $format;

    /**
     *
     * @var array
     */
    protected $vars = ["BACKTRACE" => true, "MESSAGE" => true, "TIME" => true];

    /**
     *
     * @param string $format
     */
    public function __construct($format = null)
    {
        $this->format = $format;
    }

    /**
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     *
     * @param string $format
     * @return void
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     *
     * @param string $varName
     * @param string|int|float|bool|callable $varValue
     * @return void
     * @throws \InvalidArgumentException
     */
    public function addVar($varName, $varValue)
    {
        if (!is_string($varName))
            throw new \InvalidArgumentException("Variable name must be string.");
        
        if (!is_scalar($varValue) && !is_callable($varValue))
            throw new \InvalidArgumentException("Variable value must be scalar or callable.");
        
        $this->vars[$varName] = $varValue;
    }

    /**
     *
     * @param string|\Exception $message
     * @param array $customVars [optional] Custom variables, ["VAR_NAME" => VAR_VALUE]
     *        VAR_VALUE: string|int|float|bool|callable(LogMessageFormatter $formatter, string $varName)
     * @return string Formatted message
     */
    public function format($message, array $customVars = [])
    {
        if (!$this->format)
            return $message;
        
        $vars = [];
        preg_match_all('/{([^}\[]+)(\[.+\])?}/', $this->format, $vars, PREG_OFFSET_CAPTURE);
        
        if (!$vars || !$vars[1])
            return $message;
        
        $formattedMessage = $this->getFormat();
        $offset = 0;
        
        foreach ($vars[1] as $index => $var) {
            
            $varName = $var[0];
            $varValue = "";
            
            if (isset($this->vars[$varName])) {
                
                if ($varName === "BACKTRACE") {
                    
                    if ($message instanceof \Exception)
                        $varValue = preg_replace("/(\r\n|\n|\r).+{main}$/", "", $message->getTraceAsString());
                    else {
                        if (isset($vars[2][$index][0]))
                            $limit = (int)substr($vars[2][$index][0], 1, -1);
                        else
                            $limit = null;
                        
                        $varValue = $this->getTraceAsString($limit);
                    }
                } elseif ($varName === "MESSAGE") {
                    
                    $varValue = $message instanceof \Exception ? $message->getMessage() : (string)$message;
                } elseif ($varName === "TIME") {
                    
                    if (isset($vars[2][$index][0]))
                        $timeFormat = substr($vars[2][$index][0], 1, -1);
                    else
                        $timeFormat = "d.m.Y H:i:s O";
                    
                    $varValue = date($timeFormat);
                } else {
                    if (is_callable($this->vars[$varName]))
                        $varValue = (string)call_user_func($this->vars[$varName], $this, $varName);
                    else
                        $varValue = (string)$this->vars[$varName];
                }
            } elseif (isset($customVars[$varName])) {
                
                if (is_callable($customVars[$varName]))
                    $varValue = (string)call_user_func($customVars[$varName], $this, $varName);
                
                elseif (is_scalar($customVars[$varName]))
                    $varValue = (string)$customVars[$varName];
            } elseif (isset($_SERVER[$varName]) && is_scalar($_SERVER[$varName])) {
                
                $varValue = $_SERVER[$varName];
            }
            
            $varNameLength = strlen($vars[0][$index][0]);
            $start = $offset + $vars[0][$index][1];
            
            $formattedMessage = substr_replace($formattedMessage, $varValue, $start, $varNameLength);
            
            $offset += strlen($varValue) - $varNameLength;
        }
        
        return $formattedMessage;
    }

    /**
     *
     * @return string
     */
    protected function getTraceAsString($limit)
    {
        $value = "";
        
        $limit = $limit === null ? null : $limit + 2;
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit);
        
        if (count($backTrace) > 2) {
            $backTrace = array_slice($backTrace, 2);
            
            $backTraceItems = [];
            
            foreach ($backTrace as $i => $item) {
                $file = isset($item["file"]) ? $item["file"] : "";
                $line = isset($item["line"]) ? $item["line"] : "";
                $class = isset($item["class"]) ? $item["class"] : "";
                $type = isset($item["type"]) ? $item["type"] : "";
                $function = isset($item["function"]) ? $item["function"] : "";
                
                $backTraceItems[] = "#$i $file($line): {$class}{$type}{$function}()";
            }
            
            $value = implode(PHP_EOL, $backTraceItems);
        }
        
        return $value;
    }
}