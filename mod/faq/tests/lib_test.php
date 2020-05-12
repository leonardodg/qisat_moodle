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
 * Unit tests for some mod URL lib stuff.
 *
 * @package    mod_faq
 * @category   phpunit
 * @copyright  2015 Petr Skoda, Inty Castillo {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * mod_faq tests
 *
 * @package    mod_faq
 * @category   phpunit
 * @copyright  2015 Petr Skoda, Inty Castillo {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_faq_lib_testcase extends basic_testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/faq/locallib.php');
    }

    /**
     * Tests the faq_appears_valid_faq function
     * @return void
     */
    public function test_faq_appears_valid_faq() {
        $this->assertTrue(faq_appears_valid_faq('http://example'));
        $this->assertTrue(faq_appears_valid_faq('http://www.example.com'));
        $this->assertTrue(faq_appears_valid_faq('http://www.exa-mple2.com'));
        $this->assertTrue(faq_appears_valid_faq('http://www.example.com/~nobody/index.html'));
        $this->assertTrue(faq_appears_valid_faq('http://www.example.com#hmm'));
        $this->assertTrue(faq_appears_valid_faq('http://www.example.com/#hmm'));
        $this->assertTrue(faq_appears_valid_faq('http://www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(faq_appears_valid_faq('http://www.example.com/žlutý koníček/lala.txt#hmmmm'));
        $this->assertTrue(faq_appears_valid_faq('http://www.example.com/index.php?xx=yy&zz=aa'));
        $this->assertTrue(faq_appears_valid_faq('https://user:password@www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(faq_appears_valid_faq('ftp://user:password@www.example.com/žlutý koníček/lala.txt'));

        $this->assertFalse(faq_appears_valid_faq('http:example.com'));
        $this->assertFalse(faq_appears_valid_faq('http:/example.com'));
        $this->assertFalse(faq_appears_valid_faq('http://'));
        $this->assertFalse(faq_appears_valid_faq('http://www.exa mple.com'));
        $this->assertFalse(faq_appears_valid_faq('http://www.examplé.com'));
        $this->assertFalse(faq_appears_valid_faq('http://@www.example.com'));
        $this->assertFalse(faq_appears_valid_faq('http://user:@www.example.com'));

        $this->assertTrue(faq_appears_valid_faq('lalala://@:@/'));
    }
}