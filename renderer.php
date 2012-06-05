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
 * Renderer for outputting the collapsed topics course format.
 *
 * @package format_topics
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/course/format/topcoll/lib.php');

/**
 * Basic renderer for collapsed topics format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_topcoll_renderer extends format_section_renderer_base {

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('sectionname','format_topcoll');
    }

    /**
     * Generate the content to displayed on the right part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_right_content($section, $course, $onsectionpage) {
        $o = $this->output->spacer();

        if ($section->section != 0) {
            $controls = $this->section_edit_controls($course, $section, $onsectionpage);
            if (!empty($controls)) {
                $o = implode('<br />', $controls);
            } else {
			// Get the specific words from the language files.
			global $tcsetting;
$topictext = null;
if (($tcsetting->layoutstructure == 1) || ($tcsetting->layoutstructure == 4)) {
    $topictext = get_string('setlayoutstructuretopic', 'format_topcoll');
} else {
    $topictext = get_string('setlayoutstructureweek', 'format_topcoll');
}
        $o = html_writer::tag('span', $topictext . '<br />' . $section->section, array('class' => 'cps_centre'));
			}
        }
		

        return $o;
    }

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        if (!has_capability('moodle/course:update', context_course::instance($course->id))) {
            return array();
        }

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

		global $tcsetting;
		$controls = array();
		if (($tcsetting->layoutstructure == 1) || ($tcsetting->layoutstructure == 4)) {
        if ($course->marker == $section->section) {  // Show the "light globe" on/off.
            $url->param('marker', 0);
            $controls[] = html_writer::link($url,
                                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
                                    'class' => 'icon ', 'alt' => get_string('markedthistopic'))),
                                array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));
        } else {
            $url->param('marker', $section->section);
            $controls[] = html_writer::link($url,
                            html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
                                'class' => 'icon', 'alt' => get_string('markthistopic'))),
                            array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));
        }
}

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
    }
	
    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage) {
        global $PAGE;

        $o = '';
        $currenttext = '';
        $sectionstyle = '';
        $toggletext = get_string('topcolltoggle', 'format_topcoll'); // The word 'Toggle'.

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if ($this->is_section_current($section, $course)) {
                $sectionstyle = ' current';
            }
        }

        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));

        if (!$onsectionpage) {

        $o.= html_writer::start_tag('div', array('class' => 'sectionhead toggle','id' => 'toggle-'.$section->section));
		
        $title = $this->section_title($section, $course);
		if (empty($section->summary)) {
        $o.= html_writer::start_tag('a', array('class' => 'cps_nosumm cps_a', 'href' => '#', 'onclick' => 'toggle_topic(this,' . $section->section . '); return false;'));
		$o.= $title.' - '.$toggletext;
        } else {
        $o.= html_writer::start_tag('a', array('class' => 'cps_a', 'href' => '#', 'onclick' => 'toggle_topic(this,' . $section->section . '); return false;'));
		$o.= $title.' - '.$toggletext.'<br />'.$section->summary;
		}
        $context = context_course::instance($course->id);
        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context)) {
            $url = new moodle_url('/course/editsection.php', array('id'=>$section->id));

            if ($onsectionpage) {
                $url->param('sectionreturn', 1);
            }

            $o.= html_writer::link($url,
                html_writer::empty_tag('img', array('src' => $this->output->pix_url('t/edit'), 'class' => 'iconsmall edit')),
                array('title' => get_string('editsummary')));
        }
		$o.= html_writer::end_tag('a');
		$o.= html_writer::end_tag('div');
        $o.= html_writer::start_tag('div', array('class' => 'sectionbody toggledsection','id' => 'toggledsection-'.$section->section));

        } else {
		        $o.= html_writer::start_tag('div', array('class' => 'sectionbody'));
        }

        //$o.= html_writer::start_tag('div', array('class' => 'summary'));
        //$o.= $this->format_summary_text($section);

        //$o.= html_writer::end_tag('div');

        $o .= $this->section_availability_message($section);

		//print_object($section);
		
        return $o;
    }

    /**
     * Generate the display of the footer part of a section
     *
     * @return string HTML to output.
     */
    protected function section_footer() {
        $o = html_writer::end_tag('div');
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');

        return $o;
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param array $mods used for print_section()
     * @param array $modnames used for print_section()
     * @param array $modnamesused used for print_section()
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;
		global $tcsetting;

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course);

        // Now the list of sections..
        echo $this->start_section_list();

		// Collapsed Topics settings and cookie consent.
		echo $this->cookie_consent($course);
		echo $this->settings($course);
		
        // General section if non-empty.
        $thissection = $sections[0];
        unset($sections[0]);
        if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing()) {
            echo $this->section_header($thissection, $course, true);
            print_section($course, $thissection, $mods, $modnamesused, true);
            if ($PAGE->user_is_editing()) {
                print_section_add_menus($course, 0, $modnames);
            }
            echo $this->section_footer();
        }
		
        // Collapsed Topics all toggles.
		echo $this->toggle_all();

        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);
        for ($section = 1; $section <= $course->numsections; $section++) {
            if (!empty($sections[$section])) {
                $thissection = $sections[$section];
            } else {
                // This will create a course section if it doesn't exist..
                $thissection = get_course_section($section, $course->id);

                // The returned section is only a bare database object rather than
                // a section_info object - we will need at least the uservisible
                // field in it.
                $thissection->uservisible = true;
                $thissection->availableinfo = null;
                $thissection->showavailability = 0;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but showavailability is turned on
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available && $thissection->showavailability);
            if (!$showsection) {
                // Hidden section message is overridden by 'unavailable' control
                // (showavailability option).
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section);
                }

                unset($sections[$section]);
                continue;
            }
