<?php
/**

 * @author  Marcos  
 */
class Professor extends TRecord
{
    const TABLENAME = 'ufc_professor';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    use SystemChangeLogTrait;

    private $cidade;
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('siape');
        parent::addAttribute('nome');
        parent::addAttribute('email');
        parent::addAttribute('telefone');
       

    }

    
}