<?php

use Adianti\Control\TWindow;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;

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
class ConcedenteFormWindow extends TWindow
{
    protected $form; // form
    
    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(0.9, 0.9);
        
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
        $situacao->addItems(['1' => 'Não conveniada', '2' => 'Conveniada']);
        $tipo = new TCombo('tipo');
        $tipo->addItems(['1' => 'Empresa/Instituição', '2' => 'Projeto/Bolsa', '3' => 'Profissional único']);
        $cidade_id = new TDBCombo('cidade_id', 'estagio', 'Cidade', 'id', 'nome');
        $cidade_id->enableSearch();
        $representante     = new TEntry('representante');
        $email     = new TEntry('email');
        $telefone     = new TEntry('telefone');
        $endereco     = new TEntry('endereco');
        $endereco->placeholder = 'Escreva endereço, numero, bairro';
        

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
        $this->form->addFields( [new TLabel('Nome')], [$nome] );
        $this->form->addFields( [new TLabel('E-mail')], [$email] );
        $this->form->addFields( [new TLabel('Telefone')], [$telefone] );
        $this->form->addFields( [new TLabel('Representante')], [$representante] );
       
        $this->form->addFields( [new TLabel('Endereço')], [$endereco],  [new TLabel('Cidade')], [$cidade_id] );
       
       

        $this->form->appendPage('Dados Convênio');

        $this->form->addFields( [new TLabel('Nº Convênio')], [$n_convenio] );
        $this->form->addFields( [new TLabel('Data inicio')], [$validade_ini] );
        $this->form->addFields( [new TLabel('Data Término')], [$validade_fim] );
     
   

      
       
        
        $nome->addValidation( 'nome', new TRequiredValidator);
        //$state_id->addValidation( 'State', new TRequiredValidator);
        
        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addActionLink('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->form->addActionLink('Listar Empresas',  new TAction(array('ConcedenteList', 'onReload')), 'fa:table blue');
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'ConcedenteList'));
        $vbox->add($this->form);
        parent::add($vbox);
    }
}
