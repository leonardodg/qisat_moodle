<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de matrículas
 *
 *
 * @author Deyvison Fernandes
 *
 */

defined('MOODLE_INTERNAL') || die();

require($CFG->dirroot.'/enrol/multimatricula/AbstractEnrolBkp.php');
class RoleAssignmentsTrilhaBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->roleAssignmentsBkp();
		$this->userEnrolmentsBkp();
	}

	private function roleAssignmentsBkp(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();

		$select = 'SELECT ra.roleid,ra.contextid,ra.userid,ra.timemodified,ra.modifierid,ra.component,
						ra.itemid,ra.sortorder,ra.instance_provisorio,ra.roleid_antigo_provisorio,ra.id
				   FROM {role_assignments} ra
				   INNER JOIN {context} c ON ra.contextid = c.id
				   WHERE ra.userid = '.$user.' AND c.instanceid = '.$course.' AND ra.roleid IN (5,11,24)';

		$roleAssignments = $DB->get_record_sql($select);

		if($roleAssignments){
			$sql = 'INSERT INTO {role_assignments_bkp}
					(roleid,contextid,userid,timemodified,modifierid,component,itemid,sortorder,
						instance_provisorio,roleid_antigo_provisorio,idreference)
					('.$select.')';

			$DB->execute($sql);
		}
	}

	private function userEnrolmentsBkp(){
		global $DB;

		$select = 'SELECT ue.id, ue.`status`, ue.enrolid, ue.userid, ue.timestart, 
						 ue.timeend, ue.modifierid, ue.timecreated, ue.timemodified,
						 ue.ecm_alternative_host_id, ue.proposta, ue.ecm_produto_id
					FROM {user_enrolments} ue
					INNER JOIN {enrol} e ON e.id = ue.enrolid
					WHERE ue.userid = '.$this->getUser().' and e.courseid = '.$this->getCourse();

		$userEnrolment = $DB->get_record_sql($select);

		if($userEnrolment){
			$sql = 'INSERT INTO {user_enrolments_bkp} (idreference,`status`,enrolid,userid, timestart,
														timeend, modifierid, timecreated, timemodified,
														ecm_alternative_host_id, proposta, ecm_produto_id)
					('.$select.')';

			$DB->execute($sql);
		}
	}

}