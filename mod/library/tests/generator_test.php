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
 * mod_library generator tests
 *
 * @package    mod_library
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Genarator tests class for mod_library.
 *
 * @package    mod_library
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_library_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('library', array('course' => $course->id)));
        $library = $this->getDataGenerator()->create_module('library', array('course' => $course));
        $records = $DB->get_records('library', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($library->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another library');
        $library = $this->getDataGenerator()->create_module('library', $params);
        $records = $DB->get_records('library', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another library', $records[$library->id]->name);

        // Examples of adding a library with files (do not validate anything, just check for exceptions).
        $params = array(
            'course' => $course->id,
            'files' => file_get_unused_draft_itemid()
        );
        $usercontext = context_user::instance($USER->id);
        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                'contextid' => $usercontext->id, 'itemid' => $params['files'],
                'filename' => 'file1.txt', 'filepath' => '/');
        $fs = get_file_storage();
        $fs->create_file_from_string($filerecord, 'Test file contents');
        $library = $this->getDataGenerator()->create_module('library', $params);
    }
}
