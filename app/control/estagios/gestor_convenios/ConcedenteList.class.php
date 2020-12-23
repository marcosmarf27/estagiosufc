<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Wrapper\BootstrapDatagridWrapper;
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
class ConcedenteList extends TPage
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
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('estagio');        // defines the database
        $this->setActiveRecord('Concedente');       // defines the active record
        $this->addFilterField('nome', 'ilike', 'nome');
        $this->addFilterField('situacao', '=', 'situacao'); // filter field, operator, form field
        $this->setDefaultOrder('id', 'desc');  // define the default order
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_City');
        $this->form->setFormTitle('Lista de Empresas');
        
        $nome = new TEntry('nome');
        $situacao = new TCombo('situacao');
        $situacao->addItems(['1' => 'Não conveniada', '2' => 'Conveniada', '3' => 'Processando', '4' => 'Com problemas', '5'=> 'Na procuradoria']);
        $this->form->addFields( [new TLabel('Name:')], [$nome], [new TLabel('Situação:')], [$situacao] );
        
        
        // add form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addActionLink('Novo',  new TAction(['ConcedenteForm', 'onClear']), 'fa:plus-circle green');
        $this->form->addActionLink('Limpar',  new TAction([$this, 'clear']), 'fa:eraser red');
        
        // keep the form filled with the search data
        $this->form->setData( TSession::getValue('ConcedenteForm_filter_data') );
        
        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";
        
        // creates the datagrid columns
        $col_id    = new TDataGridColumn('id', 'Id', 'right', '5%');
        $col_name  = new TDataGridColumn('nome', 'Name', 'left', '25%');
        $col_situacao  = new TDataGridColumn('situacao', 'Status', 'left', '15%');
        $convenio= new TDataGridColumn('n_convenio', 'Nº processo', 'left', '15%');
        $criacao  = new TDataGridColumn('criacao', 'Data envio', 'left', '20%');
        $ultima  = new TDataGridColumn('atualizacao', 'Última atualziação', 'left', '20%');
        //$cidade = new TDataGridColumn('cidade->nome', 'Cidade', 'center', '10%');
       
        
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_name);
        $this->datagrid->addColumn($col_situacao);
        $this->datagrid->addColumn($convenio);
        $this->datagrid->addColumn($criacao);
        $this->datagrid->addColumn($ultima);

      $col_situacao->setTransformer(array($this, 'Ajustar'));
     
        
        $col_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_name->setAction( new TAction([$this, 'onReload']), ['order' => 'nome']);
        
        $action1 = new TDataGridAction(['ConcedenteForm', 'onEdit'],   ['key' => '{id}'] );
        $action2 = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        $action3 = new TDataGridAction(['ListEstagioEmpresa', 'onReload'],   ['key' => '{id}'] );
        $action4 = new TDataGridAction(['EmailList', 'listarEmails'],   ['email' => '{id}'] );
        
        $this->datagrid->addAction($action1, 'Editar',   'far:edit blue');
        $this->datagrid->addAction($action2, 'Deletar', 'far:trash-alt red');
        $this->datagrid->addAction($action3, 'Ver Estágios', 'fas:eye fa-fw');
        $this->datagrid->addAction($action4, 'Ver E-mails', 'fas:envelope fa-fw');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->enableCounters();
        
        
        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
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

    public function ajustar($value, $object, $row){
        switch ($value) {
            case 1:
                $div = new TElement('span');
                $div->class="label label-warning";
                 $div->style="text-shadow:none; font-size:12px";
                $div->add('Não conveniada');
                return $div;
                break;
            case 2:
                $div = new TElement('span');
                $div->class="label label-success";
                 $div->style="text-shadow:none; font-size:12px";
                $div->add('Empresa Conveniada');
                return $div;
                break;
    
                case 3:
                    $div = new TElement('span');
                    $div->class="label label-primary";
                     $div->style="text-shadow:none; font-size:12px";
                    $div->add('Processando');
                    return $div;
                    break;
    
                    case 4:
                        $div = new TElement('span');
                        $div->class="label label-danger";
                         $div->style="text-shadow:none; font-size:12px";
                        $div->add('Convenio com problemas');
                        return $div;
                        break;
                    case 5:
                        $div = new TElement('span');
                        $div->class="label label-info";
                            $div->style="text-shadow:none; font-size:12px";
                        $div->add('Na procuradoria');
                        return $div;
                        break;
    
                      
             
                    
                
         
        }
    }
    
}
