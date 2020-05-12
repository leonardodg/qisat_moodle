<?php 
/**
 * Classe responsável por executar todos os processos de back-up
 *
 *
 * @author Deyvison Fernandes
 *
 */
defined('MOODLE_INTERNAL') || die();

require($CFG->dirroot.'/enrol/multimatricula/AbstractEnrolBkp.php');
class EnrolBkp extends AbstractEnrolBkp{

	public function __construct($user,$course){
		$this->setUser($user);
		$this->setCourse($course);
	}

	public function executeBackup(){
		$this->executeBackupPlugins();
	}

	/*Função que busca os locais das classes de back-up*/
	private function buscaPuginBkp(){
		global $DB;

		$listaPlugins = $DB->get_records('enrol_backup',null,'ordem ASC');
		return $listaPlugins;
	}

	/*Função responsável por validar e executar os processos de back-up das diversas classes*/
	private function executeBackupPlugins(){
		global $CFG;

		$listaPlugins = $this->buscaPuginBkp();

		foreach ($listaPlugins as $backup) {
			$classe = $backup->classe.'EnrolBkp';
			$nomeArquivo = $CFG->dirroot.'/'.$backup->local.'/'.$classe.'.php';
			if(file_exists($nomeArquivo)){
				require_once $nomeArquivo;
				$execute = new $classe;
				$execute->setUser($this->getUser());
				$execute->setCourse($this->getCourse());
				$execute->executeBackup();
			}
		}
	}
}

?>