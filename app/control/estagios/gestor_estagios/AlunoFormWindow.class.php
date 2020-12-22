<?php

use Adianti\Control\TWindow;

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
class AlunoFormWindow extends TWindow
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
        parent::setSize(0.8, null);
        parent::removePadding();
        parent::setTitle('Informações Aluno');
        parent::disableEscape();
        
        $this->setDatabase('estagio');    // defines the database
        $this->setActiveRecord('Aluno');   // defines the active record
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_aluno');
    
        $this->form->setClientValidation(true);
        
        
        // create the form fields
        $id       = new TEntry('id');
        $matricula       = new TEntry('matricula');
        $nome     = new TEntry('nome');
        $cidade_id = new TDBCombo('cidade_id', 'estagio', 'Cidade', 'id', 'nome');
        $curso_id = new TDBCombo('curso_id', 'estagio', 'Curso', 'id', 'nome');
        $email     = new TEntry('email');
        $telefone     = new TEntry('telefone');
        $endereco     = new TEntry('endereco');
        $endereco->placeholder = 'Escreva endereço, numero, bairro';

        $telefone->setMask('(99)99999-9999');
        $email->addValidation('email', new TEmailValidator);
        $cidade_id->enableSearch();

        /* parent::addAttribute('nome');
        parent::addAttribute('matricula');
        parent::addAttribute('email');
        parent::addAttribute('curso_id');
        parent::addAttribute('telefone');
        parent::addAttribute('cidade_id');
        parent::addAttribute('endereco'); */
        $id->setEditable(FALSE);
        
        // add the form fields
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel('Matricula', 'red')], [$matricula] );
        $this->form->addFields( [new TLabel('Nome', 'red')], [$nome] );
        $this->form->addFields( [new TLabel('Cidade', 'red')], [$cidade_id], [new TLabel('Curso', 'red')], [$curso_id] );
        $this->form->addFields( [new TLabel('E-mail', 'red')], [$email] );
        $this->form->addFields( [new TLabel('Telefone', 'red')], [$telefone] );
        $this->form->addFields( [new TLabel('Endereço', 'red')], [$endereco] );
       
        
        $nome->addValidation( 'nome', new TRequiredValidator);
        //$state_id->addValidation( 'State', new TRequiredValidator);
        
        // define the form action
        $this->form->addAction('Cadastrar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addActionLink('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->form->addActionLink('Listar Alunos',  new TAction(array('AlunoList', 'onReload')), 'fa:table blue');
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
      
        $vbox->add($this->form);
        parent::add($vbox);
    }
}
