<?php

use Adianti\Control\TWindow;
use Adianti\Widget\Form\TUniqueSearch;

/**
 * StandardDataGridView Listing
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ListEstagioEmpresa extends TWindow
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    // trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct($param)
    {
        parent::__construct();
        parent::setSize(0.9, 0.9);
        $criteria = new TCriteria();
        $criteria->add(new TFilter('concedente_id','=', $param['key']));
        $this->setCriteria($criteria);
        
        $this->setDatabase('estagio');        // defines the database
        $this->setActiveRecord('Estagio');       // defines the active record
        $this->addFilterField('aluno_id', 'like', 'aluno_id'); // filter field, operator, form field
        $this->setDefaultOrder('id', 'asc');  // define the default order
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_estagios_empresa');
        $this->form->setFormTitle('Estágios na Empresa');
        
        $aluno_id = new TDBUniqueSearch('aluno_id', 'estagio', 'Aluno', 'id', 'nome');
        $aluno_id->setMinLength(1);
        $aluno_id->setMask('{nome} ({id})');
        $this->form->addFields( [new TLabel('Nome:')], [$aluno_id] );
        
        // add form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
       
        $this->form->addActionLink('Clear',  new TAction([$this, 'clear']), 'fa:eraser red');
        
        // keep the form filled with the search data
        $this->form->setData( TSession::getValue('ListEstagioAluno_filter_data') );
        
        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";
        
        // creates the datagrid columns
        $column_id       = new TDataGridColumn('id', 'nº Estágio', 'center', '5%');
        $column_situacao    = new TDataGridColumn('situacao', 'Status', 'center', '20%');
        $column_aluno = new TDataGridColumn('aluno->nome', 'Aluno', 'left', '20%');
       
        $column_data_ini     = new TDataGridColumn('data_ini', 'Data Inicio', 'center', '20%');
        $column_data_fim    = new TDataGridColumn('data_fim', 'Data Término', 'center', '20%');
       
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_situacao);
    
        $this->datagrid->addColumn($column_aluno);
     
        $this->datagrid->addColumn($column_data_ini);
        $this->datagrid->addColumn($column_data_fim);
        $column_situacao->setTransformer( array($this, 'ajustarSituacao'));
       
        
        $column_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'id']);
      
        
    

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        
        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', 'EstagioList'));
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        // add the table inside the page
        parent::add($vbox);
    }
    
    /**
     * Clear filters
     */
    function clear()
    {
        $this->clearFilters();
        $this->onReload();
    }

    public function ajustarSituacao($value, $object, $row){

 

        $pendencias = Pendencia::where('estagio_id', '=', $object->id)->where('status', '=', 'N')->load();
    
        if($pendencias){
            
         TTransaction::open('estagio');
         
    
    
    
        $estagio = Estagio::find($object->id);
        $estagio->situacao = '4';
        $estagio->store();
       
    
        
        TTransaction::close();
    
       
    
     }
    
     
    
        
       
        
    
        
    
    
        switch ($object->situacao) {
            case 1:
                $div = new TElement('span');
                $div->class="label label-primary";
                 $div->style="text-shadow:none; font-size:12px";
                $div->add('Em Avaliação');
                return $div;
                break;
            case 2:
                $div = new TElement('span');
                $div->class="label label-success";
                 $div->style="text-shadow:none; font-size:12px";
                $div->add('Estágio Aprovado');
                return $div;
                break;
    
                case 3:
                    $div = new TElement('span');
                    $div->class="label label-danger";
                     $div->style="text-shadow:none; font-size:12px";
                    $div->add('Rescindido');
                    return $div;
                    break;
    
                    case 4:
                        $div = new TElement('span');
                        $div->class="label label-warning";
                         $div->style="text-shadow:none; font-size:12px";
                        $div->add('Estágio com problemas');
                        return $div;
                        break;
    
                        case 5:
                            $div = new TElement('span');
                            $div->class="label label-danger";
                             $div->style="text-shadow:none; font-size:12px";
                            $div->add('Cancelado');
                            return $div;
                            break;
             
                    
                
         
        }
       
    
    
       }
}
