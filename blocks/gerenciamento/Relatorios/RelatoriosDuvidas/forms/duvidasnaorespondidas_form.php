<?php
require_once($CFG->libdir.'/formslib.php');

class blocks_gerenciamento_duvidasnaorespondidas_form extends moodleform {

    function definition () {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'titulo', get_string('consultar', 'block_gerenciamento'));

        $sql = 'SELECT c.id, c.shortname, c.fullname
                FROM {course} c
                INNER JOIN {tira_duvidas} td ON td.idcurso = c.id
                WHERE td.resposta IS NULL
                GROUP BY c.id';

        $cursos = $DB->get_records_sql($sql);

        $optionsCurso = ['' => get_string('allcursos', 'block_gerenciamento')];
        foreach ($cursos as $c) {
            $optionsCurso[$c->id] = $c->shortname;
        }

        $mform->addElement('select', 'select_curso', get_string('escolhacurso','block_gerenciamento'), $optionsCurso);

        $optionsTurma = ['' => get_string('escolhacurso', 'block_gerenciamento')];
        $mform->addElement('select', 'select_grupo', get_string('escolhaturma','block_gerenciamento'), $optionsTurma, ['disabled']);

        $script = '$("#id_select_curso").on("change", function(){
                        $("input[name=\"idgrupo\"").val("");
                        setOptionSelect();
                   });

                   $(window).on("load", function(){
                        setOptionSelect();
                   });

                   function setOptionSelect(){
                        var idCurso = $("#id_select_curso").val();

                        if(idCurso != ""){
                            $.post("ajax/listarGrupos.php", {curso: idCurso}, function(data){
                                $("#id_select_grupo").html("<option value=\"\">'.get_string('todasTurmas','block_gerenciamento').'</option>");

                                data.forEach(function(curso) {
                                    $("#id_select_grupo").append("<option value=\""+curso.id+"\">"+curso.name+"</option>");
                                });

                                $("#id_select_grupo").removeAttr("disabled");

                                $("#id_select_grupo").val($("input[name=\"idgrupo\"").val());
                            }, "json");
                        }else{
                            $("#id_select_grupo").html("<option value=\"\">'.get_string('escolhacurso','block_gerenciamento').'</option>");
                                $("#id_select_grupo").attr("disabled", "disabled");
                        }
                   }
                   $("#id_select_grupo").on("change", function(){
                        $("input[name=\"idgrupo\"").val($(this).val());
                   });';

        $mform->addElement('html', html_writer::tag('script', $script));
        $mform->addElement('hidden', 'idgrupo');

        $mform->addElement('submit', 'submitbutton', get_string('enviar', 'block_gerenciamento'));
    }
}