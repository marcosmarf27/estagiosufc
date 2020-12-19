<?php
class PublicView extends TPage
{
    public function __construct()
    {
        parent::__construct();
        
        $html1 = new THtmlRenderer('app/resources/system_welcome_pt.html');
        $html1->enableSection('main', array());
      

       
      
        
        $panel1 = new TPanelGroup('BEM-VINDO AO NOVO SISTEMA DE ESTÁGIOS!');
        $panel1->add($html1);
        
    
        
        $vbox = TVBox::pack($panel1);
        $vbox->style = 'display:block; width: 100%';
        
        parent::add($panel1); 
    }
}