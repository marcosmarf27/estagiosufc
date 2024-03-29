<?php

use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Wrapper\BootstrapFormBuilder;
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
class ConvenioList extends TPage
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
        $this->addFilterField('nome', 'ilike', 'nome'); // filter field, operator, form field
        $this->setDefaultOrder('id', 'desc');  // define the default order
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_convenio');
        $this->form->setFormTitle('Lista de Empresas');
        
        $nome = new TEntry('nome');
        $this->form->addFields( [new TLabel('Nome:')], [$nome] );
        
        // add form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
       
        $this->form->addActionLink('Limpar',  new TAction([$this, 'clear']), 'fa:eraser red');
        $this->form->addActionLink('Solicitar Convênio',  new TAction(['EmpresaFormAluno', 'abrir']), 'fa:plus blue');
        
        // keep the form filled with the search data
        $this->form->setData( TSession::getValue('ConvenioList_filter_data') );
        
        // creates the DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = "100%";
        $this->datagrid->datatable= 'true';
        
        // creates the datagrid columns
       
        $col_name       = new TDataGridColumn('nome', 'RAZÃO SOCIAL', 'left', '20%');
        $col_situacao   = new TDataGridColumn('situacao', 'STATUS', 'left', '15%');
        $cnpj           = new TDataGridColumn('cnpj', 'CNPJ', 'left', '10%');
        $convenio       = new TDataGridColumn('n_convenio', 'Nº PROCESSO', 'center', '10%');
        $validade_ini   = new TDataGridColumn('validade_ini', 'INICIO VIGÊNCIA', 'center', '15%');
        $data_envio     = new TDataGridColumn('criacao', 'ENVIADO EM', 'center', '15%');
        $atualizacao    = new TDataGridColumn('atualizacao', 'MOVIMENTADO EM', 'center', '15%');
       
      

       
       
        
       // $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_name);
        $this->datagrid->addColumn($col_situacao);
        $this->datagrid->addColumn($cnpj);
        $this->datagrid->addColumn($atualizacao);
        $this->datagrid->addColumn($data_envio);
        $this->datagrid->addColumn($validade_ini);
        $this->datagrid->addColumn($convenio);
       
        $verparecer = new TDataGridAction(['Status', 'abrir'],   ['convenio_id' => '{id}', 'register_state' => 'false'] );
        
        $verparecer->setDisplayCondition([$this, 'displayParecer']);
        
        $this->datagrid->addAction($verparecer, '<b>Ver</b> - parecer/resultado', 'fas:file fa-fw');
        

        $col_situacao->setTransformer(array($this, 'Ajustar'));

        $validade_ini->setTransformer( function($value) 
        {
            if(!empty($value)){
                $date = new DateTime($value);
                return $date->format('d/m/Y');
            }else{
                 return '';
            }
         
        });

        $atualizacao->setTransformer( function($value) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $data_envio->setTransformer( function($value) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
     
       
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->enableCounters();
        
        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       
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

    public function displayParecer( $object )
    {
        if ($object->situacao == '4' and !empty($object->pendencia))
        {
            return TRUE;
        }
            return FALSE;
    }
    
}
