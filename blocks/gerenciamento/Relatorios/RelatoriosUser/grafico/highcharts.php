<?php

/**
 * Objeto Highcharts desenvolvido para para fazer a ponte (ligação) entre o php e o Pluging da biblioteca Highcharts do JQuery (Javascript)
 * 			- JQuery é uma biblioteca publica onde o Highcharts é um pluging para criação de gráficos
 * 			- link da API para o Highcharts -> http://api.highcharts.com/highcharts
 * 
 * @author leonardo.giustina
 *
 */
class Highcharts{
	/**
	 * Os atributos deste objeto faz relações com os valores da biblioteca JQuery porem não possui todos apenas os nécessarios.
	 * 
	 * @var $chart					Objeto com o opções sobre a área do gráfico e área do lote, bem como opções de gráfico geral.
	 * 			$chats->renderTo	- String contendo o ID da Tag HTML que vai recebe o gráfico
	 * 			$chats->type		- String contendo o tipo de exibião do gráfico, pode ser "line", "spline", "area", "areaspline", "column", "bar", "pie" and "scatter". 
	 * 								  From version 2.3, "arearange", "areasplinerange" and "columnrange" are supported with the highcharts-more.js component. Defaults to "line".
	 * 
	 * @var $credits				Objeto necessario pois o Highchart por padrão coloca um rótulo créditos no canto inferior direito do gráfico. Isso pode ser alterado utilizando estas opções.
	 * 			$credits->enabled	- Boolean, para mostra o creditos - Defaults é true.
	 * 			$credits->href 		- String, A URL para a etiqueta créditos.  Defaults to "http://www.qisat.com.br/".
	 * 			$$credits->position - Objeto com a configuração da posição para os creditos. Possui as seguintes propriedades:
	 * 				$position->align			- String para o alinhamento horizontal do creditos. Pode ser: "left", "center" and "right". Defaults to "right".
	 * 				$position->x				- string Defaults -10
	 * 				$position->verticalAlign	- string alianhamento vertical do creditos. Pode ser  "top", "middle" and "bottom". Defaults to "bottom".
	 * 				$position->y				- String Defaults -5
	 * 
	 * @var $colors 				Array com as cores em HEX - Um array contendo as cores padrão para a série do gráfico. 
	 * 								Quando todas as cores são utilizadas, as novas cores são puxados a partir do início de novo. 
	 * 								O padrão é: colors: ['#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92']
	 * 
	 * @var $title and $subtitle 	Objeto Simple class com alguns atributos não obrigatórios 
	 * 			$title->align		- String com O alinhamento horizontal do título. Pode ser "left", "center" e "right". Defaults é "center".
	 * 			$title->text		- String com O Título do Gráfico. Para desabilita-lo set NULL. Defaults to "Chart title".
	 * 			$title->verticalAlign	- String com O alinhamento vertical do título. Pode ser "top", "middle" and "bottom". Defaults to "top".
	 * 			$title->x			- String
	 * 			$title->y			- String 
	 * 			$title->styles		- Array contendo os style do CSS, esta indeçado com o nome do style como index e os valores das propriedades do grafico.
	 * 	
	 * @var $series					Isso é um ARRAY podendo ser uma array simples apenas com os dados ou então um array de objetos
	 * 			$series->data		- Array de pontos de dados para a série. Os pontos podem ser dada de 2 maneiras:
	 * 									Modo 1:	data = array(0, 5, 3, 5)
	 * 									Modo 2: data = array(objeto->data = array(0,3),objeto->data = array(5,3))
	 * 			$series->type		String contendo o tipo de exibião do gráfico, pode ser "line", "spline", "area", "areaspline", "column", "bar", "pie" and "scatter". 
	 * 								  From version 2.3, "arearange", "areasplinerange" and "columnrange" are supported with the highcharts-more.js component. Defaults to "line".
	 * 
	 * @var $xAxis					- Objeto Simple class com alguns atributos obrigatórios para montra o eixo X ou eixo de categoria do Gráfico.
	 * 									Normalmente isto é o eixo horizontal, embora, se o gráfico está invertida isto é o eixo vertical. Em caso de vários eixos, o nó xaxis é um array de objetos de configuração.
	 * 			$xAxis->categories	- Array conteudo as categorias presentes para os $xAxis, os nomes são usados ​​em vez de números desse eixo.
	 *
	 * @var	$yAxis					- Objeto Simple class com alguns atributos obrigatórios para montra O eixo Y ou eixo de valor.
	 * 									Normalmente isto é o eixo vertical, sendo que, se o gráfico está invertida isto é o eixo horiontal. No caso de eixos múltiplos, o nó yaxis é uma matriz de objectos de configuração
	 * 			$yAxis->categories	- Array conteudo as categorias presentes para os $yAxis, os nomes são usados ​​em vez de números desse eixo.
	 
	 *@var functions 				- Array contendo as funções necessarias para o funcionamento do gráfico;
	 *								Podendo ser uma array simple indexado com o nome da proprieda da vaviavel do objeto apontando para um String que é a função ou 
	 *								uma matrix com index sendo o nome do objeto que contem a função mais o nome da propriedade.
	 *								exemplo:
	 *
	 *								 tooltip: {
							                formatter: function() {
							                        return '<b>'+ this.series.name +'</b><br/>'+
							                        this.x +': '+ this.y +'°C';
							                }
							            },
							            
							         isso é pode ser representado assim:
							         
							         $grafico->tooltip = new object();
									 $grafico->tooltip->formatter = "function";
									
									 $grafico->functions['formatter'] = "function() {
												                        return \"<b>\"+ this.series.name +\"</b><br/>\"+
												                        this.x +\": \"+ this.y ;
												                	}";
												                	
									ou assim:
									
																         $grafico->tooltip = new object();
									 $grafico->tooltip->formatter = "function";
									
									 $grafico->functions['tooltip']['formatter'] = "function() {
												                        return \"<b>\"+ this.series.name +\"</b><br/>\"+
												                        this.x +\": \"+ this.y ;
												                	}";									                	
	 *
	 */
	
