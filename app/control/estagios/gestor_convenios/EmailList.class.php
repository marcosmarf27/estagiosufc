<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Database\TFilter;
use Adianti\Registry\TSession;
use Adianti\Database\TCriteria;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\THtmlEditor;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Wrapper\BootstrapDatagridWrapper;

/**
 * StandardFormDataGridView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class EmailList extends TPage
{
    protected $form;      // form
    protected $datagrid;  // datagrid
    protected $loaded;
    protected $pageNavigation;  // pagination component
    
    // trait with onSave, onEdit, onDelete, onReload, onSearch...
    use Adianti\Base\AdiantiStandardFormListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct($param)
    {
        parent::__construct();
        if(!empty($param['key'])){
            TSession::delValue(__CLASS__.'emailPorConvenio');
            TSession::setValue(__CLASS__.'emailPorConvenio', $param['key']);
        }
        
        $criteria = new TCriteria();
        $criteria->add(new TFilter('convenio_id','=', TSession::getValue(__CLASS__.'emailPorConvenio')));
        $this->setCriteria($criteria);
        
        
        $this->setDatabase('estagio'); // define the database
        $this->setActiveRecord('Email'); // define the Active Record
        $this->setDefaultOrder('id', 'asc'); // define the default order
        $this->setLimit(-1); // turn off limit for datagrid
        
        // create the form
        $this->form = new BootstrapFormBuilder('form_emaillist');
        
        
        // create the form fields
        $id     = new TEntry('id');
        $conteudo   = new THtmlEditor('conteudo');
        $conteudo->setSize('100%', '600');
        $assunto    = new TEntry('assunto');
        
        // add the form fields
        $this->form->addFields( [new TLabel('ID')],    [$id] );
        $this->form->addFields( [new TLabel('Assunto')],  [$assunto] );
        $this->form->addFields( [new TLabel('E-mail')],  [$conteudo] );

        
       
        
        // define the form actions
       /*  $this->form->addAction( 'Save', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addActionLink( 'Clear',new TAction([$this, 'onClear']), 'fa:eraser red'); */
        
        // make id not editable
        $id->setEditable(FALSE);
        
        // create the datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // add the columns
        $col_id    = new TDataGridColumn('id', 'Id', 'right', '10%');
        $assunto  = new TDataGridColumn('assunto', 'Assunto', 'left', '50%');
        $data_envio  = new TDataGridColumn('data_envio', 'Data', 'left', '20%');
        $para  = new TDataGridColumn('para', 'Enviado para', 'left', '20%');
        
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($assunto);
        $this->datagrid->addColumn($data_envio);
        $this->datagrid->addColumn($para);
        
        $data_envio->setAction( new TAction([$this, 'onReload']),   ['order' => 'data_envio']);
       
        
        // define row actions
        $action1 = new TDataGridAction([$this, 'onEdit'],   ['key' => '{id}'] );
        $action2 = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}'] );
        
        $this->datagrid->addAction($action1, 'Ver E-mail',   'far:envelope blue');
        $this->datagrid->addAction($action2, 'Apagar E-mail', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // wrap objects inside a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        
       
        $vbox->add(TPanelGroup::pack('', $this->datagrid));
        $vbox->add($this->form);
        
        // pack the table inside the page
        parent::add($vbox);
    }
    public function listarEmails(){
      
        
    }
}