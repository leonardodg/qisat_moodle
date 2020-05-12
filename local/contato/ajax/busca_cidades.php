<?php  
require('../../../config.php');
global $DB;
$id = optional_param('id',null,PARAM_INT);

if(!is_null($id)){
	$listaCidades = $DB->get_records_menu('cidade',array('uf'=>$id),'nome','id,nome');
	$optionsCidade = '<option value="">'.get_string('selecioneCidade','local_contato').'</option>';

	foreach ($listaCidades as $key => $value) {
		$optionsCidade .= '<option value="'.$key.'">'.$value.'</option>';
	}

	echo $optionsCidade;
}
?>