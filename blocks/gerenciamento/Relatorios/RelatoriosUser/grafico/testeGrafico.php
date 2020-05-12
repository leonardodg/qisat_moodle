<?php
global $CFG;

require_once('../../config.php');
require_once ($CFG->libdir.'/grafico/highcharts.php');


$title =  get_string('teste', 'block_gerenciamento');
$navlinks = array();
$navlinks[] = array('name' => $title, 'link' => null, 'type' => 'misc');
$navigation = build_navigation($navlinks);
	
print_header($title, $title, $navigation, "", "", true, "&nbsp;", "");
	


// ---------------------------------------------------------------------------------------------------------------
$ob = new Highcharts();
$ob->chart->marginRight = 0;
$ob->title->text = $title;


$ob->xAxis->categories = array('Jan', 'Fev', 'Mar', 'ABr', 'Mai', 'Jun','Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');

$ob->yAxis->title = new object();
$ob->yAxis->title->text = "Quantidade";


$ob->tooltip = new object();
$ob->tooltip->formatter = "function";

$ob->functions['formatter'] = "function() {
			                        return \"<b>\"+ this.series.name +\"</b><br/>\"+
			                        this.x +\": \"+ this.y ;
			                	}";

$data1 = new object();
$data1->name = "Inscricoes";
$data1->data = array(7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6);
$data2 = new object();
$data2->name = "Certificados";
$data2->data = array(-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5);

$ob->series[] = $data1;
$ob->series[] = $data2;

$ob->display();


// ---------------------------------------------------------------------------------------------------------------

$graf = new Highcharts();

$graf->chart->renderTo = "pizza";

$graf->chart->plotBackgroundColor = null;
$graf->chart->plotBorderWidth = null;
$graf->chart->plotShadow = false;
$graf->title->text = "NOVO Acessos por Regiao";

$graf->tooltip = new object();
$graf->tooltip->pointFormat = "{series.name}: <b>{point.percentage}%</b>";
$graf->tooltip->percentageDecimals = 1;

$graf->plotOptions = new object();
$graf->plotOptions->pie = new object();
$graf->plotOptions->pie->allowPointSelect = true;
$graf->plotOptions->pie->cursor = "pointer";
$graf->plotOptions->pie->dataLabels = new object();
$graf->plotOptions->pie->dataLabels->enabled = false;
$graf->plotOptions->pie->showInLegend =  true;

$data = new object();

$data->type = "pie";
$data->name = "Acessos";
$data->data = array();
$data->data[] = array ("Centro-Oeste", (float)2.2 );
$data->data[] = array ("Nordeste", 6);
$data->data[] = array ("Norte", 7);
$data->data[] = array ("Sudeste", 8);
$data->data[] = array ("Outros", 6);

$efeito = new object();
$efeito->name = "Norte";
$efeito->y = 12.8;
$efeito->sliced = true;
$efeito->selected = true;

$data->data[] = $efeito;

$graf->series[] = $data;

$graf->display();

// ---------------------------------------------------------------------------------------------------------------

$novograf = new Highcharts();
$novograf->chart->renderTo = "novo";
$novograf->chart->type = "line";
$novograf->chart->marginRight = 130;
$novograf->chart->marginBottom = 25;

$novograf->title->text = "Monthly Average Temperature";
$novograf->title->x = -20;

$novograf->subtitle->text = "Source: WorldClimate.com";
$novograf->subtitle->x = -20;

$novograf->xAxis->categories = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun','Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$novograf->yAxis->title = new object();
$novograf->yAxis->title->text = "Temperature (°C)";
$novograf->yAxis->title->plotLines = array();
$plotLines = new object();

$plotLines->value = 0;
$plotLines->width = 1;
$plotLines->color = "#808080";

$novograf->yAxis->title->plotLines[] = $plotLines;


$novograf->tooltip = new object();
$novograf->tooltip->formatter = "function";

$novograf->functions['formatter'] = "function() {
			                        return \"<b>\"+ this.series.name +\"</b><br/>\"+
			                        this.x +\": \"+ this.y ;
			                	}";

$novograf->legend = new object();

$novograf->legend->layout =  "vertical";
$novograf->legend->align = "right";
$novograf->legend->verticalAlign  = "top";
$novograf->legend->x = -10;
$novograf->legend->y = 100;
$novograf->legend->borderWidth = 0;

$data1 = new object();
$data1->name = "Tokyo";
$data1->data = array (7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6);
$novograf->series[] = $data1;

$data2 = new object();
$data2->name = "New York";
$data2->data = array(-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5);
$novograf->series[] = $data2;

$data3 = new object();
$data3->name = "Berlin";
$data3->data = array(-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0);
$novograf->series[] = $data3;

$data4 = new object();
$data4->name = "London";
$data4->data = array(3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8);
$novograf->series[] = $data4;

