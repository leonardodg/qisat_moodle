<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 * @author Inty Brian
 *
 */

defined('MOODLE_INTERNAL') || die();

class AdministracaoEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->backup_desbloquearaulas();
		$this->backup_bloquearcurso();
		$this->backup_prorrogacao();
	}

	private function backup_desbloquearaulas(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		if($select = $DB->get_records('aulas_liberadas', array('userid'=>$user->id, 'courseid'=>$course->id))){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('aulas_liberadas_bkp', $select);
				
			$DB->delete_records_list('aulas_liberadas', 'id', $delete);
		}
	}

	private function backup_bloquearcurso(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();

    	$sql = 'SELECT bc.*
				FROM {bloqueio_curso} bc
				INNER JOIN {user_enrolments} ue ON ue.id = bc.user_enrolments
				INNER JOIN {enrol} e ON e.id = ue.enrolid
				WHERE ue.userid = '.$user->id.' AND e.courseid = '.$course->id;

		if($select = $DB->get_records_sql($sql)){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('bloqueio_curso_bkp', $select);
				
			$DB->delete_records_list('bloqueio_curso', 'id', $delete);
		}
	}

	private function backup_prorrogacao(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		if($select = $DB->get_records('prorrogacoes', array('userid'=>$user->id, 'courseid'=>$course->id))){

			$delete = array();
			foreach ($select as &$value) {
				$value->idreference = $value->id;
				$delete[] = $value->id;
				unset($value->id);
			}
			
			$DB->insert_records('prorrogacoes_bkp', $select);
				
			$DB->delete_records_list('prorrogacoes', 'id', $delete);
		}
	}
}