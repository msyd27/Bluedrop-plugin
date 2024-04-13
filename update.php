<?php
/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_bluedrop
 * @copyright   2023 Muteeb Syed
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// Check if the "update" button was clicked
if (isset($_POST['update'])) {
    global $DB;

    // Get the maximum ID from the table
    $maxId = $DB->get_field_sql('SELECT MAX(bluedrop_id) FROM {local_bluedrop_table}', array());

    // If no records exist, set the maximum ID to 0
    if ($maxId === null) {
        $maxId = 0;

        // Reset the auto increment value for the primary key column
        $DB->execute("ALTER TABLE {local_bluedrop_table} AUTO_INCREMENT = 1");
    }

    $sqlUpdate = "INSERT INTO {local_bluedrop_table} (training_standard_key, timecompleted, external_class_id, instructor_names, evaluator_names, id, firstname, lastname, email, DOB, address, city, province, postal_code, country, phone1, phone2, fullname, flag)
    SELECT
        IFNULL(MAX(CASE WHEN b.fieldid = 20 THEN b.charvalue END), '') AS training_standard_key, 
        c.timecompleted, 
        a.id AS external_class_id,
        '' AS instructor_names,
        '' AS evaluator_names,
        u.id,
        u.firstname,
        u.lastname,
        u.email,
        IFNULL(MAX(CASE WHEN d.fieldid = 8 THEN d.data END), '') AS DOB,
        u.address,
        u.city,
        IFNULL(MAX(CASE WHEN d.fieldid = 11 THEN d.data END), '') AS province,
        IFNULL(MAX(CASE WHEN d.fieldid = 10 THEN d.data END), '') AS postal_code,
        u.country,
        u.phone1,
        u.phone2,
        a.fullname,
        1 AS flag
    FROM {user} u
    JOIN {course_completions} c ON u.id = c.userid
    JOIN {course} a ON a.id = c.course
    JOIN {user_info_data} d ON u.id = d.userid
    JOIN {customfield_data} b ON b.instanceid = a.id
    WHERE d.fieldid IN (8, 10, 11)
    GROUP BY u.id, u.firstname, u.lastname, u.email, u.address, c.id, c.timecompleted, a.fullname
    HAVING NOT EXISTS (
        SELECT 1
        FROM {local_bluedrop_table} t
        WHERE t.firstname = u.firstname
        AND t.lastname = u.lastname
        AND t.email = u.email
        AND t.DOB = IFNULL(MAX(CASE WHEN d.fieldid = 8 THEN d.data END), '')
        AND t.address = u.address
        AND t.province = IFNULL(MAX(CASE WHEN d.fieldid = 11 THEN d.data END), '')
        AND t.postal_code = IFNULL(MAX(CASE WHEN d.fieldid = 10 THEN d.data END), '')
        AND t.fullname = a.fullname
    )";

    $params = [
        'newid' => $maxId + 1, // Increment the maximum ID to start from 1
    ];

    $DB->execute($sqlUpdate, $params);
    // Set the default value for id_bin using a generated UUID
    $DB->execute("UPDATE {local_bluedrop_table} SET id_bin = UUID_TO_BIN(UUID(),1)");

    // Redirect back to the display page after the update is done
    redirect(new moodle_url('/local/bluedrop/display.php'));
}

?>
