<?php
require_once('../../../../config.php');
require_once('../../../indicacao/geoip/geoip.inc');
require_once('../../../indicacao/geoip/geoipcity.inc');
require_once('../RelatoriosUser/grafico/highcharts.php');

global $CFG, $DB;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/gerenciamento/Relatorios/RelatoriosAcessos/graficoacessoregiao.php');
$PAGE->set_title(get_string('graficoacessoregiao', 'block_gerenciamento'));

$PAGE->navbar->add(get_string('blockname', 'block_gerenciamento'))->
	add(get_string('relatorios', 'block_gerenciamento'))->
	add(get_string('relatoriosacessos', 'block_gerenciamento'))->
	add(get_string('graficoacessoregiao', 'block_gerenciamento'), '/blocks/gerenciamento/Relatorios/RelatoriosAcessos/graficoacessoregiao.php');
$PAGE->set_pagelayout('incourse');

if (has_capability('block/gerenciamento:graficoacessoregiao', $context)) {

	echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		  <script src="../RelatoriosUser/grafico/js/highcharts.js"></script>
		  <script src="../RelatoriosUser/grafico/js/modules/exporting.js"></script>';

	echo $OUTPUT->heading(get_string('graficoacessoregiao', 'block_gerenciamento'));
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('descricao', 'block_gerenciamento'));
	echo $OUTPUT->heading(get_string('descricaograficoacessoregiao', 'block_gerenciamento'), 6);
	
	$sql='select id,ip
			from {logstore_standard_log} 
			where 
				ip <> "0.0.0.0" and 
				ip <> "127.0.0.1" and 
				ip <> "10.0.1.88" and 
				ip <> "192.168.10.177" and 
				ip <> "192.168.0.105" and 
				ip <> "192.168.0.154" and 
				ip <> "192.168.206.12" and 
				ip <> "192.168.66.165" and 
				ip <> "10.0.42.55" and 
				ip <> "10.94.16.24" and 
				ip <> "10.45.1.103" and 
				ip <> "192.168.10.239" and 
				ip <> "10.100.104.84" and
				ip <> "192.168.0.175" and
				ip <> "192.168.0.109" and
				ip <> "192.168.0.104" and
				ip <> "192.168.0.93" and
				ip <> "172.16.2.101" and
				ip <> "0:0:0:0:0:0:0:1" 
				and	action="loggedin"';

	$result = $DB->get_records_sql($sql);

	if($result){
		$regioes=array();
		$nencontados=array();
		$outros=0;
	
		$geoip = geoip_open('../../../indicacao/geoip/GeoLiteCity.dat', GEOIP_STANDARD);
		foreach ($result as $linha){
			$city = geoip_record_by_addr($geoip, $linha->ip);
			if(isset($city->country_code) && isset($city->region)){
				$estado = $GEOIP_REGION_NAME[$city->country_code][$city->region];
				if($estado){
					if($city->country_code=="BR"){
						if(array_key_exists($estado, $regioes)){
							$regioes[$estado]+=1;
						}else{
							$regioes[$estado]=1;
						}
					}else{
						$outros++;
					}
				}else{
					$outros++;
				}
			}else{
				$outros++;
			}
		}
		geoip_close($geoip);

		$regiaosul = (isset($regioes['Santa Catarina']) ? $regioes['Santa Catarina'] : 0)
				   + (isset($regioes['Rio Grande do Sul']) ? $regioes['Rio Grande do Sul'] : 0)
				   + (isset($regioes['Parana']) ? $regioes['Parana'] : 0);
		$regiaosudeste = (isset($regioes['Sao Paulo']) ? $regioes['Sao Paulo'] : 0)
					   + (isset($regioes['Rio de Janeiro']) ? $regioes['Rio de Janeiro'] : 0)
					   + (isset($regioes['Minas Gerais']) ? $regioes['Minas Gerais'] : 0)
					   + (isset($regioes['Espirito Santo']) ? $regioes['Espirito Santo'] : 0);
		$regiaocentral = (isset($regioes['Mato Grosso do Sul']) ? $regioes['Mato Grosso do Sul'] : 0)
					   + (isset($regioes['Goias']) ? $regioes['Goias'] : 0)
					   + (isset($regioes['Mato Grosso']) ? $regioes['Mato Grosso'] : 0)
					   + (isset($regioes['Distrito Federal']) ? $regioes['Distrito Federal'] : 0);
		$regiaonordeste = (isset($regioes['Maranhao']) ? $regioes['Maranhao'] : 0)
						+ (isset($regioes['Bahia']) ? $regioes['Bahia'] : 0)
						+ (isset($regioes['Piaui']) ? $regioes['Piaui'] : 0)
						+ (isset($regioes['Ceara']) ? $regioes['Ceara'] : 0)
						+ (isset($regioes['Sergipe']) ? $regioes['Sergipe'] : 0)
						+ (isset($regioes['Rio Grande do Norte']) ? $regioes['Rio Grande do Norte'] : 0)
						+ (isset($regioes['Paraiba']) ? $regioes['Paraiba'] : 0)
						+ (isset($regioes['Pernambuco']) ? $regioes['Pernambuco'] : 0)
						+ (isset($regioes['Alagoas']) ? $regioes['Alagoas'] : 0);
		$regiaonorte = (isset($regioes['Tocantins']) ? $regioes['Tocantins'] : 0)
					 + (isset($regioes['Para']) ? $regioes['Para'] : 0)
					 + (isset($regioes['Amapa']) ? $regioes['Amapa'] : 0)
					 + (isset($regioes['Roraima']) ? $regioes['Roraima'] : 0)
					 + (isset($regioes['Rondonia']) ? $regioes['Rondonia'] : 0)
					 + (isset($regioes['Amazonas']) ? $regioes['Amazonas'] : 0)
					 + (isset($regioes['Acre']) ? $regioes['Acre'] : 0);

		$graf = new Highcharts();

		$graf->chart->renderTo = "pizza";
		$graf->chart->type = "pie";
			
		$graf->chart->plotBackgroundColor = null;
		$graf->chart->plotBorderWidth = null;
		$graf->chart->plotShadow = false;
			
		$graf->title->text = get_string('titleacessoregiao', 'block_gerenciamento');
			
		$total = $regiaosul + $regiaosudeste +$regiaocentral + $regiaonordeste + $regiaonorte;
		$graf->subtitle->text= get_string('subtitleacessoregiao', 'block_gerenciamento').$total;
			
		$graf->tooltip = new stdClass();
		$graf->tooltip->formatter = "function";
		$graf->functions['tooltip']['formatter'] = "function() {
			                    return '<b>'+ this.point.name+'<br/> Total de Acessos: '+this.point.y+'</b><br/>';
			                }";
			
		$graf->plotOptions = new stdClass();
		$graf->plotOptions->pie = new stdClass();
		$graf->plotOptions->pie->allowPointSelect = true;
		$graf->plotOptions->pie->cursor = "pointer";
		$graf->plotOptions->pie->dataLabels = new stdClass();
		$graf->plotOptions->pie->dataLabels->enabled = true;
		$graf->plotOptions->pie->showInLegend =  true;
		$graf->plotOptions->pie->dataLabels->formatter = "function";
			
		$graf->functions['dataLabels']['formatter'] = "function() {
			                              return this.percentage.toFixed(2) +' %';
			                        }";
			
		$data = new stdClass();
		$data->data = array();
		$data->data[] = array ("Centro-Oeste", (int) $regiaocentral);
		$data->data[] = array ("Nordeste", (int)$regiaonordeste);
		$data->data[] = array ("Norte", (int) $regiaonorte);
		$data->data[] = array ("Sudeste", (int) $regiaosudeste);
		$data->data[] = array ("Outros", (int) $outros);
			
		$efeito = new stdClass();
		$efeito->name = "Sul";
		$efeito->y = (int) $regiaosul;
		$efeito->sliced = true;
		$efeito->selected = true;
			
		$data->data[] = $efeito;
			
		$graf->series[] = $data;
		$graf->divStyles=array( "min-width" => "600px" , "height" => "400px", "margin" =>"0 auto");
		$graf->display();
	}else{
		echo '<br>';
		echo $OUTPUT->notification(get_string('nodados', 'block_gerenciamento'));
	}
} else {
	redirect(new moodle_url($CFG->wwwroot), get_string('erropermissao', 'block_gerenciamento'));
}
echo $OUTPUT->footer();
?>