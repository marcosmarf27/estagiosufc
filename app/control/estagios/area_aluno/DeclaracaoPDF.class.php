<?php

use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TPanel;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Template\THtmlRenderer;

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
class DeclaracaoPDF extends TWindow
{
    /**
     * Constructor method
     */
    public function __construct($param)
    {
        parent::__construct();
        parent::setTitle('Declaração de registro e aprovação do estágio');
        parent::removePadding();
        
        // with: 500, height: automatic
        parent::setSize(0.6, null); // use 0.6, 0.4 (for relative sizes 60%, 40%)
        
        $conteudo;
        
        if($param['estagio_id']){
            TTransaction::open('estagio');
            $estagio = new Estagio($param['estagio_id']);


              
            $replaces = [];
            $replaces['nome'] = $estagio->aluno->nome;
            $replaces['tipo'] = $estagio->tipo_estagio->nome;
            $replaces['matricula'] = $estagio->aluno->matricula;
            $replaces['concedente'] = $estagio->concedente->nome;
            $replaces['data_ini'] = $estagio->data_ini;
            $replaces['data_fim'] = $estagio->data_fim;
            $replaces['validador'] = $estagio->validador;
            $replaces['estagio_id'] = $estagio->id;
           
            $html = new THtmlRenderer('app/resources/tutor/template_declaracao.html');
            $html->enableSection('main', $replaces);
            $conteudo = $html->getContents();
         
           
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

    public static function gerarPDF($param){

        TTransaction::open('estagio');
        $estagio = new Estagio($param['estagio_id']);


          
        $replaces = [];
        $replaces['nome'] = $estagio->aluno->nome;
        $replaces['tipo'] = $estagio->tipo_estagio->nome;
        $replaces['matricula'] = $estagio->aluno->matricula;
        $replaces['concedente'] = $estagio->concedente->nome;
        $replaces['data_ini'] = $estagio->data_ini;
        $replaces['data_fim'] = $estagio->data_fim;
        $replaces['validador'] = $estagio->validador;
        $replaces['estagio_id'] = $estagio->id;
       
        $html = new THtmlRenderer('app/resources/tutor/template_declaracao.html');
        $html->enableSection('main', $replaces);
       
     
       
        TTransaction::close();

            $contents = $html->getContents();
            
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', TRUE);
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            // write and open file
            file_put_contents('app/output/declaraco.pdf', $dompdf->output());
            parent::openFile( 'app/output/declaraco.pdf');
            
            // open window to show pdf
      
    }
}