<?php

use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TPanel;
use Adianti\Widget\Container\TPanelGroup;

/**
 * SingleWindowView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class Status extends TWindow
{
    /**
     * Constructor method
     */
    public function __construct($param)
    {
        parent::__construct();
        parent::setTitle('Resultado da análise da procuradoria');
        parent::removePadding();
        
        // with: 500, height: automatic
        parent::setSize(0.6, null); // use 0.6, 0.4 (for relative sizes 60%, 40%)
        
        $conteudo;
        if($param['convenio_id']){
            TTransaction::open('estagio');
            $convenio = new Concedente($param['convenio_id']);
            $conteudo = $convenio->pendencia;
            TTransaction::close();
           
        }
        
       
        $panel = new TPanelGroup;
        $panel->add($conteudo);
        
      
        
        $panel->addFooter('Mais informações no whatsapp <b>(88) 3411-9226</b>');
        
        // wrap the page content using vertical box
   
        
        parent::add($panel);            
    }

    public function abrir(){

    }
}