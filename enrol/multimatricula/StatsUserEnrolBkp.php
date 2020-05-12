<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de status do usuário
 *
 *
 * @author Deyvison Fernandes
 *
 */

defined('MOODLE_INTERNAL') || die();

class StatsUserEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->statsUserBkp('stats_user_daily');
		$this->statsUserBkp('stats_user_monthly');
		$this->statsUserBkp('stats_user_weekly');
	}

	private function statsUserBkp($tabela){
		global $DB;

		$select = 'SELECT sud.id,sud.courseid,sud.userid,sud.roleid,sud.timeend,sud.statsreads,
								sud.statswrites,sud.stattype
					FROM {'.$tabela.'} sud
					WHERE sud.courseid = '.$this->getCourse()->id.' AND sud.userid = '.$this->getUser()->id;

		$statsDaily = $DB->get_records_sql($select);

		if($statsDaily){
			$sql = 'INSERT INTO {'.$tabela.'_bkp} (idreference,courseid,userid,roleid,timeend,statsreads,
															statswrites,stattype)
					('.$select.')';

			if($dados = $DB->execute($sql)){
				$DB->delete_records($tabela, array('courseid'=>$this->getCourse()->id,'userid'=>$this->getUser()->id));
			}
		}
	}
}