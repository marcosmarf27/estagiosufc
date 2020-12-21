<?php

use Adianti\Control\TPage;
use Adianti\Control\TWindow;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Form\TCombo;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\THtmlEditor;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Form\THtmlEditorSimples;

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
class PendenciaFormList extends TWindow
{
    protected $form;      // form
    protected $datagrid;  // datagrid
    protected $loaded;
    protected $pageNavigation;  // pagination component
    
   
    use Adianti\Base\AdiantiStandardFormListTrait;
    
    
    public function __construct($param)
    {
        parent::__construct();
        parent::setSize(0.9, 0.9);

        if(isset($param['estagio_id']) and isset($param['usuario_id'])){
          TSession::setValue('estagio_pendencia', $param['estagio_id']);
          TSession::setValue('usuario_pendencia', $param['usuario_id']);
          }
        
    
       
       
        
        $this->setDatabase('estagio'); // define the database
        $this->setActiveRecord('Pendencia'); // define the Active Record
        $this->setDefaultOrder('id', 'desc'); // define the default order
        $this->setLimit(-1); // turn off limit for datagrid
        $criteria = new TCriteria();
        $criteria->add(new TFilter('estagio_id','=', TSession::getValue('estagio_pendencia')));
        $this->setCriteria($criteria);
   

        
        // create the form
        $this->form = new BootstrapFormBuilder('form_pendencias');
        $this->form->setFormTitle('Resgistrar Pendências');
        
        // create the form fields
        $id     = new TEntry('id');
        $status    = new TEntry('status');
        $estagio_id    = new TEntry('estagio_id');
        $system_user_id     = new TEntry('system_user_id');
        $data_reg = new TDate('data_reg');
        $tipo_pendencia = new TDBCombo('tipo_pendencia', 'estagio', 'Solucao', 'id', 'nome');
        $descricao = new TText('descricao');
        $descricao->setSize('100%', 100);
        $descricao->placeholder = 'Resuma aqui os problemas encontrados';
        $parecer = new THtmlEditor('parecer');
        $parecer->setSize('100%', 350);
       
       

        

      
        

      
     
    
        
        // add the form fields
       
        $this->form->addFields([new TLabel('ID')],    [$id] ,  [new TLabel('Usuário')],  [$system_user_id], [new TLabel('Número do Estágio')],  [$estagio_id] );
        $this->form->addFields( [new TLabel('Registro data')],  [$data_reg], [new TLabel('Tipo de Pendência')],  [$tipo_pendencia] );
        $this->form->addFields( [new TLabel('Solução')],    [$descricao],  [new TLabel('Status')],    [$status] );

        $label = new TLabel('Fundamento/Descrição', '#7D78B6', 12, 'bi');
        $label->style='text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        
        $this->form->addContent( [$label] );
        
        $this->form->addFields( [$parecer] );
       
        
      
        
        // define the form actions
        $this->form->addAction( 'Registrar Pendência', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addActionLink( 'Limpar',new TAction([$this, 'onClear']), 'fa:eraser red');

        $change_action = new TAction(array($this, 'onChangeAction'));
        $tipo_pendencia->setChangeAction($change_action);
      
        // make id not editable
        $id->setEditable(FALSE);
        $estagio_id->setEditable(FALSE);
        $status->setEditable(FALSE);
        $system_user_id->setEditable(FALSE);
        
        
        
        
        // create the datagrid
        
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // add the columns
        $col_id    = new TDataGridColumn('id', 'Id', 'right', '10%');
        $col_tipo = new TDataGridColumn('tipo_pendencia', 'Tipo Pendência', 'left', '20%');
        $col_descricao  = new TDataGridColumn('descricao', 'Descrição ', 'left', '60%');
        $col_data_reg  = new TDataGridColumn('data_reg', 'Data de registro', 'left', '20%');
        
        
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_tipo);
        $this->datagrid->addColumn($col_descricao);
        $this->datagrid->addColumn($col_data_reg);
        
        $col_tipo->setTransformer( function($value, $object, $row) {

         
                TTransaction::open('estagio');
                
                $solucao = Solucao::find($value);
                return $solucao->nome;

                TTransaction::close();
              
         
      });

       $col_id->setTransformer( function($value, $object, $row) {
                if($object->status == 'S'){

                  $row->style = 'background-color: #98FB98';
                  return $value;
                }else{

                  return $value;
                }
      });
        
        $col_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_data_reg->setAction( new TAction([$this, 'onReload']), ['order' => 'data_reg']);
        
        // define row actions
        $action1 = new TDataGridAction([$this, 'onEdit'],   ['key' => '{id}'] );
        $action2 = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}'] );
        $action3 = new TDataGridAction([$this, 'resolver'], ['key' => '{id}'] );
        
        $this->datagrid->addAction($action1, 'Editar',   'far:edit blue');
        $this->datagrid->addAction($action2, 'Deletar', 'far:trash-alt red');
        $this->datagrid->addAction($action3, 'resolver Pendência', 'fa:fas fa-stamp');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // wrap objects inside a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'EstagioList'));
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid));
        
        // pack the table inside the page
        parent::add($vbox);
    }

    public function registraPendencia($param){

          

        TSession::setValue('estagio_pendencia', $param['estagio_id']);
        TSession::setValue('usuario_pendencia', $param['usuario_id']);
      
        $dados = $this->form->getData();
        $dados->estagio_id = TSession::getValue('estagio_pendencia');
        $dados->system_user_id = TSession::getValue('usuario_pendencia');
        $dados->status = 'N';
     

        $this->form->setData($dados);

        

       



        
    }
    public function onClear($param){

        $this->form->clear();

        
      //  TSession::setValue('usuario', $param['id_user']);
        $dados = $this->form->getData();
        $dados->estagio_id = TSession::getValue('estagio_pendencia');
        $dados->system_user_id = TSession::getValue('usuario_pendencia');
        $dados->status = 'N';

        $this->form->setData($dados);



        
    }

  
    
    public function resolver($param){

      TTransaction::open('estagio');

      $pendencia = Pendencia::find($param['key']);

      if($pendencia->status == 'S'){

        $pendencia->status = 'N';
        $pendencia->store();
      }else{

        
        $pendencia->status = 'S';
        $pendencia->store();
      }

      $action1 = new TAction(array('EstagioList', 'onReload'));
  
     
      
      // shows the question dialog
      new TQuestion('Pendência corrigida! Deseja Voltar pra tela principal de estágios?', $action1);


      TTransaction::close();
    }

    public static function onChangeAction($param){

    try {
      //code...
      TTransaction::open('estagio');
       
        $solucao = Solucao::find($param['_field_value']);
    
       // echo "<pre>"; print_r($param); echo "</pre>";

        $dados = new stdClass;
        $dados->descricao = $solucao->solucao;
        $dados->parecer = $solucao->problema;
        TForm::sendData($param['_form_name'], $dados);
        
        TTransaction::close();
            } catch (Exception $th) {
              //throw $th;
        new TMessage('error', 'Por favor selecione uma opção');
              
            } 
     
          





     

   




    }

    
}
