<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de scale
 *
 *
 * @author Deyvison Fernandes
 *
 */

defined('MOODLE_INTERNAL') || die();

class ScaleEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->scaleBkp('scale');
		$this->scaleBkp('scale_history');
	}

	private function scaleBkp($tabela){
		global $DB;

		$scaleBkp = $DB->get_records($tabela,array('userid'=>$this->getUser()->id,'courseid'=>$this->getCourse()->id));

		if($scaleBkp){

			$ids = array();
			foreach ($scaleBkp as $value) {
				$ids[] = $value->id;
				$value->idreference = $value->id;
				unset($value->id);
			}

			try {
				$DB->insert_records($tabela.'_bkp',$scaleBkp);
				$DB->delete_records_list($tabela,'id',$ids);
			} catch (Exception $e) {
				
			}
		}
	}
}