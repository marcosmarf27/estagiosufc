<?php
/**

 * @author  Marcos  
 */
class Concedente extends TRecord
{
    const TABLENAME = 'ufc_concedente';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    const CREATEDAT = 'criacao';
    const UPDATEDAT = 'atualizacao';
    use SystemChangeLogTrait;

    private $cidade;
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
      
        parent::addAttribute('email');
        parent::addAttribute('origem');
       
        parent::addAttribute('telefone');
        parent::addAttribute('cidade_id');
        parent::addAttribute('representante');
        parent::addAttribute('endereco');

        parent::addAttribute('situacao');
        parent::addAttribute('n_convenio');
        parent::addAttribute('criacao');
        parent::addAttribute('atualizacao');
        parent::addAttribute('validade_ini');
        parent::addAttribute('validade_fim');
        parent::addAttribute('tipo');
        parent::addAttribute('arquivo');
        parent::addAttribute('pendencia');
        parent::addAttribute('cnpj');
        parent::addAttribute('cpf');

    }

    function get_cidade()
    {
        // instantiates City, load $this->city_id
        if (empty($this->cidade))
        {
            $this->cidade = new Cidade($this->cidade_id);
        }
        
        // returns the City Active Record
        return $this->cidade;
    }
}