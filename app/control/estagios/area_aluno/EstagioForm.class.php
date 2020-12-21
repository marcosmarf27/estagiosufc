<?php

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TText;

/**
 * CustomerFormView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class EstagioForm extends TPage
{
    private $form; // form
    private $horarios;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();
        $this->form = new BootstrapFormBuilder('form_estagio');
        $this->form->setFormTitle('Cadastro de Estágios');
    
        
        
     

     
        
        $code        = new TEntry('id');
        $editado = new THidden('editado');
        //aqui o form só lista o aluno da sessão
        $filter_a = new TCriteria;
        $userid = TSession::getValue('userid');
        $filter_a->add(new TFilter('system_user_id', '=', $userid));
        $aluno_id    = new TDBUniqueSearch('aluno_id', 'estagio', 'Aluno', 'id', 'nome', 'nome',  $filter_a);
        $aluno_id->setMinLength(0);
        
        //Aqui Form só lista as empresas conveniadas
        $filter_c = new TCriteria;
        $filter_c->add(new TFilter('situacao', '=', '2'));
        $concedente_id    = new TDBUniqueSearch('concedente_id', 'estagio', 'Concedente', 'id', 'nome', 'nome',  $filter_c);
        $concedente_id->setMinLength(0);
        
      
        $professor_id    = new TDBUniqueSearch('professor_id', 'estagio', 'Professor', 'id', 'nome');
        $professor_id->setMinLength(0);
        $tipo_estagio_id = new TDBCombo('tipo_estagio_id', 'estagio', 'Tipo', 'id', 'nome');
        $apolice      = new TEntry('apolice');
        $data_ini_a  = new TDate('data_ini_a');
        $data_fim_a   = new TDate('data_fim_a');
        $valor_transporte      = new TEntry('valor_transporte');
        $data_ini  = new TDate('data_ini');
        $data_fim   = new TDate('data_fim');
        $pagamento_id = new TDBCombo('pagamento_id', 'estagio', 'Pagamento', 'id', 'nome');
        $atividades = new TText('atividades');
        $carga_horaria       = new TEntry('carga_horaria');
        $ano       = new TEntry('ano');
        $mes       = new TEntry('mes');
        $valor_bolsa       = new TEntry('valor_bolsa');
        
        // placeholders dos campos
        $aluno_id->placeholder = 'Digite para buscar...';
        $concedente_id->placeholder = 'Digite para buscar...';
        $professor_id->placeholder = 'Digite para buscar...';
        $atividades->placeholder =' Descreva aqui todas atividades que serão executadas no estágio';
        $data_ini->placeholder = 'clique aqui';
        $data_fim->placeholder = 'clique aqui';
        $valor_bolsa->placeholder = '0.000,00';
        $carga_horaria->placeholder = '00:00';
        

        //aqui exibe ou não campos referentes a estágio não obrigatório
        $tipo_estagio_id->setChangeAction(new TAction(array($this, 'onChangeType')));
        self::onChangeType( ['_field_value' => '1'] );

        
       
       
        $code->setEditable(FALSE);
        $ano->setEditable(FALSE);
        $mes->setEditable(FALSE);
        $aluno_id->setEditable(FALSE);
       
        //data do estagio
        $data_ini->setMask('dd/mm/yyyy');
        $data_fim->setMask('dd/mm/yyyy');
        $data_ini->setDatabaseMask('yyyy-mm-dd');
        $data_fim->setDatabaseMask('yyyy-mm-dd');
        $valor_bolsa->setNumericMask(2, ',', '.', true);
       
        //data apolice
        $data_ini_a->setMask('dd/mm/yyyy');
        $data_fim_a->setMask('dd/mm/yyyy');
        $data_ini_a->setDatabaseMask('yyyy-mm-dd');
        $data_fim_a->setDatabaseMask('yyyy-mm-dd');
        $valor_transporte->setNumericMask(2, ',', '.', true);
        $valor_transporte->style = "text-align: left";
        
        
        //ajusta e garante que o formato da carga seja 00:00
        $carga_horaria->setMask('99:99');
        $change_action_carga = new TAction(array($this, 'cargaAjustar'));
        $carga_horaria->setExitAction($change_action_carga);


        
        
        
    

       
        
        $this->form->appendPage('Dados básicos');
        $this->form->addFields( [ new TLabel('Código do Estágio') ],      [ $code ], [ new TLabel('Ano do Estágio') ],      [ $ano ], [ new TLabel('Mês do Estágio') ],      [ $mes ] );
        $this->form->addFields( [ new TLabel('Aluno') ],      [ $aluno_id ] );
        $this->form->addFields( [ new TLabel('Empresa') ],      [ $concedente_id ] );
        $this->form->addFields( [ new TLabel('Orientador') ],      [ $professor_id ] );
        $this->form->addFields( [ new TLabel('Inicio do Estágio') ],      [ $data_ini ], [ new TLabel('Fim do Estágio') ],      [ $data_fim ] );
        $this->form->addFields( [ new TLabel('Tipo de Estágio') ],      [ $tipo_estagio_id ] );
        $this->form->addFields( [ new TLabel('Nº Seguro') ],      [ $apolice ], [ new TLabel('<b>Inicio do Apólice</b>') ],      [ $data_ini_a ], [ new TLabel('Fim do Apólice') ],      [ $data_fim_a ]  );
        $this->form->addFields( [ new TLabel('Auxílio Transporte (por mês)') ],      [ $valor_transporte ],    [ $editado ] );
        $this->form->addFields( [ new TLabel('Tipo de Contraprestação') ],      [ $pagamento_id ], [ new TLabel('Valor da Bolsa R$') ],      [ $valor_bolsa ], [ new TLabel('Carga Horária (Semanal)') ],      [ $carga_horaria ] );
       
        
     
      
        $this->form->appendPage('Plano de Atividades');
        $this->form->addFields( [ new TLabel('Plano de Atividades') ],      [ $atividades ] );
  
        
        $this->form->appendPage('Horários');
        $dia_semana = new TCombo('dia_semana[]');
        $dia_semana->setSize('100%');
        $dia_semana->addItems( ['1' => 'Segunda',
                                  '2' => 'Terça-Feira',
                                  '3' => 'Quarta-feira',
                                  '4' => 'Quinta-feira',
                                  '5' => 'Sexta-Feira',
                                  '6' => 'Sabádo',
                                  '7' => 'Domingo' ]);
        
        $turno_manha_ini = new TEntry('turno_manha_ini[]');
        $turno_manha_fim = new TEntry('turno_manha_fim[]');
        $turno_tarde_ini = new TEntry('turno_tarde_ini[]');
        $turno_tarde_fim = new TEntry('turno_tarde_fim[]');
        $turno_noite_ini = new TEntry('turno_noite_ini[]');
        $turno_noite_fim = new TEntry('turno_noite_fim[]');
        $total_dia = new TEntry('total_dia[]');
        
        $turno_manha_ini->placeholder = '00:00';
        $turno_manha_fim->placeholder = '00:00';
        $turno_tarde_ini->placeholder = '00:00';
        $turno_tarde_fim->placeholder = '00:00';
        $turno_noite_ini->placeholder = '00:00';
        $turno_noite_fim->placeholder = '00:00';
       
       
        $turno_manha_ini->setSize('100%');
        $turno_manha_fim->setSize('100%');
        $turno_tarde_ini->setSize('100%');
        $turno_tarde_fim->setSize('100%');
        $turno_noite_ini->setSize('100%');
        $turno_noite_fim->setSize('100%');

        $turno_manha_ini->setMask('99:99');
        $turno_manha_fim->setMask('99:99');
        $turno_tarde_ini->setMask('99:99');
        $turno_tarde_fim->setMask('99:99');
        $turno_noite_ini->setMask('99:99');
        $turno_noite_fim->setMask('99:99');

        $total_dia->setSize('100%');

        $exit_action = new TAction(array($this, 'onExitAction'));
        $turno_manha_fim->setExitAction($exit_action);
        $turno_tarde_fim->setExitAction($exit_action);
        $turno_noite_fim->setExitAction($exit_action);


        
        $this->horarios = new TFieldList;
        $this->horarios->generateAria();
        $this->horarios->width = '100%';
        $this->horarios->name  = 'horarios_list';
        $this->horarios->addField( '<b>Dia da Semana</b>', $dia_semana, ['width' => '16%']);
        $this->horarios->addField( '<b>Manhã Inicio</b>', $turno_manha_ini , ['width' => '12%']);
        $this->horarios->addField( '<b>Manhã Término</b>', $turno_manha_fim, ['width' => '12%'] );
        $this->horarios->addField( '<b>Tarde Inicio</b>', $turno_tarde_ini, ['width' => '12%']);
        $this->horarios->addField( '<b>Tarde término</b>', $turno_tarde_fim, ['width' => '12%']);
        $this->horarios->addField( '<b>Noite Inicio</b>', $turno_noite_ini, ['width' => '12%']);
        $this->horarios->addField( '<b>Noite termino</b>', $turno_noite_fim, ['width' => '12%']);
        $this->horarios->addField( '<b>Qtd Horas dia</b>', $total_dia, ['width' => '12%']);
        
        $this->form->addField($dia_semana);
        $this->form->addField($turno_manha_ini);
        $this->form->addField($turno_manha_fim);
        $this->form->addField($turno_tarde_ini);
        $this->form->addField($turno_tarde_fim);
        $this->form->addField($turno_noite_ini);
        $this->form->addField($turno_noite_fim);
        $this->form->addField($total_dia);
       
        //insere lista de campos no form
        $this->form->addContent( [ $this->horarios ] );
      
    
      

     

        
        
        $this->form->appendPage('Documentos');
        $tipo_doc = new TCombo('tipo_doc[]');
        $tipo_doc->setSize('100%');
        $tipo_doc->addItems( ['1' => 'Termo de Estágio Obrigatório',
                                  '2' => 'Termo de Estágio Não Obrigatório',
                                  '3' => 'Aditivo',
                                  '4' => 'Rescisao',
                                  '5' => 'Relatório',
                                  '6' => 'Atestado de Matricula',
                                  '7' => 'Histórico Acadêmico' ]);
        
       
        $url = new TFile('url[]');
        $url->setSize('100%');
        $url->setDisplayMode('file');
        $url->setAllowedExtensions(['pdf']);
    
      
        $change_action = new TAction(array($this, 'onChangeAction_file'));
        $tipo_doc->setChangeAction($change_action);
        
        $this->documentos = new TFieldList;
        $this->documentos->generateAria();
        $this->documentos->width = '100%';
        $this->documentos->name  = 'documentos_list';
        $this->documentos->addField( '<b>Tipo de Documento</b>', $tipo_doc, ['width' => '20%']);
        $this->documentos->addField( '<b>Documento</b>', $url,  ['width' => '80%'] );
      
        $this->form->addField($tipo_doc);
        $this->form->addField($url);
      

        $this->form->addContent( [ $this->documentos ] );
        
        //validações    
        $aluno_id->addValidation( 'Aluno', new TRequiredValidator );
        $professor_id->addValidation( 'Professor', new TRequiredValidator );
        $concedente_id->addValidation( 'Empresa', new TRequiredValidator);
        $tipo_estagio_id->addValidation( 'Tipo de Estágio', new TRequiredValidator);
        $concedente_id->addValidation( 'Empresa', new TRequiredValidator);
        $data_ini->addValidation( 'Data de Inicio', new TRequiredValidator);
        $data_fim->addValidation( 'Data de Término', new TRequiredValidator);
        $carga_horaria->addValidation( 'Carga Horária', new  TMinLengthValidator, array(4));
        $tipo_doc->addValidation( 'Documentos', new TRequiredListValidator);
        $carga_horaria->addValidation( 'Carga Horária(00:00)', new TRequiredValidator);
        $atividades->addValidation( 'Plano de Atividades', new TRequiredValidator);
        $dia_semana->addValidation( 'Horários', new TRequiredValidator);
       
        $this->form->addAction( 'Salvar Termo', new TAction([$this, 'onSave'], [ 'static' => '1']), 'fa:save green' );
       
       
        parent::add($this->form);
     
    }

      
     
      
       
      
       
    
      


    



        
      
      
    

   
      
     
      
      
        

      
    
       
       
        
        
       
        
        
     
    
       
    
    /**
     * method onSave
     * Executed whenever the user clicks at the save button
     */
    public  function onSave($param)
    {
        try
        {

        

            TTransaction::open('estagio');
           
            TSession::getValue('userid');

            $dados = $this->form->getData();

            $this->form->validate();
            
            $dados->system_user_id=  TSession::getValue('userid');
            $dados->situacao = '1';
            $dados->editado = 'S';
            $dados->valor_bolsa = self::tofloat($dados->valor_bolsa);
            $dados->valor_transporte =self::tofloat( $dados->valor_transporte) ;

            $estagio = new Estagio;
            $estagio->fromArray( (array) $dados );
            $estagio->store();
           

            //salvando horarios
            if( !empty($dados->dia_semana) AND is_array($dados->dia_semana) )
            {

                Horario::where('estagio_id', '=', $estagio->id)->delete();
                foreach( $dados->dia_semana as $row => $dia_semana)
                {
                    if ($dia_semana)
                    {
                        $horario = new Horario();
                        $horario->dia_semana  = $dia_semana;
                        $horario->turno_manha_ini = $dados->turno_manha_ini[$row];
                        $horario->turno_manha_fim = $dados->turno_manha_fim[$row];
                        $horario->turno_tarde_ini = $dados->turno_tarde_ini[$row];
                        $horario->turno_tarde_fim = $dados->turno_tarde_fim[$row];
                        $horario->turno_noite_ini = $dados->turno_noite_ini[$row];
                        $horario->turno_noite_fim = $dados->turno_noite_fim[$row];
                        $horario->turno_tarde_ini = $dados->turno_tarde_ini[$row];
                        $horario->total_dia = $dados->total_dia[$row];
                        $horario->estagio_id = $estagio->id;
                        $horario->store();
                        
                        
                       
                        
                    }
                }
            }
            
         
           
            
            
            //salvando documentos
            if( !empty($dados->tipo_doc) AND is_array($dados->tipo_doc) )
            {

                Documento::where('estagio_id', '=', $estagio->id)->delete();
                foreach( $dados->tipo_doc as $row => $tipo_doc)
                {
                    if ($tipo_doc)
                    {
                        $documento = new Documento();
                        $documento->tipo_doc  = $tipo_doc;
                      //  $documento->obs = $param['obs'][$row];
                        $arquivo = $dados->url[$row];
                        $target_file = '';
                        
                        if ($arquivo)
                        {
                            $source_file   = 'tmp/'.$arquivo;
                            $finfo         = new finfo(FILEINFO_MIME_TYPE);
                            
                            if (file_exists($source_file) AND $finfo->file($source_file) == 'application/pdf')
                            {
                                
                                $target_file   = 'files/estagios/' . TSession::getValue('login') . '-' . md5(uniqid()) . '-' . time() . '.pdf';
                                copy($source_file, $target_file);
                             
                                

                            }
                               
                            }

                            if(!(empty($target_file))){
                                $documento->url = $target_file;
                            }else{
     
                             throw new Exception('Arquivo não permitido!');
                             
                            }
                          
                          

                            

                       
                 
                        $documento->data_envio = date('Y-m-d');
                        $documento->system_user_id = TSession::getValue('userid');
                        $documento->estagio_id = $estagio->id;
                        $documento->store();
                
                   
                       
                        
                        
                     
                        
                    }
                }
            }


            
                        $dados->id = $estagio->id;
                        $this->form->setData($dados);
                      
                        //notifica o admnistrador que um termo foi envaido
                        SystemNotification::register(1, 'Novo termo recebido', 'Avaliar Termo de Estágio', 'class=EstagioList&method=abrir&termo_id='. $estagio->id, 'Avaliar', 'fas fa-user-friends');
                        
                       
                        
                       
                        $action = new TAction(array('EstagioListAluno', 'onReload'));
                        new TMessage('info', 'Estágio salvo com Sucesso, clique para ver seu estágio!', $action);
                       
                        
                        TTransaction::close(); // close the transaction */
                    }

         

        
            
           
           
                        
                        
            
         
            
            

         
            

        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit
     * Edit a record data
     */
    function onEdit($param)
    {
        //a edição do termo do alouno foiimplentada
    }
    
    /**
     * Clear form
     */
    public  function onClear($param)
    {
        $this->form->clear();

        TTransaction::open('estagio');
    

        $aluno = Aluno::where('system_user_id', '=', TSession::getValue('userid'))
                                     ->where('status', '=', 'S')
                                     ->load();
                                   
    
        if (!($aluno)){
        $action1 = new TAction(array('AlunoForm', 'abrir'));
        // shows the question dialog
        new TQuestion('Seu CADASTRO DE ALUNO ESTÁ INCOMPLETO! Gostaria de completar seu cadastro?', $action1);
        exit;
        }
        TTransaction::close();




    

        TTransaction::open('estagio');
        $aluno_cadastrado = Aluno::where('system_user_id', '=', TSession::getValue('userid'))->load();

        if($aluno_cadastrado){

            $dados = new stdClass;
            $dados->aluno_id = $aluno_cadastrado[0]->id;
            $dados->mes = date('m');
            $dados->ano = date('Y');
            $this->form->setData($dados);
            

          

        }else{

            
            $dados = new stdClass;
            $dados->mes = date('m');
            $dados->ano = date('Y');
            $this->form->setData($dados);

        }

            TTransaction::close();

      
            
            $this->horarios->addHeader();
            $this->horarios->addDetail( new stdClass );
            $this->horarios->addCloneAction();
        


            
            $this->documentos->addHeader();
            $dados = new stdClass;
            $dados->tipo_doc = 1;
            $this->documentos->addDetail( $dados );
            $dados = new stdClass;
            $dados->tipo_doc = 6;
            $this->documentos->addDetail( $dados );
            $dados = new stdClass;
            $dados->tipo_doc = 7;
            $this->documentos->addDetail( $dados );

            $this->documentos->addCloneAction();

   
      
      

      
        
       
       
       
      

    }

  
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {  
        TScript::create("Template.closeRightPanel()");
    }

    public static function onExitAction($param){
        
        $total_por_dia = array();
        
       //Faz as substrações de cada linda
        foreach($param['dia_semana'] as $key => $dia){
            
        $hora_ini = empty($param['turno_manha_ini'][$key])? '00:00' : $param['turno_manha_ini'][$key] ;
        $hora_fim = empty($param['turno_manha_fim'][$key])? '00:00' : $param['turno_manha_fim'][$key] ;
               
        if(strlen($hora_ini) < 3 or strlen($hora_fim) < 3)
        {

        new TMessage('error', 'Intervalos inválido, use formado 24 horas Exemplo - 13:oo a 16:30');
        exit;

         }
     
        if(empty($hora_ini) and empty($hora_fim))
        {
            $horas = 00;
            $minutos = 00;
            $horasf = 00;
            $minutosf = 00;

        }else{
            
            list($horas, $minutos) = explode(':', $hora_ini);
            list($horasf, $minutosf) = explode(':', $hora_fim);
        }

        if($horas < 24 and $minutos < 60 and $horasf < 24 and $minutosf < 60)
        {

        $entrada = new DateTime($hora_ini);
        $saida = new DateTime($hora_fim);
        $intervalo = $saida->diff($entrada);
       
        $hora_m = new DateTime($intervalo->h.':'.$intervalo->i.':'.$intervalo->s);
        $horas_manha = $hora_m->format('H:i');
        
        }else
        {
            new TMessage('error', 'Intervalos inválido, use formado 24 horas Exemplo - 13:oo a 16:30');
            exit;
        }

        $hora_ini = empty($param['turno_tarde_ini'][$key])? '00:00' : $param['turno_tarde_ini'][$key] ;
        $hora_fim = empty($param['turno_tarde_fim'][$key])? '00:00' : $param['turno_tarde_fim'][$key] ;
        
        if(strlen($hora_ini) < 3 or strlen($hora_fim) < 3)
        {

            new TMessage('error', 'Intervalos inválido, use formado 24 horas Exemplo - 13:oo a 16:30');
            exit;

        }
        
        
    

        if(empty($hora_ini) and empty($hora_fim))
        {

            $horas = 00;
            $minutos = 00;
            $horasf = 00;
            $minutosf = 00;
        
        }else
        {

        list($horas, $minutos) = explode(':', $hora_ini);
        list($horasf, $minutosf) = explode(':', $hora_fim);
        }

        if($horas < 24 and $minutos < 60 and $horasf < 24 and $minutosf < 60)
        {
            $entrada = new DateTime($hora_ini);
            $saida = new DateTime($hora_fim);
            $intervalo = $saida->diff($entrada);
    
            $hora_t = new DateTime($intervalo->h.':'.$intervalo->i.':'.$intervalo->s);
            $horas_tarde = $hora_t->format('H:i');

         }else
         {
            new TMessage('error', 'Intervalos inválido, use formado 24 horas Exemplo - 13:oo a 16:30');
            exit;
         }

    $hora_ini = empty($param['turno_noite_ini'][$key])? '00:00' : $param['turno_noite_ini'][$key] ;
    $hora_fim = empty($param['turno_noite_fim'][$key])? '00:00' : $param['turno_noite_fim'][$key] ;
    
    if(strlen($hora_ini) < 3 or strlen($hora_fim) < 3){

        new TMessage('error', 'Intervalos inválido, use formado 24 horas Exemplo - 13:oo a 16:30');
        exit;

    }

       

        if(empty($hora_ini) and empty($hora_fim))
        {

            $horas = 00;
            $minutos = 00;
            $horasf = 00;
            $minutosf = 00;
        }else
        {

        list($horas, $minutos) = explode(':', $hora_ini);
        list($horasf, $minutosf) = explode(':', $hora_fim);

        }

        if($horas < 24 and $minutos < 60 and $horasf < 24 and $minutosf < 60){
        $entrada = new DateTime($hora_ini);
        $saida = new DateTime($hora_fim);
        $intervalo = $saida->diff($entrada);
     
        $hora_n = new DateTime($intervalo->h.':'.$intervalo->i.':'.$intervalo->s);
        $horas_noite = $hora_n->format('H:i');

    }else
    {
        new TMessage('error', 'Intervalos inválido, use formado 24 horas Exemplo - 13:oo a 16:30');
        exit;
    }

     

       

       
            // Array com as cargas horarias de cada turno manha, tarde, noite
      $total_por_dia[$dia] = [$horas_manha, $horas_tarde, $horas_noite];

      $seconds = 0;
//calcula a carga do dia 
        foreach ( $total_por_dia[$dia] as $time )
        {
                list( $g, $i ) = explode( ':', $time );
                $seconds += $g * 3600;
                $seconds += $i * 60;
            }

                        $hours = floor( $seconds / 3600 );
                        $hours2 = str_pad($hours , 2 , '0' , STR_PAD_LEFT);
                        $seconds -= $hours * 3600;
                        $minutes = floor( $seconds / 60 );
                        $minute2 = str_pad($minutes , 2 , '0' , STR_PAD_LEFT);

                        $total = "{$hours2}:{$minute2}";
                        $total_por_dia[$dia] = [$horas_manha, $horas_tarde, $horas_noite, $total];

    

        
       



        }

        
        

       
                    $totais = array();
        //prepara um array com os totais de carga horária de cada dia
          foreach($total_por_dia as $dias => $dados ){

            $totais[] = $dados[3];

          }

          
        $data = new stdClass;
        $data->total_dia = $totais;
        
        TForm::sendData('form_estagio', $data); 

          
        
        
 
        

        
        
    }


    public static function onChangeType($param)
    {
        if ($param['_field_value'] == '1')
        {
            TQuickForm::hideField('form_estagio', 'apolice');
            TQuickForm::hideField('form_estagio', 'valor_transporte');
           
        }
        else
        {
            TQuickForm::showField('form_estagio', 'apolice');
            TQuickForm::showField('form_estagio', 'valor_transporte');
         
        }

     
    }

    public static function onChangeAction_file($param){
      

    

        $input_id = $param['_field_id'];
       
        $input_pieces = explode('_', $input_id);
        $unique_id = end($input_pieces);

        $data = new stdClass;
         $campo = 'data_envio_'.$unique_id;
        $data->{'data_envio_'.$unique_id} = date('d/m/Y');
        

          
      
        
        TForm::sendData('form_estagio', $data); 

        
    }

    public static  function tofloat($num) {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
      
        if (!$sep) {
            return floatval(preg_replace("/[^0-9]/", "", $num));
        }
    
        return floatval(
            preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
        );
    }

    public static function cargaAjustar($param){
            $carga = $param['_field_value'];

         
        
       if(strlen($carga) <= 3) {

        $carga_nova = str_pad($carga , 4 , '0' , STR_PAD_RIGHT);
 
        $horas = substr($carga_nova, 0 , 2);
        $minutos = substr($carga_nova,  -2);
       
        $carga_nova2 = $horas.':'.$minutos;
        $data = new stdClass;
        $data->carga_horaria = $carga_nova2;
        TForm::sendData('form_estagio', $data); 

       }
       
      
            $findme   = ':';
            $pos = strpos($carga, $findme);

         
       if(($pos == 2 ) and (strlen($carga) == 4) ){

    

       
            new TMessage('error',   'Use o formato 00:00 para informar carga horária');
        


       }
            

          
      
        
       

    }

   
   
}




