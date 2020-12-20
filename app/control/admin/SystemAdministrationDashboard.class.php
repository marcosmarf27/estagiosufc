<?php
/**
 * SystemAdministrationDashboard
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemAdministrationDashboard extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        try
        {
            $html = new THtmlRenderer('app/resources/system_admin_dashboard.html');
            
            TTransaction::open('permission');
            $indicator1 = new THtmlRenderer('app/resources/info-box.html');
            $indicator2 = new THtmlRenderer('app/resources/info-box.html');
            $indicator3 = new THtmlRenderer('app/resources/info-box.html');
            $indicator4 = new THtmlRenderer('app/resources/info-box.html');
            
            $indicator1->enableSection('main', ['title' => 'Estágios Avaliados',    'icon' => 'user',       'background' => 'orange', 'value' => Estagio::count()]);
            $indicator2->enableSection('main', ['title' => 'Convênios cadastrados',   'icon' => 'users',      'background' => 'blue',   'value' => Concedente::count()]);
            $indicator3->enableSection('main', ['title' => 'Professores que já orientaram',    'icon' => 'university', 'background' => 'purple', 'value' => Professor::count()]);
            $indicator4->enableSection('main', ['title' => 'Alunos cadastrados', 'icon' => 'code',       'background' => 'green',  'value' => SystemUser::count()]);
            
            $chart1 = new THtmlRenderer('app/resources/google_pie_chart.html');
            $data1 = [];
            $data1[] = [ 'Tipo de Estágio', 'Quantidade' ];
            
            $stats1 = Estagio::groupBy('tipo_estagio_id')->countBy('id', 'count');
            if ($stats1)
            {
                foreach ($stats1 as $row)
                {
                    $data1[] = [ Tipo::find($row->tipo_estagio_id)->nome, (int) $row->count];
                }
            }
            
            // replace the main section variables
            $chart1->enableSection('main', ['data'   => json_encode($data1),
                                            'width'  => '100%',
                                            'height'  => '500px',
                                            'title'  => 'Estágios por tipo',
                                            'ytitle' => 'Tipos de Estágios', 
                                            'xtitle' => 'Quantidade',
                                            'uniqid' => uniqid()]);
            
            $chart2 = new THtmlRenderer('app/resources/google_column_chart.html');
            $data2 = [];
            $data2[] = [ 'Ano', 'Estágios' ];
            
            $stats2 = Estagio::groupBy('ano')->countBy('id', 'count');
            
            asort($stats2);
            /* echo "<pre>";
            print_r($stats2);
            echo "</pre>"; */
            if ($stats2)
            {
                foreach ($stats2 as $row)
                {
                    $data2[] = [ $row->ano, (int) $row->count];
                }
            }
          
            $chart2->enableSection('main', ['data'   => json_encode($data2),
            'width'  => '100%',
            'height'  => '500px',
            'title'  => 'Estágios por ano',
            'ytitle' =>  'Ano', 
            'xtitle' => 'Quantidade',
            'uniqid' => uniqid()]);


            $chart3 = new THtmlRenderer('app/resources/google_column_chart.html');
            $data3 = [];
            $data3[] = [ 'Tipo de Estágio', 'Quantidade' ];
            
            $stats3 = Estagio::groupBy('concedente_id')->countBy('id', 'count');
            if ($stats3)
            {
                foreach ($stats3 as $row)
                {
                    $data3[] = [ Concedente::find($row->concedente_id)->nome, (int) $row->count];
                }
            }
            
            // replace the main section variables
            $chart3->enableSection('main', ['data'   => json_encode($data3),
                                            'width'  => '100%',
                                            'height'  => '500px',
                                            'title'  => 'Maiores Parceiros UFC Campus Russas',
                                            'ytitle' => '', 
                                            'xtitle' => 'Quantidade',
                                            'uniqid' => uniqid()]);
            
            $html->enableSection('main', ['indicator1' => $indicator1,
                                          'indicator2' => $indicator2,
                                          'indicator3' => $indicator3,
                                          'indicator4' => $indicator4,
                                          'chart1'     => $chart1,
                                          'chart2'     => $chart2,
                                          'chart3'     => $chart3] );
            
            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $container->add($html);
            
            parent::add($container);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            parent::add($e->getMessage());
        }
    }
}
