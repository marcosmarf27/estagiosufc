<?php

use Adianti\Control\TPage;
use Adianti\Control\TWindow;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\THtmlEditor;
use Adianti\Widget\Form\TText;

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
//DEFINIÇÃO: classe responsável pela visualziaçãoe e edição de documentos do GESTOR.
class DocumentoFormList extends TPage
{
    protected $form;      // form
    protected $datagrid;  // datagrid
    protected $loaded;
    protected $pageNavigation;  // pagination component
    
    // trait with onSave, onEdit, onDelete, onReload, onSearch...
    use Adianti\Base\AdiantiStandardFormListTrait;
    use Adianti\Base\AdiantiFileSaveTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct($param)
    {
        parent::__construct();
        parent::setTargetContainer('adianti_right_panel');

        if(isset($param['estagio_id']) and isset($param['usuario_id'])){
        TSession::setValue(__CLASS__.'estagio_documento', $param['estagio_id']);
        TSession::setValue(__CLASS__.'usuario_documento', $param['usuario_id']);
        }
       
        //parent::setSize(0.9, 0.9);
        
        $this->setDatabase('estagio'); // define the database
        $this->setActiveRecord('Documento'); // define the Active Record
        $this->setDefaultOrder('id', 'desc'); // define the default order
        $this->setLimit(-1); // turn off limit for datagrid
        $criteria = new TCriteria();
        $criteria->add(new TFilter('estagio_id','=', TSession::getValue(__CLASS__.'estagio_documento')));
        $this->setCriteria($criteria);

        
        // create the form
        $this->form = new BootstrapFormBuilder('form_documentos');
        $this->form->setFormTitle('Documentos do Estágio');
        
        // create the form fields
        $id     = new TEntry('id');
        $estagio_id    = new TEntry('estagio_id');
        $system_user_id     = new TEntry('system_user_id');
        $data_envio = new TDate('data_envio');
        $data_envio->setEditable(FALSE);
        $tipo_doc = new TCombo('tipo_doc');
        $url = new TFile('url');
        $url->setAllowedExtensions( ['pdf'] );
        $url->enableFileHandling();

        $tipo_doc->addItems( ['1' => 'Termo de Estágio Obrigatório',
                                  '2' => 'Termo de Estágio Não Obrigatório',
                                  '3' => 'Aditivo',
                                  '4' => 'Rescisao',
                                  '5' => 'Relatório',
                                  '6' => 'Atestado de Matricula',
                                  '7' => 'Histórico Acadêmico' ]);
    
          
     
        
        // add the form fields
       
        $this->form->addFields([new TLabel('ID')],    [$id] ,  [new TLabel('Usuário')],  [$system_user_id], [new TLabel('Número do Estágio')],  [$estagio_id] );
        $this->form->addFields( [new TLabel('Registro data')],  [$data_envio], [new TLabel('Tipo de Documento')],  [$tipo_doc] );
        $this->form->addFields( [new TLabel('Selecione o arquivo')],    [$url] );

    
       
        
      //  $name->addValidation('Name', new TRequiredValidator);
        
        // define the form actions
        $this->form->addAction( 'Salvar Documento', new TAction([$this, 'onSave']), 'fa:save green');
       // $this->form->addActionLink( 'Entregar novo documento',new TAction([$this, 'onClear']), 'fa:eraser red');
       // $this->form->addActionLink( 'Voltar', new TAction(['EstagioList', 'Limpar']), 'fa:arrow-left');
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

        $dados = new stdClass;
        $dados->estagio_id = TSession::getValue(__CLASS__.'estagio_documento');
        $dados->system_user_id = TSession::getValue(__CLASS__.'usuario_documento');
        $dados->data_envio = date('d/m/Y');
    

        $this->form->setData($dados);
        
        // make id not editable
        $id->setEditable(FALSE);
        $estagio_id->setEditable(FALSE);
        $system_user_id->setEditable(FALSE);
        // create the datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // add the columns
      //  $col_id    = new TDataGridColumn('id', 'Id', 'right', '10%');
        $col_tipo = new TDataGridColumn('tipo_doc', 'Tipo Documento', 'left', '20%');
        $col_url  = new TDataGridColumn('url', 'Descrição ', 'Arquivo', '30%');
        $col_data_envio = new TDataGridColumn('data_envio', 'Data de registro', 'left', '20%');
        
        
     //   $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_tipo);
        $this->datagrid->addColumn($col_url);
        $this->datagrid->addColumn($col_data_envio);
        
        
       // $col_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_data_envio->setAction( new TAction([$this, 'onReload']), ['order' => 'data_envio']);

        $col_tipo->setTransformer( function($value, $object, $row) {

            switch ($value) {
                case 1:
                  
                    return 'TCE obrigatório';
                    break;
                case 2:
                    return 'TCE não  obrigatório';
                break;
                case 3:
                    return 'TCE Aditivo';
                    break;

                case 4:
                        return 'Termo de Rescisão';
                        break;

                case 5:
                 return 'Relatório de Estágio';
                    break;

                 case 6:
                 return 'Atestado de Matricula';
                 break;

                 case 7:
                    return 'Histórico Acadêmico';
                    break;

                 
    
            }

                
           
        });

        $col_url->setTransformer( function($value, $object, $row) {

            

            $action = new TAction( [$this, 'verDoc' ] );
            $action->setParameter('url', $value);

        $b2 = new TActionLink('Ver Dcoumento', $action, 'white', 10, '', 'far:check-square #FEFF00');
        $b2->class='btn btn-success';
        return $b2;
        
           
        });
        
        // define row actions
        $action1 = new TDataGridAction([$this, 'onEdit'],   ['key' => '{id}'] );
        $action2 = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}'] );
        
