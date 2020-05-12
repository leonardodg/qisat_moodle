<?php 
/**
 * Classe responsável por padronizar a implementação dos back-ups da funcionalidade de multi-matrícula
 *
 *
 * @author Deyvison Fernandes
 *
 */
defined('MOODLE_INTERNAL') || die();

abstract class AbstractEnrolBkp{

	private $user;
	private $course;

	/*Função padrão que deve ser implementada para a execução dos back-ups*/
	abstract function executeBackup();

	public function setUser($user){
		$this->user = $user;
	}

	public function getUser(){
		return $this->user;
	}

	public function setCourse($course){
		$this->course = $course;
	}

	public function getCourse(){
		return $this->course;
	}
}
?>