$novograf->divStyles = array( "min-width" => "400px" , "height" => "400px", "margin" =>"0 auto");

$novograf->display();

/*renderTo: 'container',
                type: 'line',
                marginRight: 130,
                marginBottom: 25*/

/*
 * $(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container',
                type: 'line',
                marginRight: 130,
                marginBottom: 25
            },
            title: {
                text: 'Monthly Average Temperature',
                x: -20 //center
            },
            subtitle: {
                text: 'Source: WorldClimate.com',
                x: -20
            },
            xAxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            },
            yAxis: {
                title: {
                    text: 'Temperature (°C)'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                formatter: function() {
                        return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y +'°C';
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0
            },
            series: [{
                name: 'Tokyo',
                data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
            }, {
                name: 'New York',
                data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
            }, {
                name: 'Berlin',
                data: [-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0]
            }, {
                name: 'London',
                data: [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
            }]
        });
    });
    
});
 */

// ---------------------------------------------------------------------------------------------------------------
// 											GRAFICO AREA
// ---------------------------------------------------------------------------------------------------------------

/*
 * $(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container',
                type: 'area'
            },
            title: {
                text: 'US and USSR nuclear stockpiles'
            },
            subtitle: {
                text: 'Source: <a href="http://thebulletin.metapress.com/content/c4120650912x74k7/fulltext.pdf">'+
                    'thebulletin.metapress.com</a>'
            },
            xAxis: {
                labels: {
                    formatter: function() {
                        return this.value; // clean, unformatted number for year
                    }
                }
            },
            yAxis: {
                title: {
                    text: 'Nuclear weapon states'
                },
                labels: {
                    formatter: function() {
                        return this.value / 1000 +'k';
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    return this.series.name +' produced <b>'+
                        Highcharts.numberFormat(this.y, 0) +'</b><br/>warheads in '+ this.x;
                }
            },
            plotOptions: {
                area: {
                    pointStart: 1940,
                    marker: {
                        enabled: false,
                        symbol: 'circle',
                        radius: 2,
                        states: {
                            hover: {
                                enabled: true
                            }
                        }
                    }
                }
            },
            series: [{
                name: 'USA',
                data: [null, null, null, null, null, 6 , 11, 32, 110, 235, 369, 640,
                    1005, 1436, 2063, 3057, 4618, 6444, 9822, 15468, 20434, 24126,
                    27387, 29459, 31056, 31982, 32040, 31233, 29224, 27342, 26662,
                    26956, 27912, 28999, 28965, 27826, 25579, 25722, 24826, 24605,
                    24304, 23464, 23708, 24099, 24357, 24237, 24401, 24344, 23586,
                    22380, 21004, 17287, 14747, 13076, 12555, 12144, 11009, 10950,
                    10871, 10824, 10577, 10527, 10475, 10421, 10358, 10295, 10104 ]
            }, {
                name: 'USSR/Russia',
                data: [null, null, null, null, null, null, null , null , null ,null,
                5, 25, 50, 120, 150, 200, 426, 660, 869, 1060, 1605, 2471, 3322,
                4238, 5221, 6129, 7089, 8339, 9399, 10538, 11643, 13092, 14478,
                15915, 17385, 19055, 21205, 23044, 25393, 27935, 30062, 32049,
                33952, 35804, 37431, 39197, 45000, 43000, 41000, 39000, 37000,
                35000, 33000, 31000, 29000, 27000, 25000, 24000, 23000, 22000,
                21000, 20000, 19000, 18000, 18000, 17000, 16000]
            }]
        });
    });
    
});
 */

$area = new Highcharts();
$area->chart->renderTo = "area";
$area->chart->type = "area";

$area->title->text = "US and USSR nuclear stockpiles";
$area->subtitle->text = "Source: ";

$area->plotOptions = new object();
$area->plotOptions->area = new object();
$area->plotOptions->area->pointStart = 1940;
$area->plotOptions->area->marker = new object();
$area->plotOptions->area->marker->enabled = false;
$area->plotOptions->area->marker->symbol = "circle";
$area->plotOptions->area->marker->radius = 2;
$area->plotOptions->area->marker->states = new object();
$area->plotOptions->area->marker->states->hover = new object();
$area->plotOptions->area->marker->states->hover->enabled = true;

$area->xAxis->labels = new object();
$area->xAxis->labels->formatter = "function";

$area->yAxis->labels = new object();

$area->yAxis->title = new object();
$area->yAxis->title->text = "Nuclear weapon states";

$area->yAxis->labels->formatter = "function";

$area->functions['labels']['formatter'] = "function() {
                        return this.value; // clean, unformatted number for year
                    }";

$area->tooltip = new object();
$area->tooltip->formatter = "function";

$area->functions['tooltip']['formatter'] = "function() {
                    return this.series.name +' produced <b>'+
                        Highcharts.numberFormat(this.y, 0) +'</b><br/>warheads in '+ this.x;
                }";


