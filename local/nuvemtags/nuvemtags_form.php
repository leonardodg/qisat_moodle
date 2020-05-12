<?php
global $CFG;

require_once ($CFG->libdir.'/formslib.php');

class local_nuvemtags_nuvemtags_form extends moodleform {
	function definition() {
		global $CFG, $DB;
		
		$mform = & $this->_form;

		$cid = $this->_customdata['cid'];
		$id = $this->_customdata['id'];

		$mform->addElement('hidden', 'cid', $cid);
		$mform->setType('cid', PARAM_INT);
		$mform->addElement('hidden', 'id', $id);
		$mform->setType('id', PARAM_INT);

		$mform->addElement('text', 'name', get_string ('tag', 'local_nuvemtags'), array());
		$mform->setType('name', PARAM_TEXT);
		$mform->addElement('button', 'buttonAdd', get_string('salvar', 'local_nuvemtags'));

		$lista = $DB->get_records('nuvemtags_data', array('idnuvemtags'=>$id), 'tag');
		
		$html_lista = '<div class="dd" style="width:500px"><ol class="dd-list">';
		foreach ( $lista as $tag ) {
			$html_lista .= '<li class="dd-item" data-id="'.$tag->id.'" id="item_'.$tag->id.'" data-value="'.$tag->tag.'">  ';
			$html_lista .= '<div class="dd-handle"> '.$tag->tag.' </div></li>';
		}
		$html_lista .= '</ol></div>';
		$mform->addElement('html', $html_lista);
		
		$wwwroot = $CFG->wwwroot . "/local/nuvemtags";
		$html = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
				<script src="'.$CFG->wwwroot.'/local/nuvemtags/jquery/jquery.nestable-2.js" type="text/javascript"></script>
				<script>
					//$(".multiselect").multiselect();
		
					$("#id_buttonAdd").click(function(){
    					var nome = $("#id_name").val();
    					$("#id_name").val("");
						if (nome != "" && $( "div ol li" ).size() < 16) {
    						$(".dd").nestable.addItem(nome);
						}
    				});
		
					$(".dd").nestable({ wwwRoot: "'.$wwwroot.'",
    					imagens: {}
    				});
		
    				$(\'input[type = "hidden"][name = "data"]\').val(JSON.stringify($(".dd").nestable("serialize")));
    			</script>
    			<script type="text/javascript">
					<!--
					function insertTag(tag){
						$.ajax({
							url: "ajax/insertTag.php",
							data: {tag:tag, idnuvemtags:'.$id.'},
							type: "POST"
						});
	    			}
					function updateTag(id, tag){
						$.ajax({
							url: "ajax/updateTag.php",
							data: {id:id, tag:tag, idnuvemtags:'.$id.'},
							type: "POST"
						});
	    			}
					function deleteTag(id){
						$.ajax({
							url: "ajax/deleteTag.php",
							data: {id:id},
							type: "POST"
						});
	    			}
					-->
	   		</script>';
		$mform->addElement('html', $html);

	}
}
	