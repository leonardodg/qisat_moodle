<?php
// This file is part of Moodle - http://moodle.org/
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
 * mod_faq generator tests
 *
 * @package    mod_faq
 * @category   test
 * @copyright  2015 Marina Glancy, Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Genarator tests class for mod_faq.
 *
 * @package    mod_faq
 * @category   test
 * @copyright  2015 Marina Glancy, Inty Castillo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_faq_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('faq', array('course' => $course->id)));
        $faq = $this->getDataGenerator()->create_module('faq', array('course' => $course));
        $records = $DB->get_records('faq', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($faq->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another faq');
        $faq = $this->getDataGenerator()->create_module('faq', $params);
        $records = $DB->get_records('faq', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another faq', $records[$faq->id]->name);
    }
}
