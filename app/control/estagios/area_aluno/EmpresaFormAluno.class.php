<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\THidden;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Validator\TEmailValidator;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Validator\TRequiredValidator;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * StandardFormView Registration
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class EmpresaFormAluno extends TPage
{
    protected $form; // form
    
    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;
    use Adianti\Base\AdiantiFileSaveTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('estagio');    // defines the database
        $this->setActiveRecord('Concedente');   // defines the active record
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_concedente_aluno');
        $this->form->setFormTitle('Cadastro de Empresa');
        $this->form->setClientValidation(true);
        
        
        // create the form fields
        $id       = new THidden('id');
       
        $nome     = new TEntry('nome');
       
      
    
     
       
        $tipo = new TCombo('tipo');
        $tipo->addItems(['1' => 'Empresa/Instituição', '2'=> 'Projeto de Extensão' ,'3' => 'Profissional único']);
        $cidade_id = new TDBCombo('cidade_id', 'estagio', 'Cidade', 'id', 'nome');
        $cidade_id->enableSearch();
        $representante     = new TEntry('representante');
        $email     = new TEntry('email');
        $telefone     = new TEntry('telefone');
        $endereco     = new TEntry('endereco');
        $arquivo     = new TFile('arquivo');
        $cnpj     = new TEntry('cnpj');
        $cpf     = new TEntry('cpf');
        $cnpj->setMask('99.999.999/9999-99');
        $cpf->setMask('999.999.999-99');
        $arquivo->enableFileHandling();
        $arquivo->enablePopover();

        $tipo->setChangeAction(new TAction(array($this, 'onChangeType')));
        self::onChangeType( ['_field_value' => '1'] );
      
        
        
     


       
        

        $telefone->setMask('(99)99999-9999');
        $email->addValidation('email', new TEmailValidator);

        /* parent::addAttribute('nome');
        parent::addAttribute('matricula');
        parent::addAttribute('email');
        parent::addAttribute('curso_id');
        parent::addAttribute('telefone');
        parent::addAttribute('cidade_id');
        parent::addAttribute('endereco'); */
        $id->setEditable(FALSE);
        
        // add the form fields

        $this->form->appendPage('Dados básicos');
        $this->form->addFields( [$id]);
        $this->form->addFields( [new TLabel('Nome')], [$nome],  [new TLabel('Tipo')], [$tipo] );
        $this->form->addFields( [new TLabel('E-mail')], [$email], [new TLabel('Telefone')], [$telefone] );
        $this->form->addFields( [new TLabel('<b>CPF</b>')], [$cpf] );
        $this->form->addFields( [new TLabel('<b>CNPJ</b>')], [$cnpj] );
        $this->form->addFields( [new TLabel('Representante')], [$representante] );
       
        $this->form->addFields( [new TLabel('Endereço')], [$endereco],  [new TLabel('Cidade')], [$cidade_id] );
        $this->form->addFields( [new TLabel('Documentação Convênio')], [$arquivo] );
       
       

     
   

      
       
        
        $nome->addValidation( 'nome', new TRequiredValidator);
        //$state_id->addValidation( 'State', new TRequiredValidator);
        
        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addActionLink('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');
       
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'ConcedenteList'));
        $vbox->add($this->form);
        parent::add($vbox);
    }

    public function onSave()
    {
        try
        {
            TTransaction::open('estagio');
            
            // form validations
          //  $this->form->validate();
            
            // get form data
            $data   = $this->form->getData();
            $data->situacao = '3';
            $data->n_convenio = 'Aguardando parecer da procuradoria';
          
            
            // store product
            $object = new Concedente();
            $object->fromArray( (array) $data);
            $object->store();
            
            // copy file to target folder
            $this->saveFile($object, $data, 'arquivo', 'files/estagios');
        
            
          
            
            // send id back to the form
            $data->id = $object->id;
            $this->form->setData($data);
            SystemNotification::register(1, 'Novo convênio recebido', 'Avaliar Convênio', 'class=ConcedenteList', 'Avaliar', 'fas fa-user-tie');
            
            TTransaction::close();
      
  
   
    
    // shows the question dialog
    new TMessage('info', 'Empresa cadastrada com sucesso!');
        }
        catch (Exception $e)
        {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public static function onChangeType($param)
    {
        if ($param['_field_value'] == '1' or $param['_field_value'] == '2')
        {
            TQuickForm::hideField('form_concedente_aluno', 'cpf');
            TQuickForm::showField('form_concedente_aluno', 'cnpj');
            
           
        }
        else
        {
            TQuickForm::showField('form_concedente_aluno', 'cpf');
            TQuickForm::hideField('form_concedente_aluno', 'cnpj');
            
         
        }

     
    }
}
