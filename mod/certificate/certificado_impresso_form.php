<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/lib/formslib.php');

class certificado_impresso_form extends moodleform {

    // Define the form
    function definition () {
    	global $CFG, $USER, $DB;
    	
    	echo '<SCRIPT TYPE="text/javascript">
			 <!--
        		function mascara(obj,func){
				    objeto = obj;
				    funcao = func;
				    setTimeout("execmascara()",1);
				}
				function execmascara(){
			 	    objeto.value = funcao(objeto.value);
				}
				function mascara_cep(v){
					v = v.replace(/\D/g,"");
				    v = v.replace(/^(\d{5})(\d)/,"$1-$2");
				    return v;
				}
				function soNumeros(v){
    				return v.replace(/\D/g,"");
				}
			//-->
			</SCRIPT>';
    	
    	$id = $this->_customdata['id'];
    	$userid = $this->_customdata['userid'];
    	$sql = "select *
				from {user} u
				left join {user_endereco} e
				on u.id = e.id
				where u.id = $userid";
    	$user = $DB->get_record_sql($sql);
    	
    	$courseid = $this->_customdata['course'];
    	$course = $DB->get_record('course', array('id'=>$courseid));
    	
    	$mform = $this->_form;
    	$mform->addElement('header', 'dados', get_string('dadosendereco', 'certificate'));
    	$mform->addElement('html', '<div align="center">'.get_string('dadosenderecomensagem', 'certificate').'</div><br>');
    	$objs = array();
    	$texto = array();

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', $id);
    	
    	//$attributes='size="45" maxlength="70"';
    	$attributes='';
    	$mform->addElement('text', 'endereco', get_string('endereco', 'certificate'), $attributes);
    	$mform->setType('endereco', PARAM_TEXT);
    	$mform->setDefault('endereco', $user->address);
    	$mform->addRule('endereco', get_string('obrigatorio', 'certificate'), 'required');
    	
    	//$attributes='size="4" maxlength="10" onkeypress="mascara(this,soNumeros)"';
    	$attributes='onkeypress="mascara(this,soNumeros)"';
    	$mform->addElement('text', 'num', get_string('numero', 'certificate'), $attributes);
    	$mform->setType('num', PARAM_INT);
    	$mform->setDefault('num', $user->number);
    	$mform->addRule('num', get_string('obrigatorio', 'certificate'), 'required');
    	$mform->addRule('num', get_string('numeric', 'certificate'), 'numeric');
    	
    	//$attributes='size="45" maxlength="30"';
    	$attributes='';
    	$mform->addElement('text', 'comp', get_string('complemento', 'certificate'), $attributes);
    	$mform->setType('comp', PARAM_TEXT);
    	$mform->setDefault('comp', $user->complement);
    	
    	//$attributes='size="19" maxlength="30" style="margin-left:290px"';
    	$objs[0] = $mform->createElement('text', 'cidade', NULL, $attributes);
    	$mform->setType('cidade', PARAM_TEXT);
    	$mform->setDefault('cidade', $user->city);
    	//$attributes='size="12" maxlength="30"';
    	$objs[1] = $mform->createElement('text', 'bairro', NULL, $attributes);
    	$mform->setType('bairro', PARAM_TEXT);
    	$mform->setDefault('bairro', $user->district);
    	
    	$texto[0] = get_string('bairro', 'certificate');
    	$mform->addGroup($objs,'grupo3',get_string('cidade', 'certificate'), $texto[0], false);
    	$mform->addGroupRule('grupo3',get_string('obrigatorios', 'certificate'), 'required');
    		
    	$arrayUF = $estados = $DB->get_records_select_menu('estado', '', null, '', 'uf,nome');
    	
    	//$attributes='size="15" maxlength="9" style="margin-left:290px" onkeypress="mascara(this,mascara_cep)"';
    	$attributes='onkeypress="mascara(this,mascara_cep)"';
    	$objs[0] = $mform->createElement('text', 'cep', NULL, $attributes);
    	$mform->setType('cep', PARAM_TEXT);
    	$mform->setDefault('cep', $user->cep);
        
    	$objs[1] = $mform->createElement('select', 'uf', NULL, $arrayUF);
    	$mform->setDefault('uf', $user->state);
    	
    	$texto[0] = get_string('uf', 'certificate');
    	$mform->addGroup($objs,'grupo2',get_string('cep', 'certificate'), $texto[0], false);
    	$mform->addGroupRule('grupo2',get_string('obrigatorios', 'certificate'), 'required');
    	
    	//adicionar regra para UM elemento do grupo
    	$mform->addGroupRule('grupo2', array(
    			'cep' => array(
    					array(get_string('minlengthcep', 'certificate'), 'minlength', '9')
    			)
    	));
    	
    	$mform->addElement('selectyesno', 'enderecopadrao', get_string('alterarpermanentemente', 'certificate'));
    	
    	//$mform->registerNoSubmitButton('alterar');
    	//$mform->addElement('submit', 'alterar', get_string("alterarendereco",'certificate'));
    		
    	
    	$mform->addElement('header', 'confirm', get_string("confirmar", 'certificate'));
    	    	
    	$mform->addElement('html', '<div align="center">'.get_string("confirmarmensagem", 'certificate').'</div>');
    	//if ($course->imprimir_nota != 1) {
    		$mform->addElement('checkbox', 'imprimir', '', get_string("imprimirmedia", 'certificate'));
    	//}
    	    	
    	//if($USER->id != $user->id){
    		//$mform->addElement('checkbox', 'enviodocertificado', '', get_string("emitircertificado", 'certificate'));
    	//}
    		
    	$buttonarray=array();
    	$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('emitir', 'certificate')/*, 'style="margin-left:290px"'*/);
    	$buttonarray[] = &$mform->createElement('cancel');
    	$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($this->is_recaptcha_enabled()) {
            $recaptcha_element = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['recaptcha_challenge_field'])) {
                $challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
                $response_field = $this->_form->_submitValues['recaptcha_response_field'];
                if (true !== ($result = $recaptcha_element->verify($challenge_field, $response_field))) {
                    $errors['recaptcha'] = $result;
                }
            } else {
                $errors['recaptcha'] = get_string('missingrecaptchachallengefield');
            }
        }
        return $errors;
    }

    function is_recaptcha_enabled() {
        global $CFG;
        return (!empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey));
    }

}