	private static $type = array("line", "spline", "area", "areaspline", "column", "bar", "pie", "scatter","arearange", "areasplinerange", "columnrange" );
	public $chart = null;
	public $credits = null;
	public $title = null;
	public $subtitle = null;
	public $xAxis = null;
	public $yAxis = null;
	public $series = null;
	public $color = array ();
	public $erros = array();
	public $divStyles;
	public $functions = array();
	

	private function montarDiv(){
		
		$styleaux = "";
		
		if(is_array($this->divStyles))
			foreach ($this->divStyles as $nome => $valor)
				$styleaux .= $nome.":".$valor."; ";

		return '<div id="'.$this->chart->renderTo.'" style="'.$styleaux.'"></div>';
	}
	
	public function display(){
		
		$retorno = '';
		
		$script = ' 
			<script type="text/javascript">
			//<![CDATA[
			$(function () {
			    var chart;
			    $(document).ready(function() {
			        chart = new Highcharts.Chart({';
		
						$object_vars = get_object_vars($this);
							foreach ($object_vars as $name => $value){
								if(is_object($value)){
										$script .=$this->print_objeto($value,$name).', ';
								}elseif (is_array($value) && $name!="erros" && $name!="divStyles" && !empty($value) && $name!="functions"){
									$script .=$this->print_array($value,$name).', ';
								}
							}
		$script = substr($script, 0, -2);					
		$script .= '
				        });
				    });
				    
				});	
			//]]>	
			</script>';
		
		$retorno .= $script;
		$retorno .= $this->montarDiv();
		
		echo $retorno;
		
	}
	
	/**
	 * Método interno da class utilizado para criar as propriedade do objeto e definir as variaveis Padrões;
	 * Este método so pode ser chamado pelo proprio objeto e sempre deve ser chamado quando o objeto for criado
	 * 
	 * Sem Returno
	 * 
	 */
	private function setDefault(){
		
		$this->divStyles = array ('width' => '400px', 'height' => '400px', 'margin' => '0 auto');
		
		if(is_null($this->chart))
			$this->chart = new object();
		
		if(is_object($this->chart)){
			$this->chart->type = "line";
			$this->chart->renderTo = "highcharts";
			
		}
		
		if(is_null($this->title))
	    	$this->title  = new object();
	    	
	    if(is_object($this->title)){
			$this->title->text = get_string('title', 'block_gerenciamento');
	    }			
		
		if(is_null($this->subtitle))
			$this->subtitle = new object();
			
		if(is_object($this->subtitle)){	
			$this->subtitle->text = get_string('subtitle', 'block_gerenciamento');
		}
		
		if(is_null($this->series))
			$this->series = array();
					
		if(is_null($this->xAxis))
			$this->xAxis = new object();
			
		if(is_null($this->yAxis))
			$this->yAxis = new object();
			
		if(is_null($this->credits))
			$this->credits = new object();
			
		$this->credits->enabled = false;
			
	}
	
