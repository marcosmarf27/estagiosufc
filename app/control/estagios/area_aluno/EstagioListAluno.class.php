<?php

use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TText;

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
class EstagioListAluno extends TPage
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

       
    
        
        $criteria = new TCriteria();
        $criteria->add(new TFilter('system_user_id','=', TSession::getValue('userid')));
        $this->setCriteria($criteria);
        
        $this->setDatabase('estagio');          // defines the database
        $this->setActiveRecord('Estagio');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
     
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Detalhes', '<b>Nº doc:</b> {id} <br> <b>Estágio:</b> {tipo_estagio->nome} <br> <b>Nome:</b> {aluno->nome} <br> <b>Curso:</b> {aluno->curso->nome} <br> <b>Ano:</b> {ano} - <b>Mês:</b> {mes}');
       // $this->datagrid->height = '500px';
     
        
        // creates the datagrid columns
      //  $column_id       = new TDataGridColumn('id', 'nº Estágio', 'center', '5%');
        $column_situacao    = new TDataGridColumn('situacao', 'Status', 'center', '20%');
      //  $column_aluno = new TDataGridColumn('aluno->nome', 'Aluno', 'left', '20%');
        $column_tipo = new TDataGridColumn('tipo_estagio->nome', 'TCE tipo', 'left', '20%');
        $column_concedente = new TDataGridColumn('concedente->nome', 'Concedente', 'left', '20%');
        $column_data_ini     = new TDataGridColumn('data_ini', 'Data Inicio', 'center', '15%');
        $column_data_fim    = new TDataGridColumn('data_fim', 'Data Término', 'center', '15%');
        $column_tipo->setDataProperty('style','font-weight: bold');
       
  
        
        // add the columns to the DataGrid
       // $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_situacao);
        $this->datagrid->addColumn($column_concedente);
       // $this->datagrid->addColumn($column_aluno);
        $this->datagrid->addColumn($column_tipo);
       
        $this->datagrid->addColumn($column_data_ini);
        $this->datagrid->addColumn($column_data_fim);
       

        //Transformação que define a situação do estagio 
        $column_situacao->setTransformer( array($this, 'ajustarSituacao'));
        
        // creates the datagrid column actions
      //  $column_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
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

  
        $action_aditivo = new TDataGridAction([$this, 'gerarAditivo'],   ['key' => '{id}', 'estagio'=> '{id}', 'register_state' => 'false'] );
        $action_relatorio = new TDataGridAction([$this, 'gerarRelatorio'],   ['key' => '{id}', 'estagio_id' => '{id}', 'usuario_id' => '{system_user_id}', 'register_state' => 'false'] );
        $action_rescisao = new TDataGridAction([$this, 'gerarRescisao'],   ['key' => '{id}', 'register_state' => 'false'] );
        $action_edit = new TDataGridAction(['EstagioForm', 'onEdit'],   ['key' => '{id}', 'estagio_edit' => '{estagio_ref}', 'register_state' => 'false'] );
        $action_ver = new TDataGridAction(['PendenciaFormListAluno', 'registraPendencia'],   ['key' => '{id}', 'estagio_id' => '{id}',  'usuario_id' => '{system_user_id}', 'register_state' => 'false'] );
        $action_doc = new TDataGridAction([$this, 'entregarDoc'],   ['key' => '{id}', 'estagio_id' => '{id}', 'usuario_id' => '{system_user_id}', 'register_state' => 'false'] );
        
        $action_edit->setDisplayCondition([$this, 'displayAcao']);
        $action_relatorio->setDisplayCondition([$this, 'displayAcaoR']);
        $action_aditivo->setDisplayCondition([$this, 'displayAcaoA']);
        $action_rescisao->setDisplayCondition([$this, 'displayAcaoRE']);
        $action_ver->setDisplayCondition([$this, 'displayAcaoVer']);
      

        $this->datagrid->addAction($action_aditivo, '<b>Termo de Aditivo</b> - Registrar Aditivo', 'far:clone green');
        $this->datagrid->addAction($action_relatorio, '<b>Relatório</b> - Entregar relatório', 'fas:book fa-fw');
       $this->datagrid->addAction($action_rescisao, '<b>Rescisão</b> - Registrar Rescisão', 'fa:power-off orange'); 
       $this->datagrid->addAction($action_edit, '<b>Editar</b> - Informe as novos dados', 'far:edit blue fa-fw');
       $this->datagrid->addAction($action_ver, '<b>Ver</b> - Ver pendências/soluções', 'fas:eye fa-fw');
       $this->datagrid->addAction($action_doc, '<b>Documentos</b> - Entregar documentos complementares', 'fas:file-upload fa-fw');


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

    public function gerarAditivo($param){


        $action1 = new TAction(array($this, 'gerarAditivoEfetivo'));
       // $action2 = new TAction(array($this, 'onAction2'));
        // define os parâmetros de cada ação
        $action1->setParameter('estagio', $param['estagio']);
//$action2->setParameter('parameter', 2);
        
        // shows the question dialog
        new TQuestion('Gostaria de registrar um aditivo para esse termo ?', $action1);

       


        
       
       // AdiantiCoreApplication::loadPage('AlunoForm', 'Editar', $param);
       // TScript::create("__adianti_load_page('engine.php?class=AlunoForm&method=Editar');");

        

    }

    public function gerarRelatorio($param){


        
        $action1 = new TAction(array('DocumentoFormListAluno', 'registraDocumento'));
       // $action2 = new TAction(array($this, 'onAction2'));
        // define os parâmetros de cada ação
        $action1->setParameter('estagio_id', $param['estagio_id']);
        $action1->setParameter('usuario_id', $param['usuario_id']);
//$action2->setParameter('parameter', 2);
        
        // shows the question dialog
        new TQuestion('Gostaria de entregar o relatório para esse termo de estágio ?', $action1);


    }

    public function entregarDoc($param){


        
        $action1 = new TAction(array('EntregaDocumentoAluno', 'registraDocumento'));
       // $action2 = new TAction(array($this, 'onAction2'));
        // define os parâmetros de cada ação
        $action1->setParameter('estagio_id', $param['estagio_id']);
        $action1->setParameter('usuario_id', $param['usuario_id']);
//$action2->setParameter('parameter', 2);
        
        // shows the question dialog
        new TQuestion('Deseja entregar novo documento ?', $action1);


    }

        public function displayAcao( $object )
    {
        if ($object->tipo_estagio_id == '3' and is_null($object->editado))
        {
            return TRUE;
        }
        return FALSE;
    }
    public function displayAcaoR( $object )
    {
        if ($object->tipo_estagio_id == '1' or $object->tipo_estagio_id == '2' or $object->editado == 'S')
        {
            return TRUE;
        }
        return FALSE;
    }

    public function displayAcaoA( $object )
    {
        if ($object->tipo_estagio_id == '1' or $object->tipo_estagio_id == '2' or $object->editado == 'S')
        {
            return TRUE;
        }
        return FALSE;
    }
    public function displayAcaoRE( $object )
    {
        if ($object->tipo_estagio_id == '1' or $object->tipo_estagio_id == '2' or $object->editado == 'S')
        {
            return TRUE;
        }
        return FALSE;
    }

    public function displayAcaoVer( $object )
    {
        if ($object->situacao == '4')
        {
            return TRUE;
        }
        return FALSE;
    }
    

    /* public function gerarRescisao($param){

        $action1 = new TAction(array($this, 'gerarRescisaoEfetivo'));
        // $action2 = new TAction(array($this, 'onAction2'));
         // define os parâmetros de cada ação
         $action1->setParameter('key', $param['key']);
       
 //$action2->setParameter('parameter', 2);
         
         // shows the question dialog
         new TQuestion("Gostaria realmente de <b>Rescindir</b>  esse estágio?", $action1);

    }


 */

