<?php
require_once '../../../../../config.php';
$curso = required_param('curso', PARAM_INT);

global $DB;
$sql = 'SELECT g.id, g.name
        FROM {groups} g
        INNER JOIN {groups_members} gm on gm.groupid = g.id
        INNER JOIN {user} u on u.id = gm.userid
        INNER JOIN {course} c ON g.courseid = c.id
        INNER JOIN {tira_duvidas} td ON td.idcurso = c.id and u.id = td.iduser
        WHERE td.resposta IS NULL AND g.courseid = ?
        GROUP BY g.id';

$listaGrupos = $DB->get_records_sql($sql, [$curso]);

echo json_encode(array_values($listaGrupos));
?>