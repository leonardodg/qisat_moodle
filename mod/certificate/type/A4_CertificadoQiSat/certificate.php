<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A4_non_embedded certificate type
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

$imprimirNota = optional_param('nota', 0, PARAM_INT);

$pdf = new PDF($certificate->orientation, 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($certificate->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 10;
    $y = 30;
    $sealx = 230;
    $sealy = 150;
    $sigx = 47;
    $sigy = 155;
    $custx = 47;
    $custy = 155;
    $wmarkx = 40;
    $wmarky = 31;
    $wmarkw = 212;
    $wmarkh = 148;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codey = 180;
} else { // Portrait
    $x = 10;
    $y = 40;
    $sealx = 150;
    $sealy = 220;
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;
}

$user = $USER;
if(has_capability('block/gerenciamento:visualizarcertificado', $context))
    $user = $DB->get_record('user', array('id' => $certrecord->userid));

// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text
//$pdf->SetTextColor(0, 0, 120);
//certificate_print_text($pdf, $x, $y, 'C', 'Helvetica', '', 30, get_string('title', 'certificate'));

$nomeUsuario = ucwords(mb_strtolower(fullname($user), 'UTF-8'));
$nomeAbreviado = '';

if(strlen($nomeUsuario) > 27){
    $expNome = explode(' ', $nomeUsuario);

    foreach ($expNome as $key => $value) {
        if($key == 0){
            $nomeAbreviado = $value.' ';
        }elseif ($key == count($expNome)-1) {
            $nomeAbreviado .= $value;
        }elseif(strlen($value) <= 3){
                $nomeAbreviado .= $value.' ';
        }else{
            $nomeAbreviado .= strtoupper(substr($value, 0, 1)).'. ';
        }
    }
}else{
    $nomeAbreviado = $nomeUsuario;
}


$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x + 55, $y + 43, 'L', 'Helvetica', 'I', 16, 'Conferido a ');
certificate_print_text($pdf, $x + 87, $y + 40.9, 'L', 'Helvetica', 'BI', 22, $nomeAbreviado);
certificate_print_text($pdf, $x + 55, $y + 53, 'L', 'Helvetica', 'I', 16, 'pela participação no');
certificate_print_text($pdf, $x + 108, $y + 53, 'L', 'Helvetica', 'BI', 16, format_string($course->fullname));

$totalCaracteresNome = strlen(format_string($course->fullname));
if($totalCaracteresNome > 57 && $totalCaracteresNome < 128){
    $y += 10;
}elseif ($totalCaracteresNome > 127) {
    $y += 20;
}


certificate_print_text($pdf, $x + 55, $y + 63, 'L', 'Helvetica', 'BI', 16, 'Carga Horária:');
certificate_print_text($pdf, $x + 96, $y + 63, 'L', 'Helvetica', 'I', 16, $certificate->printhours . ' horas');
certificate_print_text($pdf, $x + 124, $y + 63, 'L', 'Helvetica', 'BI', 16, 'Período:');

$period = certificate_get_period($certificate, $certrecord, $course, $user->id);

certificate_print_text($pdf, $x + 150, $y + 63, 'L', 'Helvetica', 'I', 16, date("d/m/Y", $period->timestart) . ' a ' . date("d/m/Y", $period->timeend));

if($imprimirNota && $certificate->printgrade == 1) {
    certificate_print_text($pdf, $x + 55, $y + 73, 'L', 'Helvetica', 'BI', 16, 'Nota:');
    certificate_print_text($pdf, $x + 73, $y + 73, 'L', 'Helvetica', 'I', 16, certificate_get_grade($certificate, $course, $user->id, true));
    $y += 10;
}
certificate_print_text($pdf, $x + 55, $y + 73, 'L', 'Helvetica', 'BI', 16, 'Ambiente:');
certificate_print_text($pdf, $x + 85, $y + 73, 'L', 'Helvetica', 'I', 16, 'AVA - Ambiente Virtual de Aprendizagem');
certificate_print_text($pdf, $x + 120, $y + 87, 'L', 'Helvetica', 'I', 16, 'Florianópolis - SC, ' . date("d/m/Y") . '.');

//certificate_print_text($pdf, $x + 55, $codey, 'L', 'Helvetica', '', 10, 'Para validar a autenticidade deste certificado acesse');
//$pdf->SetTextColor(5, 88, 156);
//certificate_print_text($pdf, $x + 139, $codey, 'L', 'Helvetica', 'I', 10, 'http://www.qisat.com.br/certificado');
//$pdf->SetTextColor(0, 0, 0);
//certificate_print_text($pdf, $x + 194, $codey, 'L', 'Helvetica', '', 10, 'e digite o código de validação');
//certificate_print_text($pdf, $x + 241, $codey, 'L', 'Helvetica', 'BI', 10, certificate_get_code($certificate, $certrecord) . '.');
//certificate_print_text($pdf, $x + 55, $codey, 'L', 'Helvetica', '', 10, 'Para validar a autenticidade deste certificado acesse http://www.qisat.com.br/certificado e digite o código de validação ' . certificate_get_code($certificate, $certrecord) . '.');
certificate_print_text($pdf, $x + 55, $codey, 'L', 'Helvetica', 'I', 10, 'Para validar a autenticidade deste certificado acesse <font color="rgb(14,81,162)">'.$CFG->wwwroot.'/blocks/verify_certificate/index.php?certnumber='.certificate_get_code($certificate, $certrecord).'</font>.');

$i = 0;
if ($certificate->printteacher) {
    $context = context_module::instance($cm->id);
    if ($teachers = get_users_by_capability($context, 'mod/certificate:printteacher', '', $sort = 'u.lastname ASC', '', '', '', '', false)) {
        foreach ($teachers as $teacher) {
            $i++;
            certificate_print_text($pdf, $sigx, $sigy + ($i * 4), 'L', 'Times', '', 12, fullname($teacher));
        }
    }
}

certificate_print_text($pdf, $custx, $custy, 'L', null, null, null, $certificate->customtext);
