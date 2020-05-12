<?php 

defined('MOODLE_INTERNAL') || die();

class CertificateEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->certificate_issues();
	}

	private function certificate_issues(){
		global $DB;

		$user = $this->getUser();
		$course = $this->getCourse();
		
		$sql = 'SELECT ci.userid, ci.certificateid, ci.code, ci.timecreated, ci.id
				FROM {certificate_issues} ci INNER JOIN {certificate} c ON ci.certificateid = c.id
				where c.course = '.$course->id.' AND ci.userid = '.$user->id;
		
		if($select = $DB->get_records_sql($sql)){
			
			$sql = 'INSERT INTO {certificate_issues_bkp} (userid, certificateid, code, timecreated, idreference)
					('.$sql.')';
			
			if($dados = $DB->execute($sql)) {

				$delete = array();
				foreach ($select as &$value) {
					$delete[] = $value->id;
				}
				$DB->delete_records_list('certificate_issues', 'id', $delete);
				
			}
		}
	}
}