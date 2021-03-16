<?php
namespace Kodeine\Acl\Helper;

use Exception;

class FieldException extends Exception
{
    protected $_field;
    public function __construct($message="", $codeStatus=0,$status, Exception $previous=NULL, $field = NULL)
    {
        $this->_field = $field;
        parent::__construct($message, $codeStatus,$status , $previous);
    }
    public function getField()
    {
        return $this->_field;
    }
}