<?php
/**

 * @author  Marcos  
 */
class Documento extends TRecord
{
    const TABLENAME = 'ufc_documento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    use SystemChangeLogTrait;

    private $cidade;
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('tipo_doc');
        parent::addAttribute('obs');
        parent::addAttribute('url');
        parent::addAttribute('estagio_id');
        parent::addAttribute('data_envio');
        parent::addAttribute('system_user_id');
       

    }

    
}