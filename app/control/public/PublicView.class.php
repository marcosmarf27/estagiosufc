<?php
class PublicView extends TPage
{
    public function __construct()
    {
        parent::__construct();
        
        $iframe = new TElement('iframe');
        $iframe->id = "iframe_external";
        $iframe->src = "http://dmontier.pro.br/balcao/";
        $iframe->frameborder = "0";
        $iframe->scrolling = "yes";
        $iframe->width = "100%";
        $iframe->height = "900px";
        
        parent::add($iframe); 
    }
}