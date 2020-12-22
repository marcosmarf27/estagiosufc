<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\THtmlEditor;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Validator\TEmailValidator;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Validator\TRequiredValidator;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Template\THtmlRenderer;

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
class ConcedenteForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_concedente');
        $this->form->setFormTitle('Cadastro de Empresa');
        $this->form->setClientValidation(true);
        
        
        // create the form fields
        $id       = new TEntry('id');
       
        $nome     = new TEntry('nome');
        $n_convenio     = new TEntry('n_convenio');
        $validade_ini = new TDate('validade_ini');
        $validade_ini->setMask('dd/mm/yyyy');
        $validade_ini->setDatabaseMask('yyyy-mm-dd');
        $validade_fim = new TDate('validade_fim');
        $validade_fim->setMask('dd/mm/yyyy');
        $validade_fim->setDatabaseMask('yyyy-mm-dd');
        $situacao = new TCombo('situacao');
        $situacao->addItems(['1' => 'Não conveniada', '2' => 'Conveniada', '3' => 'Processando', '4' => 'Com problemas']);
        $tipo = new TCombo('tipo');
        $tipo->addItems(['1' => 'Empresa/Instituição', '2' => 'Projeto/Bolsa', '3' => 'Profissional único']);
        $cidade_id = new TDBCombo('cidade_id', 'estagio', 'Cidade', 'id', 'nome');
        $cidade_id->enableSearch();
        $representante     = new TEntry('representante');
        $email     = new TEntry('email');
        $telefone     = new TEntry('telefone');
        $cnpj     = new TEntry('cnpj');
        $cnpj->setMask('99.999.999/9999-99');
        $cpf     = new TEntry('cpf');
        $cpf->setMask('999.999.999-99');
        $endereco     = new TEntry('endereco');
        $origem     = new TEntry('origem');
        $arquivo     = new TFile('arquivo');
        $arquivo->enableFileHandling();
        $arquivo->enablePopover();
        $endereco->placeholder = 'Escreva endereço, numero, bairro';
        $pendencia = new THtmlEditor('pendencia');
        $pendencia->setSize('100%', 500);
        
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
        $this->form->addFields( [new TLabel('ID')], [$id],  [new TLabel('Situação')], [$situacao],  [new TLabel('Tipo')], [$tipo]);
        $this->form->addFields( [new TLabel('Nome')], [$nome],  [new TLabel('Origem')], [$origem]  );
        $this->form->addFields( [new TLabel('<b>CPF</b>')], [$cpf] );
        $this->form->addFields( [new TLabel('<b>CNPJ</b>')], [$cnpj] );
        $this->form->addFields( [new TLabel('E-mail')], [$email] );
        $this->form->addFields( [new TLabel('Telefone')], [$telefone] );
        $this->form->addFields( [new TLabel('Representante')], [$representante] );
       
        $this->form->addFields( [new TLabel('Endereço')], [$endereco],  [new TLabel('Cidade')], [$cidade_id] );
        $this->form->addFields( [new TLabel('Documentação Convênio')], [$arquivo] );
       
       

        $this->form->appendPage('Dados Convênio');

        $this->form->addFields( [new TLabel('Nº Convênio')], [$n_convenio] );
        $this->form->addFields( [new TLabel('Data inicio')], [$validade_ini] );
        $this->form->addFields( [new TLabel('Data Término')], [$validade_fim] );

        
        $this->form->appendPage('Parecer do convênio');

        $this->form->addFields( [new TLabel('Avaliação procuradoria')], [$pendencia] );
     
   

      
       
        
        $nome->addValidation( 'nome', new TRequiredValidator);
        //$state_id->addValidation( 'State', new TRequiredValidator);
        
        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addActionLink('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->form->addActionLink('Listar Empresas',  new TAction(array('ConcedenteList', 'onReload')), 'fa:table blue');
        $this->form->addAction('Notificar Empresa', new TAction(array('NotificaForm', 'notificar')), 'fa:envelope blue');
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
          
            
            // store product
            $object = new Concedente();
            $object->fromArray( (array) $data);
            $object->store();
            
            // copy file to target folder
            $this->saveFile($object, $data, 'arquivo', 'files/estagios');
            
          
            
            // send id back to the form
            $data->id = $object->id;
            $this->form->setData($data);
            
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
            TQuickForm::hideField('form_concedente', 'cpf');
            TQuickForm::showField('form_concedente', 'cnpj');
            
           
        }
        else
        {
            TQuickForm::showField('form_concedente', 'cpf');
            TQuickForm::hideField('form_concedente', 'cnpj');
            
         
        }

     
    }

    public function onEdit($param){

        TTransaction::open('estagio');
        
        $dados = new Concedente($param['key']);
        $replaces = [];
        $replaces['nome'] = $dados->nome;
        $replaces['cnpj'] = $dados->cnpj;
       
        $html = new THtmlRenderer('app/resources/tutor/template_pendencia.html');
        $html->enableSection('main', $replaces);
        $template = $html->getContents();
        $dados->pendencia = $template;
      
     



        $this->form->setData($dados);

        TTransaction::close();

    }
}