	/**
	 * Método utilizado para definir o estilo ou modo que o gráfico irá aparece.
	 * 
	 * Returna boolean sendo true (sem problema) ou false (contem erro)
	 * 
	 * @param String $type - Variável contendo um texto sendo a type do gráfico, o tipo do gráfico deve ser algum destes:
	 * 						"line", "spline", "area", "areaspline", "column", "bar", "pie", "scatter","arearange", "areasplinerange", "columnrange" 
	 * 
	 * @return boolean true or false
	 */
	public function setTypeGrafico($type){
		if(is_object($this->chart)){
			if(in_array($type, self::type)){
				$this->chart->type = $type;
				return true;
			}else{
				$this->erros = get_string('typegrafico', 'block_gerenciamento');
				return false;
			}
		}else {
			$this->erros = get_string('definigrafico', 'block_gerenciamento');
			return false;
		}
	}
	
	/**
	 * Método function cssArray utilizada para criar o script para estilos dentro do array
	 * Caso a variavel $styles nao seja um array gera um erro e nao irá pega os estylos 
	 * 
	 * @param Array $style Contendo as propriedades dos estylos sendo index do array e o valores das propriedades
	 * @return String com o script do style;
	 */
	private function cssArray($styles){
		
		$string = '';
		if(is_array($styles) && !empty($styles)){
			$string = 'style: { ';
			foreach ($styles as $style => $value)
				$string .= $style.':"'.$value.'",';
				
			$string = substr($string, 0, -1);
			$string .= '}';
		}else
			$this->erros =  get_string('errocss', 'block_gerenciamento');
		
		return $string;
	}
	
	/**
	 * Método utulizado para definir os estilo do gráfico
	 * Returna boolean sendo true (sem problema) ou false (contem erro)
	 * 
	 * @param Array $styles Contendo os estilos necessários para melhoras a visualização do gráfico
	 * 						Sendo o index do array propriedades dos estilos e o valores do array os valores das propriedades
	 * @return boolean true or false
	 */
	public function setStylesGrafico($styles){
		
		if(is_array($styles) && is_object($this->chart)){
			$this->chart->styles = $styles;
			return true;
		}else{
			$this->erros = get_string('errostyles', 'block_gerenciamento');
			return false;
		}
		
	}
	
	/**
	 * Métido public criado para definir o título do grafico e/ou as configurações para o mesmo.
	 * 
	 * @param String or Objeto $titulo - Este paramentro pode ser uma string onde apenas define o título do gráfico ou então pode ser um objeto 
	 *									 com propriedades padrão para este objeto que são:
	 * 
	 * 			$title->align		- String com O alinhamento horizontal do título. Pode ser "left", "center" e "right". Defaults é "center".
	 * 			$title->text		- String com O Título do Gráfico. Para desabilita-lo set NULL. Defaults to "Chart title".
	 * 			$title->verticalAlign	- String com O alinhamento vertical do título. Pode ser "top", "middle" and "bottom". Defaults to "top".
	 * 			$title->x			- String
	 * 			$title->y			- String 
	 * 			$title->styles		- Array Contendo as propriedades dos estylos sendo index do array e o valores das propriedades
	 * 
	 * Returna boolean sendo true (sem problema) ou false (contem erro)
	 * 
	 * @return boolean true or false
	 */
	public function setTitleGrafico($titulo){
		if(!is_object($this->title) && is_null($this->title))
			$this->title  = new object();
		
		if(is_string($titulo)){
			$this->title->text = $titulo;
			return true;
		}else{
			if(is_object($titulo) && !empty($titulo) && isset($titulo->text)){
				 $this->title = $titulo;
				 return true;
			}else{
				$this->erros = get_string('erroparametro', 'block_gerenciamento');
				return false;
			}
		}
	}
	
	
	/**
	 * Métido Public criado para definir o SubTítulo do Gráfico e/ou as configurações para o mesmo.
	 * 
	 * @param String or Objeto $subTitulo - Este paramentro pode ser uma string onde apenas define o subTítulo do gráfico ou então pode ser um objeto 
	 *									 com propriedades padrão para este objeto que são:
	 * 
	 * 			$subTitle->align		- String com O alinhamento horizontal do título. Pode ser "left", "center" e "right". Defaults é "center".
	 * 			$subTitle->text			- String com O SubTítulo do Gráfico. Para desabilita-lo set NULL. Defaults to "SubTitulo Gráfico".
	 * 			$subTitle->verticalAlign	- String com O alinhamento vertical do título. Pode ser "top", "middle" and "bottom". Defaults to "top".
	 * 			$subTitle->x			- String
	 * 			$subTitle->y			- String 
	 * 			$subTitle->styles		- Array Contendo as propriedades dos estylos sendo index do array e o valores das propriedades
	 * 
	 * Returna boolean sendo true (sem problema) ou false (contem erro)
	 * 
	 * @return boolean true or false
	 */
	public function setSubTitleGrafico($subTitulo){
		
		if(is_string($subTitulo)){
			if(!is_object($this->subtitle) && is_null($this->subtitle))
				$this->subtitle  = new object();
				
			$this->subtitle->text = $subTitulo;
			return true;
		}else{
			if(is_object($subTitulo)&& !empty($subTitulo) && isset($subTitulo->text)){
				$this->subtitle = $subTitulo;
				return true;
			}else{
				$this->erros =  get_string('erroparametro', 'block_gerenciamento');
				return false;
			}
		}
	}
	