        $this->datagrid->addAction($action1, 'Editar',   'far:edit blue');
        $this->datagrid->addAction($action2, 'Deletar', 'far:trash-alt red');
        
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

    public function registraDocumento($param){

        TSession::setValue(__CLASS__.'estagio_documento', $param['estagio_id']);
       TSession::setValue(__CLASS__.'usuario_documento', $param['usuario_id']);
        $dados = $this->form->getData();
        $dados->estagio_id = TSession::getValue(__CLASS__.'estagio_documento');
        $dados->system_user_id = TSession::getValue(__CLASS__.'usuario_documento');
        $dados->data_envio = date('d/m/Y');

        $this->form->setData($dados);



        
    }
    public function onClear($param){

        $this->form->clear();

        
      //  TSession::setValue('usuario', $param['id_user']);
        $dados = $this->form->getData();
        $dados->estagio_id = TSession::getValue(__CLASS__.'estagio_documento');
        $dados->system_user_id = TSession::getValue(__CLASS__.'usuario_documento');

        $this->form->setData($dados);



        
    }

    public function verDoc($param){

       parent::openFile( $param['url']);

       /*  $window = TWindow::create('Visualizando documento', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $param['url'];
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show(); */
    }
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

    public function onSave()
    {
        try
        {
            TTransaction::open('estagio');
            
            // form validations
          //  $this->form->validate();
            
            // get form data
            $data   = $this->form->getData();
            $data->data_envio = TDate::convertToMask( $data->data_envio, 'dd/mm/yyyy', 'yyyy-mm-dd');
            
            // store product
            $object = new Documento();
            $object->fromArray( (array) $data);
            $object->store();
            
            // copy file to target folder
            $this->saveFile($object, $data, 'url', 'files/estagios');
            
           // $this->saveFiles($object, $data, 'images', 'files/images', 'ProductImage', 'image', 'product_id');
            
            // send id back to the form
            $data->id = $object->id;
            $this->form->setData($data);
            
            TTransaction::close();
            $action1 = new TAction(array($this, 'onReload'));
   // $action2 = new TAction(array($this, 'onReload'));
    // define os parâmetros de cada ação
   
    
    // shows the question dialog
    new TMessage('info', 'Documento entregue com sucesso!
    de estágio aprovado', $action1);
        }
        catch (Exception $e)
        {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    
}
