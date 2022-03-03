<?php

use Adianti\Control\TWindow;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\THidden;

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
class AvaliacaoForm extends TWindow
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
        parent::setSize(0.5, null);
        parent::removePadding();
        parent::setTitle('Qual probabilidade de você recomendar ao amigo da faculdade nosso serviços e sistema de atendimento?');
      
        
        $this->setDatabase('estagio');    // defines the database
        $this->setActiveRecord('Avaliacao');   // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_avaliacao');
       
        
        
        // create the form fields
        $id       = new THidden('id');
        $comentario     = new TEntry('comentario');
        $data     = new THidden('data_ava');
        $data->setValue(date('d-m-Y'));
        $nota    = new TRadioGroup('nota');
        $usuario = new THidden('system_user_id');
        $usuario->setValue(TSession::getValue('userid'));
        $nota->setLayout('horizontal');
        $nota->setUseButton();
        $id->setEditable(FALSE);
        $comentario->placeholder = 'Comente aqui quais mudanças teriamos que fazer pra melhorar?';

        $items = ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5' , '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10'];

        $nota->addItems($items);
        
        // add the form fields
        $this->form->addFields([new TLabel('Sendo 0 "nenhum pouco provável e 10 "extremanente provável', 'green')] );
        $this->form->addFields( [$nota] );
        $this->form->addFields( [$comentario] );
        $this->form->addFields(  [$id] );
        $this->form->addFields(  [$usuario] );
        $this->form->addFields(  [$data] );
        
  
        
        // define the form action
        $this->form->addAction('Registrar Avaliação', new TAction(array($this, 'onSave')), 'fa:save green');
       
        // wrap the page content using vertical box
       
        parent::add($this->form);
    }
}