	/**
	 * Método Construtor da Class Highcharts
	 * Esta função nao tem retorno, apenas chama outro métodos necessarios para criar o objeto Highcharts
	 * 
	 */
	public function Highcharts(){
		$this->setDefault();
	}

	public function printErros($return = false){
		
		if($return)
			return $this->erros;
		else{
			echo get_string('errografico', 'block_gerenciamento');
			print_r($this->erros);
		}		
	}
	
	public function print_array($valores, $nome = ''){
		
		if(empty($nome))
			$dados = "[ ";
		else 
			$dados = "$nome:[ ";
		
		foreach ($valores as $name => $valor){
			if(is_object($valor)){
				if(($nome == "series")||($nome == "data"))
					$dados .= $this->print_objeto($valor).', ';
				else
					$dados .= $this->print_objeto($valor, $name).', ';
				
			}elseif (is_array($valor)){
				if(($nome=="series")||($name == "data")|| is_int($name))
					$dados .= $this->print_array($valor).', ';	
				else
					$dados .= $this->print_array($valor, $name).', ';
			}else{
					if(is_int($valor)|| is_real($valor) || is_integer($valor) || is_float($valor))
						$dados .= "$valor, ";
					elseif (is_null($valor))
						$dados .= "null , ";
					else
						$dados .= "\"$valor\", ";
			}
		}
		
		$dados = substr($dados, 0, -2);
		$dados .= "]";
		
		return $dados;

	}
	
	public function print_objeto($objeto, $nome=''){

		$retorno = '';
		if(is_object($objeto)){
			
			if(empty($nome))
				$retorno = '{';
			else 
				$retorno = $nome.':{';
				
			if( $object_vars = get_object_vars($objeto)){
				foreach ($object_vars as $name => $value){ 
				    if(is_object($value)){
				    		$retorno .= $this->print_objeto($value,$name).',';
				    }
				    elseif (is_array($value) && ($name == "styles") && !empty($value))
						$retorno .= $this->cssArray($value).',';
					elseif(is_float($value) or is_int($value) or is_integer($value) or is_real($value))
						$retorno .= $name.': '.$value.' ,';
					elseif(is_array($value) && !empty($value)){
							$retorno .= $this->print_array($value,$name).',';	
					}elseif (is_bool($value)){
						if($value)
							$retorno .= $name.': true,';
						else
							$retorno .= $name.': false,';
					}elseif (is_null($value))
						$retorno .= $name.': null,';
					elseif (is_string($value)){
						if($value == "function"){
							if(isset($this->functions[$name]))
								$retorno .= $name.': '.$this->functions[$name].",";
							elseif (isset($this->functions[$nome][$name]))
								$retorno .= $name.': '.$this->functions[$nome][$name].",";
						}else
							$retorno .= $name.':"'.$value.'",';
					}
					
				}
				
				$retorno = substr($retorno, 0, -1);
			}
			$retorno .= '}';
		}		

		return $retorno;
	}
	
}
?>