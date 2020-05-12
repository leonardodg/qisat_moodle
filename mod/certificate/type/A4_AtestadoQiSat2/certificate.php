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
    $codey = 168.5;
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
            $nomeAbreviado .= mb_strtoupper(substr($value, 0, 1), 'UTF-8').'. ';
        }
    }
}else{
    $nomeAbreviado = $nomeUsuario;
}

// Add text
$pdf->SetTextColor(51, 51, 51);
certificate_print_text($pdf, $x + 44, $y + 46, 'L', 'Helvetica', '', 20, 'Atestamos que o(a) Sr.(a)');
certificate_print_text($pdf, $x + 44, $y + 58, 'L', 'Helvetica', 'B', 33, mb_strtoupper($nomeAbreviado, 'UTF-8'));
certificate_print_text($pdf, $x + 44, $y + 76, 'L', 'Helvetica', '', 20, 'Participou do(a)');

$pdf->SetTextColor(14,81,162);
$espaco = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
certificate_print_text($pdf, $x + 44, $y + 76, 'L', 'Helvetica', 'B', 19.5, $espaco . format_string($course->fullname), 200);

$pdf->SetTextColor(51, 51, 51);
$totalCaracteresNome = strlen(format_string($course->fullname));
$totalCaracteresNome += strlen($nomeAbreviado);

if ($totalCaracteresNome > 141) {
    $y += 15;
}

certificate_print_text($pdf, $x + 44, $y + 94, 'L', 'Helvetica', '', 20, 'Carga Horária: ' . $certificate->printhours . ' horas');
$period = certificate_get_period($certificate, $certrecord, $course, $user->id);
certificate_print_text($pdf, $x + 44, $y + 103, 'L', 'Helvetica', '', 20, 'Período: ' . date("d/m/Y", $period->timestart) . ' a ' . date("d/m/Y", $period->timeend));

if($imprimirNota && $certificate->printgrade == 1) {
    certificate_print_text($pdf, $x + 44, $y + 112, 'L', 'Helvetica', '', 20, 'Nota:');
    certificate_print_text($pdf, $x + 61, $y + 112, 'L', 'Helvetica', '', 20, certificate_get_grade($certificate, $course, $user->id, true));
    $y += 9;
}

certificate_print_text($pdf, $x + 44, $y + 112, 'L', 'Helvetica', '', 20, 'Ambiente: AVA - Ambiente Virtual de Aprendizagem');
//certificate_print_text($pdf, $x + 44, $y + 121, 'L', 'Helvetica', '', 20, 'Florianópolis - SC, ' . date("d/m/Y") . '.');
$pdf->SetTextColor(110, 110, 110);
//certificate_print_text($pdf, $x + 18.4, $codey, 'L', 'Helvetica', '', 10, 'Para validar a autenticidade deste certificado acesse <font color="rgb(14,81,162)">http://www.qisat.com.br/certificado</font> e digite o código de validação <b>' . certificate_get_code($certificate, $certrecord) . '</b>.');
certificate_print_text($pdf, $x + 18.4, $codey, 'L', 'Helvetica', '', 14, 'Para validar a autenticidade deste certificado acesse <br><font color="rgb(14,81,162)">'.$CFG->wwwroot.'/blocks/verify_certificate/index.php?certnumber='.certificate_get_code($certificate, $certrecord).'</font>.');

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