//print_object($thissection);
            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course);
            } else {
                echo $this->section_header($thissection, $course, false);
                if ($thissection->uservisible) {
                    print_section($course, $thissection, $mods, $modnamesused);

                    if ($PAGE->user_is_editing()) {
                        print_section_add_menus($course, $section, $modnames);
                    }
                }
                echo $this->section_footer();
            }

            unset($sections[$section]);
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            $modinfo = get_fast_modinfo($course);
            foreach ($sections as $section => $thissection) {
                if (empty($modinfo->sections[$section])) {
                    continue;
                }
                echo $this->stealth_section_header($section);
                print_section($course, $thissection, $mods, $modnamesused);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Increase number of sections.
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                array('courseid' => $course->id,
                      'increase' => true,
                      'sesskey' => sesskey()));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon.get_accesshide($straddsection), array('class' => 'increase-sections'));

            if ($course->numsections > 0) {
                // Reduce number of sections sections.
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    array('courseid' => $course->id,
                          'increase' => false,
                          'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon.get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }

            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }

    }
	
	/**
     * Is the section passed in the current section?
     *
     * @param stdClass $section The course_section entry from the DB
     * @param stdClass $course The course entry from DB
     * @return bool true if the section is current
     */
    protected function is_section_current($section, $course) {
	global $tcsetting;
	if (($tcsetting->layoutstructure == 2) || ($tcsetting->layoutstructure == 3)) {
        if ($section->section < 1) {
            return false;
        }

        $timenow = time();
        $dates = format_topcoll_get_section_dates($section, $course);

        return (($timenow >= $dates->start) && ($timenow < $dates->end));
		} else {
		  return parent::is_section_current($section, $course);
		}
    }

    // Collapsed Topics non-overridden additions.
	
	protected $screenreader;
	protected $cookieconsent;
	//protected $tcsetting;
	
	public function set_screen_reader($screenreader) {
	    $this->screenreader = $screenreader;
	}
	
	public function set_cookie_consent($cookieconsent) {
	    $this->cookieconsent = $cookieconsent;
    }
	
	/*
	public function set_tc_setting(stdClass $setting) {
	    $this->tcsetting = $setting;
		//print_object($this->tcsetting);
	}
	
	public function get_tc_setting() {
	    //print_object($this->tcsetting);
	    return $this->tcsetting;
	}*/
	
    public function cookie_consent($course) {	
	global $USER;
	$o = '';
	
	if (($this->screenreader == false) && ($this->cookieconsent == 1)) {
        // Display message to ask for consent.
        $o.= html_writer::start_tag('li', array('class' => 'tcsection main clearfix'));

        $o.= html_writer::tag('div', $this->output->spacer(), array('class' => 'left side'));
        $o.= html_writer::tag('div', $this->output->spacer(), array('class' => 'right side'));

        $o.= html_writer::start_tag('div', array('class' => 'content'));
		$o.= html_writer::start_tag('div', array('class' => 'sectionbody cookieConsentContainer'));
        $o.= html_writer::tag('a',  html_writer::tag('div','',array('id' => 'set-cookie-consent')), array('title' => get_string('cookieconsentform','format_topcoll'),'href' =>'format/topcoll/forms/cookie_consent.php?userid=' . $USER->id.'&courseid=' . $course->id . '&sesskey=' . sesskey()));
		$o.= html_writer::tag('div',  $this->output->heading(get_string('setcookieconsent','format_topcoll'), 3, 'sectionname').get_string('cookieconsent','format_topcoll'), null);
	    $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');
		
    }
        return $o;	
    }

    public function settings($course) {
	global $PAGE;
	
	$o = '';
	
	$coursecontext = context_course::instance($course->id);	
	if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $coursecontext)) {
        $o.= html_writer::start_tag('li', array('class' => 'tcsection main clearfix'));

        //$leftcontent = $this->section_left_content($section, $course, false);
        //$o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));
        $o.= html_writer::tag('div', $this->output->spacer(), array('class' => 'left side'));

        //$rightcontent = $this->section_right_content($section, $course, false);
        //$o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::tag('div', $this->output->spacer(), array('class' => 'right side'));

        $o.= html_writer::start_tag('div', array('class' => 'content'));
		$o.= html_writer::start_tag('div', array('class' => 'sectionbody'));
        $o.= html_writer::tag('a',  html_writer::tag('div','',array('id' => 'set-settings')), array('title' => get_string("settings"),'href' =>'format/topcoll/forms/settings.php?id=' . $course->id . '&sesskey=' . sesskey()));
	    $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');
    }
        return $o;
    }
	
	public function toggle_all() {
		$o = '';
	if ($this->screenreader == false) { // No need to show if in screen reader mode.
    $toggletext = get_string('topcolltoggle', 'format_topcoll'); // The word 'Toggle'.
        // Toggle all.

		$o.= html_writer::start_tag('li', array('class' => 'tcsection main clearfix', 'id' => 'toggle-all'));

        $o.= html_writer::tag('div', $this->output->spacer(), array('class' => 'left side'));
        $o.= html_writer::tag('div', $this->output->spacer(), array('class' => 'right side'));

        $o.= html_writer::start_tag('div', array('class' => 'content'));
		$o.= html_writer::start_tag('div', array('class' => 'sectionbody'));
		$o.= html_writer::start_tag('h4', null);
        $o.= html_writer::tag('a', get_string('topcollopened', 'format_topcoll'), array('class' => 'on','href' =>'#', 'onclick' => 'all_opened(); return false;'));
        $o.= html_writer::tag('a', get_string('topcollclosed', 'format_topcoll'), array('class' => 'off','href' =>'#', 'onclick' => 'all_closed(); return false;'));
        $o.= html_writer::tag('span', get_string('topcollall', 'format_topcoll'), null);
	    $o.= html_writer::end_tag('h4');
	    $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');
}
return $o;
}

}
