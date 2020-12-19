<?php

use Adianti\Registry\TSession;

/**
 * WelcomeView
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class WelcomeView extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        $html1 = new THtmlRenderer('app/resources/system_welcome_pt.html');
      

       
        $html1->enableSection('main', array());
      
        
        $panel1 = new TPanelGroup('BEM-VINDO AO NOVO SISTEMA DE ESTÁGIOS!');
        $panel1->add($html1);
        
    
        
        $vbox = TVBox::pack($panel1);
        $vbox->style = 'display:block; width: 100%';

        TTransaction::open('estagio');
    

    $aluno = Aluno::where('system_user_id', '=', TSession::getValue('userid'))
                                 ->where('status', '=', 'S')
                                 ->load();
                                // var_dump($aluno);

                                 if (!($aluno)){
                                    $action1 = new TAction(array('AlunoForm', 'abrir'));
      

     
        
                                    // shows the question dialog
                                    new TQuestion('Seu CADASTRO DE ALUNO ESTÁ INCOMPLETO! Gostaria de completar seu cadastro?', $action1);
                                 }
    TTransaction::close();

        
    
    

        
        // add the template to the page
        parent::add( $vbox );
    }
}
