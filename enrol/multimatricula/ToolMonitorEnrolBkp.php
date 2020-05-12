<?php 

/**
 * Classe responsável pela execução dos processos de back-up do contexto de tool monitor
 *
 *
 * @author Deyvison Fernandes
 *
 */

defined('MOODLE_INTERNAL') || die();

class ToolMonitorEnrolBkp extends AbstractEnrolBkp{

	public function executeBackup(){
		$this->toolMonitorRulesBkp('tool_monitor_rules','tool_monitor_rules_bkp');
		$this->toolMonitorRulesBkp('tool_monitor_subscriptions','tl_monitor_subscriptions_bkp');
	}

	private function toolMonitorRulesBkp($tabela,$tabelaBkp){
		global $DB;

		$toolMonitor = $DB->get_records($tabela,array('userid'=>$this->getUser()->id,'courseid'=>$this->getCourse()->id));

		if($toolMonitor){

			$ids = array();
			foreach ($toolMonitor as $value) {
				$ids[] = $value->id;
				$value->idreference = $value->id;
				unset($value->id);
			}

			try {
				$DB->insert_records($tabelaBkp,$toolMonitor);
				$DB->delete_records_list($tabela,'id',$ids);
			} catch (Exception $e) {
				
			}
		}
	}
}