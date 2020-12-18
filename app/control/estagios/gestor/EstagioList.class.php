<?php

use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TCombo;

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
class EstagioList extends TPage
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

       
        if (isset($param['termo_id'])){

            $dados = new stdClass;
            $dados->id = $param['termo_id'];
            TSession::setValue('EstagioList_filter_data', $dados);
            $termo =  TSession::getValue('EstagioList_filter_data');
           
            $criteria = new TCriteria();
            $criteria->add(new TFilter('id','=', $termo->id));
            $this->setCriteria($criteria);
        }
        
        
        $this->setDatabase('estagio');          // defines the database
        $this->setActiveRecord('Estagio');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
        $this->addFilterField('id', '=', 'id'); 
        $this->addFilterField('(SELECT matricula FROM ufc_aluno WHERE id = ufc_estagio.aluno_id)', '=', 'matricula');
        $this->addFilterField('situacao', '=', 'situacao');// filterField, operator, formField
        $this->addFilterField('aluno_id', '=', 'aluno_id'); // filterField, operator, formField
        
        $this->addFilterField('data_ini', '>=', 'date_from', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        $this->addFilterField('data_fim', '<=', 'date_to', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_estagios');
        $this->form->setFormTitle('Estágios Recebidos');
        
        // create the form fields
        $id        = new TEntry('id');
        $situacao = new TCombo('situacao');
        $matricula = new TEntry('matricula');
        $situacao->addItems([ '1' => 'Em Avaliação', '2' => 'Estágio Aprovado','3' => 'Rescindidos', '4'=> 'Estágio com Problemas']);
        $date_from = new TDate('date_from');
        $date_to   = new TDate('date_to');
        
        $aluno_id = new TDBUniqueSearch('aluno_id', 'estagio', 'Aluno', 'id', 'nome');
        $aluno_id->setMinLength(1);
        $aluno_id->setMask('{nome} ({id})');
        
        // add the fields
        $this->form->addFields( [new TLabel('Id')],          [$id], [new TLabel('Status')],          [$situacao], [new TLabel('Matricula')],          [$matricula]); 
        $this->form->addFields( [new TLabel('Data Estágio (De)')], [$date_from],
                                [new TLabel('Data Término (à)')],   [$date_to] );
        $this->form->addFields( [new TLabel('Aluno')],    [$aluno_id] );
        
        $id->setSize('50%');
        $date_from->setSize('100%');
        $date_to->setSize('100%');
        $date_from->setMask( 'dd/mm/yyyy' );
        $date_to->setMask( 'dd/mm/yyyy' );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('EstagioList_filter_data') );
        
        // add the search form actions
        $this->form->addAction('Procurar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Cadastrar novo termo',  new TAction(['EstagioFormAdmin', 'onClear']), 'fa:plus green');
        $this->form->addActionLink( 'Limpar', new TAction([$this, 'Limpar']), 'fa:eraser red' );
        $this->form->addActionLink( 'Atualizar termos', new TAction([$this, 'carregar']), 'fas:play fa-fw' );
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->height = '500px';
     
        
        // creates the datagrid columns
        $column_id       = new TDataGridColumn('id', 'nº Estágio', 'center', '5%');
        $column_situacao    = new TDataGridColumn('situacao', 'Status', 'center', '20%');
        $column_aluno = new TDataGridColumn('aluno->nome', 'Aluno', 'left', '25%');
        $column_concedente = new TDataGridColumn('concedente->nome', 'Empresa/Instituição', 'left', '25%');
        $column_data_ini     = new TDataGridColumn('data_ini', 'Data Inicio', 'center', '15%');
        $column_data_fim    = new TDataGridColumn('data_fim', 'Data Término', 'center', '15%');
       
       // $column_total    = new TDataGridColumn('total', 'Total', 'right', '20%');
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
      //  $column_total->setTransformer( $format_value );
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_concedente);
        $this->datagrid->addColumn($column_aluno);
     
        $this->datagrid->addColumn($column_data_ini);
        $this->datagrid->addColumn($column_data_fim);
       

        //Transformação que define a situação do estagio 
        $column_situacao->setTransformer( array($this, 'ajustarSituacao'));
        
        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $column_data_ini->setAction(new TAction([$this, 'onReload']), ['order' => 'data_ini']);
        
        // define the transformer method over date
        $column_data_ini->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });
        $column_data_fim->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

       // $action_view   = new TDataGridAction(['SaleSidePanelView', 'onView'],   ['key' => '{id}', 'register_state' => 'false'] );
        $action_edit   = new TDataGridAction(['EstagioFormAdmin', 'onEdit'],   ['key' => '{id}',  'register_state' => 'false', 'nome'=> 'marcos antonio rafael da fonseca ç']);
        $action_edit_a   = new TDataGridAction(['AlunoFormWindow', 'onEdit'],   ['id' => '{aluno_id}',  'register_state' => 'false']);
        $action_edit_c   = new TDataGridAction(['ConcedenteFormWindow', 'onEdit'],   ['id' => '{concedente_id}',  'register_state' => 'false']);
        $action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        $action_aprovar = new TDataGridAction([$this, 'aprovarTermo'],   ['id' => '{id}'] );

        $action_registra_pendencia  = new TDataGridAction(['PendenciaFormList', 'registraPendencia'],   ['estagio_id' => '{id}', 'usuario_id' => '{system_user_id}', 'register_state' => 'false']);
        $action_registra_documento  = new TDataGridAction(['DocumentoFormList', 'registraDocumento'],   ['estagio_id' => '{id}', 'usuario_id' => '{system_user_id}']);
        $action_edit->setLabel('Editar Termo');
        $action_edit->setImage('far:edit blue fa-fw');

        $action_aprovar->setLabel('Aprovar Termo');
        $action_aprovar->setImage('fas:thumbs-up blue fa-fw');
        
        $action_delete->setLabel('Deletar Termo');
        $action_delete->setImage('far:trash-alt red fa-fw');
        
        $action_edit_a->setLabel('Cadastro Aluno');
        $action_edit_a->setImage('far:user blue fa-fw');
        
        $action_edit_c->setLabel('Cadastro Empresa');
        $action_edit_c->setImage('fas:address-card blue fa-fw');

        $action_registra_pendencia->setLabel('Pendências');
        $action_registra_pendencia->setImage('far:edit blue fa-fw');

        $action_registra_documento->setLabel('Documentos');
        $action_registra_documento->setImage('fa:search blue');
        
        
        $action_group = new TDataGridActionGroup('Realizar Ação', 'fa:th');
        
        $action_group->addHeader('>>Edição<<');
        $action_group->addAction($action_edit);
        $action_group->addAction($action_delete);
        $action_group->addSeparator();
        $action_group->addHeader('>>Cadastros<<');
        $action_group->addAction($action_edit_a);
        $action_group->addAction($action_edit_c);
        $action_group->addAction($action_registra_documento);
        $action_group->addHeader('>>Avaliação<<');
        $action_group->addAction($action_registra_pendencia);
        $action_group->addAction($action_aprovar);
        
        // add the actions to the datagrid
        $this->datagrid->addActionGroup($action_group);
        
        
     /*    //$this->datagrid->addAction($action_view, _t('View details'), 'fa:search green fa-fw');
        $this->datagrid->addAction($action_edit, 'Editar Termo',   'far:edit blue fa-fw');
        $this->datagrid->addAction($action_delete, 'Deletar Termo', 'far:trash-alt red fa-fw');
        $this->datagrid->addAction($action_edit_a, 'Cadastro Aluno',   'far:user blue fa-fw');
        $this->datagrid->addAction($action_edit_c, 'Cadastro Empresa', 'fas:address-card blue fa-fw'); */
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }

    public  function Limpar($param)
    {

       
        $this->form->clear();
        
    }

    public function abrir($param){

        TScript::create("Template.closeRightPanel()");
     
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
 if (!($pendencias) and $object->situacao == '4'){

       
    TTransaction::open('estagio');
     



    $estagio = Estagio::find($object->id);
    $estagio->situacao = '2';
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

    $this->carregar();

   
   }

   public static function onClosePanel($param)
   {
       TScript::create("Template.closeRightPanel()");
   }

   public function carregar(){

    AdiantiCoreApplication::loadPage('EstagioList', 'onReload');

   }
   
   
   
   public function aprovarTermo($param){

    TTransaction::open('estagio');
     



    $estagio = Estagio::find($param['id']);
    $estagio->situacao = '2';
    $estagio->store();
 

 
    TTransaction::close();
    $action1 = new TAction(array($this, 'onReload'));
   // $action2 = new TAction(array($this, 'onReload'));
    // define os parâmetros de cada ação
   
    
    // shows the question dialog
    new TMessage('info', 'Termo
    de estágio aprovado', $action1);


   }
   public function link(){

   }
    
}