<?php

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_bluedrop
 * @category    admin
 * @copyright   2023 Muteeb Syed
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php'); // Includ Moodle config 

$PAGE->set_url(new moodle_url('/local/bluedrop/delete.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Delete Record');

global $DB, $PAGE, $OUTPUT;

// Get the record ID from the query string
$recordId = required_param('bluedrop_id', PARAM_INT);

// Retrieve the record from the local_bluedrop_table
$record = $DB->get_record('local_bluedrop_table', ['bluedrop_id' => $recordId]);

if (!$record) {
    // Redirect back to the display page if the record is not found
    redirect(new moodle_url('/local/bluedrop/display.php'));
}

// Check if the user has confirmed the deletion
if (optional_param('confirm', 0, PARAM_BOOL)) {
    // Perform the deletion of the record
    $DB->delete_records('local_bluedrop_table', ['bluedrop_id' => $recordId]);

    // Display a success message
    echo $OUTPUT->header();
    echo '<h2>Record Deleted</h2>';
    echo '<p>The record with ID ' . $recordId . ' has been successfully deleted.</p>';
    echo '<a href="display.php">Go back to Records</a>';
    echo $OUTPUT->footer();
} else {
    // Display the confirmation message
    echo $OUTPUT->header();
    echo '<h2>Confirm Deletion</h2>';
    echo '<p>Are you sure you want to delete the record with ID ' . $recordId . '?</p>';
    echo '<a href="delete.php?bluedrop_id=' . $recordId . '&confirm=1" class="btn btn-danger">Yes, Delete</a>';
    echo '<a href="display.php" class="btn btn-primary">No, Cancel</a>';
    echo $OUTPUT->footer();
}
