<?php

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_bluedrop
 * @category    admin
 * @copyright   2023 Muteeb Syed
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class edit extends moodleform
{
    //Add elements to form
    protected $existingData; // Store the existing data

    public function set_existing_data($data)
    {
        $this->existingData = $data;
    }
    public function definition()
    {
        global $CFG;

        // Retrieve the passed parameters from the URL
        $params = $this->_customdata['params'];

        // Access the parameter values and set them as default values for the form elements
        $bluedrop_id = $params['bluedrop_id'];
        $training_standard_key = $params['training_standard_key'];
        $timecompleted = $params['timecompleted'];
        $instructor_names = $params['instructor_names'];
        $evaluator_names = $params['evaluator_names'];

        $firstname = $params['firstname'];
        $lastname = $params['lastname'];
        $email = $params['email'];
        $dob = $params['dob'];
        $address = $params['address'];
        $city = $params['city'];
        $province = $params['province'];
        $postal_code = $params['postal_code'];
        $country = $params['country'];
        $phone1 = $params['phone1'];
        $phone2 = $params['phone2'];
        $fullname = $params['fullname'];

        $mform = $this->_form;

        $mform->addElement('hidden', 'bluedrop_id', $bluedrop_id);

        $mform->addElement('text', 'training_standard_key', 'Training Standard Key');
        $mform->setType('training_standard_key', PARAM_INT);
        $mform->setDefault('training_standard_key', $training_standard_key);

        $mform->addElement('text', 'timecompleted', 'Completion Date');
        $mform->setType('timecompleted', PARAM_INT);
        $mform->setDefault('timecompleted', $timecompleted);

        $mform->addElement('text', 'instructor_names', 'Instructor Names');
        $mform->setType('instructor_names', PARAM_TEXT);
        $mform->setDefault('instructor_names', $instructor_names);

        $mform->addElement('text', 'evaluator_names', 'Evaluator Names');
        $mform->setType('evaluator_names', PARAM_TEXT);
        $mform->setDefault('evaluator_names', $evaluator_names);

        $mform->addElement('text', 'firstname', 'First Name');
        $mform->setType('firstname', PARAM_NOTAGS);
        $mform->setDefault('firstname', $firstname);

        $mform->addElement('text', 'lastname', 'Last Name');
        $mform->setType('lastname', PARAM_NOTAGS);
        $mform->setDefault('lastname', $lastname);

        $mform->addElement('text', 'email', get_string('email'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->setDefault('email', $email);

        $mform->addElement('text', 'dob', 'Date of Birth');
        $mform->setType('dob', PARAM_TEXT);
        $mform->setDefault('dob', $dob);

        $mform->addElement('text', 'address', get_string('address'));
        $mform->setType('address', PARAM_TEXT);
        $mform->setDefault('address', $address);

        $mform->addElement('text', 'city', 'City');
        $mform->setType('city', PARAM_TEXT);
        $mform->setDefault('city', $city);

        $mform->addElement('text', 'province', 'Province');
        $mform->setType('province', PARAM_TEXT);
        $mform->setDefault('province', $province);

        $mform->addElement('text', 'postal_code', 'Postal Code');
        $mform->setType('postal_code', PARAM_TEXT);
        $mform->setDefault('postal_code', $postal_code);

        $mform->addElement('text', 'country', 'Country');
        $mform->setType('country', PARAM_TEXT);
        $mform->setDefault('country', $country);

        $mform->addElement('text', 'phone1', 'Phone #1');
        $mform->setType('phone1', PARAM_TEXT);
        $mform->setDefault('phone1', $phone1);

        $mform->addElement('text', 'phone2', 'Phone #2');
        $mform->setType('phone2', PARAM_TEXT);
        $mform->setDefault('phone2', $phone2);

        $mform->addElement('text', 'fullname', 'Full Name');
        $mform->setType('fullname', PARAM_TEXT);
        $mform->setDefault('fullname', $fullname);

        $this->add_action_buttons();
    }


    //Custom validation should be added here
    function validation($data, $files)
    {
        return array();
    }
}