public static function gerarRescisao( $param )
{
try{

    TTransaction::open('estagio');
    $estagio = new Estagio($param['key']);

    if($estagio){
        if ($estagio->situacao == '3'){
            throw new Exception('Termo de Estágio já rescindido!');
            exit;
        }
    }
    TTransaction::close();
    // input fields
    $name   = new TText('motivo_res');
    $key = new THidden('key');
    $key->setValue($param['key']);
  
  
    
    $form = new BootstrapFormBuilder('input_form');
    $form->addFields( [new TLabel('Motivo')],     [$name] );
    $form->addFields( [$key] );
  
    
    // form action
    $form->addAction('Confirmar', new TAction(array(__CLASS__, 'gerarRescisaoEfetivo')), 'fa:save green');
    
    // show input dialot
    new TInputDialog('Informe o motivo da rescisão:', $form);

} catch (Exception $e) // in case of exception
{
    // shows the exception error message
    new TMessage('error', $e->getMessage());
    
    // undo all pending operations
    TTransaction::rollback();
}
}
    public function gerarAditivoEfetivo($param){

        TTransaction::open('estagio');

        $estagio = Estagio::find($param['estagio']);
        $estagio->estagio_ref = $estagio->id;
        $estagio->situacao = '1';
        $estagio->tipo_estagio_id  = '3';
        $estagio->editado = '';

        unset($estagio->id);
        $estagio->store();
        TTransaction::close();
        new TMessage('info', 'Agora bastar <b>EDITAR</> o termo de aditivo com as novas informações.');
        AdiantiCoreApplication::loadPage('EstagioListAluno', 'onReload', $param);
        

    }

    public function gerarRelatorioEfetivo($param){

    }

    public static function gerarRescisaoEfetivo($param){


        TTransaction::open('estagio');
        $estagio = new Estagio($param['key']);

        $estagio->situacao = '3';
        $estagio->data_rescisao = date('Y-m-d');
        $estagio->motivo_res = $param['motivo_res'];
        $estagio->store();

        TTransaction::close();
        AdiantiCoreApplication::loadPage('EstagioListAluno', 'onReload');

    }

    public  function Limpar($param)
    {

       
        $this->form->clear();
        
    }

    public function abrir($param){

        
     
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
    $this->carregar();


   }

   public function carregar(){

    AdiantiCoreApplication::loadPage('EstagioListAluno', 'onReload');

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
    
}