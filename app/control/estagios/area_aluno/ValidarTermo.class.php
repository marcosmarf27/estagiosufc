<?php



use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Database\TFilter;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TText;
use Adianti\Database\TCriteria;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\THidden;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Widget\Template\THtmlRenderer;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Wrapper\BootstrapDatagridWrapper;

/**
 * SaleList
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ValidarTermo extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();

       if(!empty($param['validador'])){

        $filter = new TFilter('validador', '=', $param['validador'] );

       }else{
           //coloca um valor qualquer pra não exbir nenhum termo
        $param['validador'] = 6576456657676;
        $filter = new TFilter('validador', '=', $param['validador'] );
       }
    
       
        $criteria = new TCriteria();

        $criteria->add($filter);
        $this->setCriteria($criteria);
        
        $this->setDatabase('estagio');          // defines the database
        $this->setActiveRecord('Estagio');         // defines the active record
        $this->setDefaultOrder('id', 'desc'); 
        $this->addFilterField('validador', '=', 'validador');  
        
        $this->form = new BootstrapFormBuilder('form_validador');
        $this->form->setFormTitle('Insira o código para validar o estágio');
        
        // create the form fields
        $validador        = new TEntry('validador');// defines the default order
        $this->form->addFields( [new TLabel('Código de Valiadação')],    [$validador] );
        $validador->setSize('100%');

        $this->form->addAction('Consultar/validar', new TAction([$this, 'onSearch']), 'fa:user-check');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Detalhes', '<b>Nº doc:</b> {id} <br> <b>Estágio:</b> {tipo_estagio->nome} <br> <b>Nome:</b> {aluno->nome} <br> <b>Curso:</b> {aluno->curso->nome} <br> <b>Ano:</b> {ano} - <b>Mês:</b> {mes}');
       
     
        
      
        $column_situacao    = new TDataGridColumn('situacao', 'STATUS', 'center', '15%');
        $column_tipo        = new TDataGridColumn('tipo_estagio->nome', 'ESTÁGIO', 'left', '15%');
        $column_concedente  = new TDataGridColumn('concedente->nome', 'CONCEDENTE', 'left', '25%');
        $column_aluno  = new TDataGridColumn('aluno->nome', 'ALUNO', 'left', '25%');
        $column_data_ini    = new TDataGridColumn('data_ini', 'INICIO', 'center', '10%');
        $column_data_fim    = new TDataGridColumn('data_fim', 'TÉRMINO', 'center', '10%');
        $column_tipo->setDataProperty('style','font-weight: bold');
      
       
  
        
      
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_concedente);
        $this->datagrid->addColumn($column_tipo);
        $this->datagrid->addColumn($column_aluno);
        $this->datagrid->addColumn($column_data_ini);
        $this->datagrid->addColumn($column_data_fim);
       
       
       

        
        $column_situacao->setTransformer( array($this, 'ajustarSituacao'));
        
        //define ordem do datagrid
        $column_data_ini->setAction(new TAction([$this, 'onReload']), ['order' => 'data_ini']);
        
        // define the transformer method over date
        $column_situacao->setTransformer( array($this, 'ajustarSituacao'));
        
        $column_data_ini->setTransformer( function($value, $object, $row) 
        {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        
        $column_data_fim->setTransformer( function($value, $object, $row) 
        {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

  
       
      
      
        


        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
    
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid));
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }



       


        
       
      

        


        
      


   

    

 








public  function Limpar($param)
    {

       
        $this->form->clear();
        
    }

public function abrir($param){

        
     
    }

public function ajustarSituacao($value, $object, $row){

 

    $pendencias = Pendencia::where('estagio_id', '=', $object->id)->where('status', '=', 'N')->load();

        if($pendencias)
        {
        
        TTransaction::open('estagio');
     
        $estagio = Estagio::find($object->id);
        $estagio->situacao = '4';
        $estagio->store();

        TTransaction::close();
        }



   
        switch ($object->situacao) 
        {
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