<?php
/**

 * @author  Marcos  
 */
class Email extends TRecord
{
    const TABLENAME = 'ufc_email';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('de');
        parent::addAttribute('para');
        parent::addAttribute('assunto');
        parent::addAttribute('conteudo');
        parent::addAttribute('convenio_id');
        parent::addAttribute('systema_user_id');
        parent::addAttribute('estagio_id');
        parent::addAttribute('data_envio');
       


        
    }

    
}