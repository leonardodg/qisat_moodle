<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 *
 * @author Deyvison Fernandes
 *
 */

defined('MOODLE_INTERNAL') || die();

class RoleAssignmentsEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->roleAssignmentsBkp();
		$this->logContratoBkp();
		$this->userEnrolmentsBkp();
		$this->userLastaccessBkp();
	}

	private function roleAssignmentsBkp(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();

		$select = 'SELECT ra.roleid,ra.contextid,ra.userid,ra.timemodified,ra.modifierid,ra.component,
						ra.itemid,ra.sortorder,ra.instance_provisorio,ra.roleid_antigo_provisorio,ra.id
				   FROM {role_assignments} ra
				   INNER JOIN {context} c ON ra.contextid = c.id
				   WHERE ra.userid = '.$user->id.' AND c.instanceid = '.$course->id.' AND ra.roleid IN (5,11,24)';

		$roleAssignments = $DB->get_record_sql($select);

		if($roleAssignments){
			$sql = 'INSERT INTO {role_assignments_bkp}
					(roleid,contextid,userid,timemodified,modifierid,component,itemid,sortorder,
						instance_provisorio,roleid_antigo_provisorio,idreference)
					('.$select.')';

			if($dados = $DB->execute($sql)){
				$DB->delete_records('role_assignments',array('id'=>$roleAssignments->id));
			}
		}
	}

	private function logContratoBkp(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();

		$select = 'SELECT ue.id
				   FROM {user_enrolments} ue
				   INNER JOIN {enrol} e ON e.id = ue.enrolid
				   WHERE ue.userid = '.$user->id.' AND e.courseid = '.$course->id;

		$userEnrolments = $DB->get_record_sql($select);

		if($userEnrolments) {
			$select = 'SELECT lc.ecm_venda_id, lc.mdl_user_enrolments_id, lc.timecreated, lc.id
				   FROM ecm_log_contrato lc
				   WHERE lc.mdl_user_enrolments_id = ' . $userEnrolments->id;

			$logContrato = $DB->get_record_sql($select);

			if ($logContrato) {

				$sql = 'INSERT INTO ecm_log_contrato_bkp (ecm_venda_id, mdl_user_enrolments_id,
						timecreated, idreference) ' . $select;

				if ($dados = $DB->execute($sql)) {
					$sql = 'DELETE FROM ecm_log_contrato
						WHERE id = ' . $logContrato->id;

					$DB->execute($sql);
				}
			}
		}
	}

	private function userEnrolmentsBkp(){
		global $DB;

		$select = 'SELECT ue.id, ue.`status`, ue.enrolid, ue.userid, ue.timestart, 
						 ue.timeend, ue.modifierid, ue.timecreated, ue.timemodified,
						 ue.ecm_alternative_host_id, ue.proposta, ue.ecm_produto_id
					FROM {user_enrolments} ue
					INNER JOIN {enrol} e ON e.id = ue.enrolid
					WHERE ue.userid = '.$this->getUser()->id.' and e.courseid = '.$this->getCourse()->id;

		$userEnrolment = $DB->get_record_sql($select);

		if($userEnrolment){
			$sql = 'INSERT INTO {user_enrolments_bkp} (idreference,`status`,enrolid,userid, timestart,
														timeend, modifierid, timecreated, timemodified,
														ecm_alternative_host_id, proposta, ecm_produto_id)
					('.$select.')';

			if($dados = $DB->execute($sql)){
				$DB->delete_records('user_enrolments',array('id'=>$userEnrolment->id));
			}
		}
	}

	private function userLastaccessBkp(){
		global $DB;

		$select = 'SELECT ul.id,ul.userid,ul.courseid,ul.timeaccess
				   FROM {user_lastaccess} ul
				   WHERE ul.userid = '.$this->getUser()->id.' AND ul.courseid = '.$this->getCourse()->id;

		$userLastaccess = $DB->get_record_sql($select);

		if($userLastaccess){
			$sql = 'INSERT INTO {user_lastaccess_bkp} (idreference,userid,courseid,timeaccess)
					('.$select.')';

			if($dados = $DB->execute($sql)){
				$DB->delete_records('user_lastaccess',array('id'=>$userLastaccess->id));
			}
		}
	}
}