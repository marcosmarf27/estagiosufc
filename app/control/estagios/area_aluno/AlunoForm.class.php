<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\THidden;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TQuestion;
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
class AlunoForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait;
        
    function __construct()
    {
        parent::__construct();
        $this->setDatabase('estagio');   
        $this->setActiveRecord('Aluno');
        
        $this->form = new BootstrapFormBuilder('form_aluno');
        $this->form->setFormTitle('Cadastro de aluno');
        $this->form->setClientValidation(true);
        
      
        
        
        // create the form fields
        $id       = new THidden('id');
        $status       = new THidden('status');
        $status->setValue('S');
        
        $userid = TSession::getValue('userid');
        $system_user_id       = new THidden('system_user_id');
        $system_user_id->setValue($userid);
       
        
        $matricula       = new TEntry('matricula');
        $nome     = new TEntry('nome');
        $cidade_id = new TDBCombo('cidade_id', 'estagio', 'Cidade', 'id', 'nome');
        $curso_id = new TDBCombo('curso_id', 'estagio', 'Curso', 'id', 'nome');
        $email     = new TEntry('email');
        $telefone     = new TEntry('telefone');
        $endereco     = new TEntry('endereco');
        $endereco->placeholder = 'Escreva endereço, numero, bairro';
      
        $telefone->setMask('(99)99999-9999');
        $matricula->setMask('9999999');
        $email->addValidation('email', new TEmailValidator);
        $cidade_id->enableSearch();

      
        $id->setEditable(FALSE);
        
        // add the form fields
        $this->form->addFields( [$id] );
        $this->form->addFields( [new TLabel('Nome')], [$nome], [new TLabel('Matricula')], [$matricula] );
        $this->form->addFields( [new TLabel('Cidade')], [$cidade_id], [new TLabel('Curso')], [$curso_id] );
        $this->form->addFields( [new TLabel('E-mail')], [$email] );
        $this->form->addFields( [new TLabel('Telefone')], [$telefone] );
        $this->form->addFields( [new TLabel('Endereço')], [$endereco] );
        $this->form->addFields( [$status] );
        $this->form->addFields(  [$system_user_id] );
       
        
        $nome->addValidation( 'nome', new TRequiredValidator);
        $matricula->addValidation( 'Matricula', new TRequiredValidator);
        $cidade_id->addValidation( 'Cidade', new TRequiredValidator);
        $email->addValidation( 'E-mail', new TRequiredValidator);
        $telefone->addValidation( 'Telefone', new TRequiredValidator);
        $curso_id->addValidation( 'Curso', new TRequiredValidator);
        //$state_id->addValidation( 'State', new TRequiredValidator);
        
        // define the form action
        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addActionLink('Limpar',  new TAction(array($this, 'onClear')), 'fa:eraser red');
       // $this->form->addActionLink('Listar Alunos',  new TAction(array('AlunoList', 'onReload')), 'fa:table blue');
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'AlunoList'));
        $vbox->add($this->form);
        parent::add($vbox);
    }

    public function abrir(){
        TTransaction::open('estagio');
        $user = new SystemUser(TSession::getValue('userid'));
       
                  
                                                                              
                  $aluno = new Aluno;                                                              
                $aluno->nome = $user->name;
                $aluno->email =$user->email;
                $aluno->system_user_id =  TSession::getValue('userid');
                
                $aluno->store();
              
                    
                $aluno_cadastrado = Aluno::where('system_user_id', '=', TSession::getValue('userid'))->load();
         /*    echo '<pre>';
            var_dump($aluno_cadastrado);
            
            echo '</pre>'; */
            
             
                if ($aluno_cadastrado){
        $dados = new stdClass;
        $dados->id =   $aluno_cadastrado[0]->id;
        $dados->nome =  $aluno_cadastrado[0]->nome;
        $dados->email = $aluno_cadastrado[0]->email;
      
        $this->form->setdata($dados);
                }
        TTransaction::close();


    }

    public function Editar($param){

        TTransaction::open('estagio');

        $aluno_cadastrado = Aluno::where('system_user_id', '=', TSession::getValue('userid'))->load();

        if($aluno_cadastrado){

            $this->form->setData($aluno_cadastrado[0]);



        }else{

            $action1 = new TAction(array('AlunoForm', 'abrir'));
            new TQuestion('Seu CADASTRO DE ALUNO ESTÁ INCOMPLETO! Gostaria de completar seu cadastro?', $action1);
        }

        TTransaction::close();

    


    }
}