$data5 = new object();
$data5->name = "USA";
$data5->data = array (null, null, null, null, null, 6 , 11, 32, 110, 235, 369, 640,1005, 1436, 2063, 3057, 4618, 6444, 9822, 15468, 20434, 24126,27387, 29459, 31056, 31982, 32040, 31233, 29224, 27342, 26662, 26956, 27912, 28999, 28965, 27826, 25579, 25722, 24826, 24605, 24304, 23464, 23708, 24099, 24357, 24237, 24401, 24344, 23586, 22380, 21004, 17287, 14747, 13076, 12555, 12144, 11009, 10950, 10871, 10824, 10577, 10527, 10475, 10421, 10358, 10295, 10104);
$area->series[] = $data5;

$data6 = new object();
$data6->name = "USSR/Russia";
$data6->data = array(null, null, null, null, null, null, null , null , null ,null, 5, 25, 50, 120, 150, 200, 426, 660, 869, 1060, 1605, 2471, 3322, 4238, 5221, 6129, 7089, 8339, 9399, 10538, 11643, 13092, 14478, 15915, 17385, 19055, 21205, 23044, 25393, 27935, 30062, 32049, 33952, 35804, 37431, 39197, 45000, 43000, 41000, 39000, 37000, 35000, 33000, 31000, 29000, 27000, 25000, 24000, 23000, 22000, 21000, 20000, 19000, 18000, 18000, 17000, 16000);
$area->series[] = $data6;

$area->divStyles = array( "min-width" => "400px" , "height" => "400px", "margin" =>"0 auto");

$area->display();

//=-=-=--==-=-=-=-=-=-=-=-----------------------------------------------=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
//									COLUNAS
//=-=-=--==-=-=-=-=-=-=-=-----------------------------------------------=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

/*
 * $(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
    
            chart: {
                renderTo: 'container',
                type: 'column'
            },
    
            title: {
                text: 'Total fruit consumtion, grouped by gender'
            },
    
            xAxis: {
                categories: ['Apples', 'Oranges', 'Pears', 'Grapes', 'Bananas']
            },
    
            yAxis: {
                allowDecimals: false,
                min: 0,
                title: {
                    text: 'Number of fruits'
                }
            },
    
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.x +'</b><br/>'+
                        this.series.name +': '+ this.y +'<br/>'+
                        'Total: '+ this.point.stackTotal;
                }
            },
    
            plotOptions: {
                column: {
                    stacking: 'normal'
                }
            },
    
            series: [{
                name: 'John',
                data: [5, 3, 4, 7, 2],
                stack: 'male'
            }, {
                name: 'Joe',
                data: [3, 4, 4, 2, 5],
                stack: 'male'
            }, {
                name: 'Jane',
                data: [2, 5, 6, 2, 1],
                stack: 'female'
            }, {
                name: 'Janet',
                data: [3, 0, 4, 4, 3],
                stack: 'female'
            }]
        });
    });
    
});
 */

$coluna = new Highcharts();

$coluna->chart->renderTo = "coluna";
$coluna->chart->type = "column";
$coluna->title->text = "Total fruit consumtion, grouped by gender";
$coluna->xAxis->categories = array('Apples', 'Oranges', 'Pears', 'Grapes', 'Bananas');
$coluna->yAxis->allowDecimals = false;
$coluna->yAxis->min = 0;
$coluna->yAxis->title = new object();
$coluna->yAxis->title->text = "Number of fruits";

$area->tooltip = new object();
$area->tooltip->formatter = "function";

$area->functions['tooltip']['formatter'] = "function() {
                    return '<b>'+ this.x +'</b><br/>'+
                        this.series.name +': '+ this.y +'<br/>'+
                        'Total: '+ this.point.stackTotal;
                }";

$coluna->plotOptions = new object();
$coluna->plotOptions->column = new object();
$coluna->plotOptions->column->stacking = "normal";

$data7 = new object();
$data7->name = "John";
$data7->data = array(5, 3, 4, 7, 2);
$data7->stack = "male";
$coluna->series[] = $data7;

$data8 = new object();
$data8->name = "Joe";
$data8->data = array(3, 4, 4, 2, 5);
$data8->stack = "male";
$coluna->series[] = $data8;

$data9 = new object();
$data9->name = "Jane";
$data9->data = array(2, 5, 6, 2, 1);
$data9->stack = "female";
$coluna->series[] = $data9;

$data10 = new object();
$data10->name = "Janect";
$data10->data = array(3, 0, 4, 4, 3);
$data10->stack = "female";
$coluna->series[] = $data10;

$coluna->divStyles = array( "min-width" => "400px" , "height" => "400px", "margin" =>"0 auto");

$coluna->display();

print_footer();

?>