<?php 

class block_faq extends block_base {

	function init() {
		$this->title = get_string('blockname', 'block_faq');
		$this->version = 2015091000;
	}

    function applicable_formats() {
        return array('course' => true, 'site' => true, 'my' => true);
    }

    function specialization() {
        global $CFG;

        if (empty($this->config->title) ) {
            $this->title = get_string('blockname', 'block_faq');
        } else {
            $this->title = $this->config->title;
        }
    }

    function get_content() {
        global $CFG, $COURSE;

		if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = ''; 
        $this->content->header = $this->title;

        if (empty($this->instance)) {
            // We're being asked for content without an associated instance
            return $this->content;
        }

        $cid = $COURSE->id;

		if(has_capability('block/faq:manage', $this->page->context)){
			$listaLink = array(html_writer::link(new moodle_url('/blocks/faq/gerenciarcategorias.php',array('cid'=>$cid)), get_string('gerenciarcategorias','block_faq')),
                               html_writer::link(new moodle_url('/blocks/faq/visualizarcategorias.php',array('cid'=>$cid)), get_string('visualizarcategorias','block_faq')));
            $this->content->text .= html_writer::alist($listaLink);
		}

        return $this->content;
    }

    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return false;
    }

    function instance_allow_config() {
        return false;
    }

}
?>
