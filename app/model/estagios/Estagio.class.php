<?php
/**

 * @author  Marcos  
 */
class Estagio extends TRecord
{
    const TABLENAME = 'ufc_estagio';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    const CREATEDAT = 'criacao';
    const UPDATEDAT = 'atualizacao';
    use SystemChangeLogTrait;
    private $aluno;
    private $concedente;
    private $professor;
    private $tipo_estagio;
    private $pagamento;
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('aluno_id');
        parent::addAttribute('concedente_id');
        parent::addAttribute('professor_id');
        parent::addAttribute('tipo_estagio_id');
        parent::addAttribute('apolice');
        parent::addAttribute('data_ini_a');
        parent::addAttribute('data_fim_a');
        parent::addAttribute('valor_transporte');
        parent::addAttribute('data_ini');
        parent::addAttribute('data_fim');
        parent::addAttribute('pagamento_id');
        parent::addAttribute('atividades');
        parent::addAttribute('situacao');
        parent::addAttribute('valor_bolsa');
        parent::addAttribute('carga_horaria');
        parent::addAttribute('system_user_id');
        parent::addAttribute('ano');
        parent::addAttribute('mes');
        parent::addAttribute('estagio_ref');
        parent::addAttribute('data_rescisao');
        parent::addAttribute('motivo_res');
        parent::addAttribute('editado');
        parent::addAttribute('criacao');
        parent::addAttribute('atualizacao');
        parent::addAttribute('validador');
      

        

    }

    function get_aluno()
    {
        // instantiates City, load $this->city_id
        if (empty($this->aluno))
        {
            $this->aluno = new Aluno($this->aluno_id);
        }
        
        // returns the City Active Record
        return $this->aluno;
    }
    function get_aluno_curso()
    {
        // instantiates City, load $this->city_id
        if (empty($this->aluno))
        {
            $this->aluno = new Aluno($this->aluno_id);
        }
        
        // returns the City Active Record
        return $this->aluno->curso;
    }
    function get_professor()
    {
        // instantiates City, load $this->city_id
        if (empty($this->professsor))
        {
            $this->professor = new Professor($this->professor_id);
        }
        
        // returns the City Active Record
        return $this->professor;
    }
    function get_concedente()
    {
        // instantiates City, load $this->city_id
        if (empty($this->concedente))
        {
            $this->concedente = new Concedente($this->concedente_id);
        }
        
        // returns the City Active Record
        return $this->concedente;
    }
    function get_tipo_estagio()
    {
        // instantiates City, load $this->city_id
        if (empty($this->tipo_estagio))
        {
            $this->tipo_estagio = new Tipo($this->tipo_estagio_id);
        }
        
        // returns the City Active Record
        return $this->tipo_estagio;
    }
    function get_pagamento()
    {
        // instantiates City, load $this->city_id
        if (empty($this->pagamento))
        {
            $this->pagamento = new Pagamento($this->pagamento_id);
        }
        
        // returns the City Active Record
        return $this->pagamento;
    }

    public function getHorarios(){

      return  $horarios = Horario::where('estagio_id', '=', $this->id)->load();
                                
                                 


    }

    public function getDocumentos(){

        return  $documentos = Documento::where('estagio_id', '=', $this->id)->load();

        
    }
    
    
}