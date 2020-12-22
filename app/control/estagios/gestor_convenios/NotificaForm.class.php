<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\THtmlEditor;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Validator\TRequiredValidator;
use Adianti\Wrapper\BootstrapFormBuilder;
use Dompdf\FrameDecorator\Text;

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
class NotificaForm extends TWindow
{
    protected $form; 
    // form
   
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
        $this->setActiveRecord('Email');   // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_email');
        $this->form->setFormTitle('Enviar E-mail');
        $this->form->setClientValidation(true);
        
        // create the form fields
        $id       = new TEntry('id');
        $convenio_id = new THidden('convenio_id');
        $de     = new THidden('de');
        $para     = new TEntry('para');
        $assunto     = new TEntry('assunto');
        $conteudo     = new THtmlEditor('conteudo');
        $data_envio = new TDate('data_envio');
        $data_envio->setMask('dd/mm/yyyy');
        $data_envio->setDatabaseMask('yyyy-mm-dd');
        $conteudo->setSize('100%', '450');
       
        $id->setEditable(FALSE);
        
        // add the form fields
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel('Envio')],  [$data_envio] );
        $this->form->addFields( [new TLabel('Enviar Para', 'red')], [$para] );
        $this->form->addFields( [new TLabel('Assunto', 'red')], [$assunto] );
        $this->form->addFields( [new TLabel('E-mail', 'red')], [$conteudo] );
        $this->form->addFields([$de], [$convenio_id]);
        
        

        
      
       
        ;
       // $this->form->setData(TSession::getValue('dadosEmail'));
        
     
        
        // define the form action
        $this->form->addAction('Enviar e-mail', new TAction(array($this, 'enviarEmail')), 'fa:envelope green');
       // $this->form->addActionLink('Clear',  new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add($this->form);
        parent::add($vbox);
        
    }

    public function enviarEmail($param){

        $dados = TSession::getValue('dadosEmail');
        $dadosAtivos = $this->form->getData();
        $dados->conteudo = $dadosAtivos->conteudo;
        
        MailService::send( $dados->para, $dados->assunto, $dados->conteudo, 'html' );
       
        TTransaction::open('estagio');
        $email = new Email;
        $email->fromArray((array)$dados);
        $email->store();
        $this->form->setData($dados);
        TTransaction::close();

        new TMessage('info', 'E-mail enviado com sucesso');
       



    }
    public function notificar($param){

        if($param)
        {
          
           
            $dados = new stdClass;
            $dados->para = $param['email'];
            $dados->assunto = 'Notificação importante - Central de Estágios UFC';
            $dados->conteudo = $param['pendencia'];
            $dados->data_envio = date('Y-m-d');
            $dados->de = 'Central de Estágios';
            $dados->convenio_id = $param['id'];
           
            TSession::setValue('dadosEmail', $dados);

            $this->form->setData($dados);
        }
        
    }
}
