<?php

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_bluedrop
 * @category    admin
 * @copyright   2023 Muteeb Syed
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php'); // Include the Moodle config file if not already included
require_once($CFG->dirroot . '/local/bluedrop/classes/form/edit.php');

$PAGE->set_url(new moodle_url('/local/bluedrop/edit.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit');

$params = $_GET;
$mform = new edit(null, ['params' => $params]);


// Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/bluedrop/display.php', 'Edit Form Cancelled');
} elseif ($data = $mform->get_data()) {
    // Form submitted and data is valid, process the update operation

    // Retrieve the data from the form
    $bluedrop_id = $data->bluedrop_id;
    $training_standard_key = $data->training_standard_key;
    $timecompleted = $data->timecompleted;
    $instructor_names = $data->instructor_names;
    $evaluator_names = $data->evaluator_names;
    $firstname = $data->firstname;
    $lastname = $data->lastname;
    $email = $data->email;
    $dob = $data->dob;
    $address = $data->address;
    $city = $data->city;
    $province = $data->province;
    $postal_code = $data->postal_code;
    $country = $data->country;
    $phone1 = $data->phone1;
    $phone2 = $data->phone2;
    $fullname = $data->fullname;

    // Update the row in the database
    $record = new stdClass();
    $record->bluedrop_id = $bluedrop_id;
    $record->training_standard_key = $training_standard_key;
    $record->timecompleted = $timecompleted;
    $record->instructor_names = $instructor_names;
    $record->evaluator_names = $evaluator_names;
    $record->firstname = $firstname;
    $record->lastname = $lastname;
    $record->email = $email;
    $record->dob = $dob;
    $record->address = $address;
    $record->city = $city;
    $record->province = $province;
    $record->postal_code = $postal_code;
    $record->country = $country;
    $record->phone1 = $phone1;
    $record->phone2 = $phone2;
    $record->fullname = $fullname;


    if ($DB->update_record('local_bluedrop_table', $record)) {
        // Update successful, redirect to display page
        redirect($CFG->wwwroot . '/local/bluedrop/display.php', 'Record Updated');
    } else {
        // Update failed, display an error message
        echo $OUTPUT->header();
        echo 'Error: Failed to update the record.';
        echo $OUTPUT->footer();
        exit;
    }
} else {
    // Display the form
    echo $OUTPUT->header();
    echo '<h2>Bluedrop Information</h2>';
    $mform->display();
    echo '<br>';
    echo '<a href="display.php" class="btn btn-primary">Go back to Display</a>';
    echo $OUTPUT->footer();
}
