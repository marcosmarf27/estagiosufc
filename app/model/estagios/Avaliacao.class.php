<?php
/**

 * @author  Marcos  
 */
class Avaliacao extends TRecord
{
    const TABLENAME = 'avaliacao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('data_ava');
        parent::addAttribute('nota');
        parent::addAttribute('comentario');
        parent::addAttribute('system_user_id');
   

    }
}