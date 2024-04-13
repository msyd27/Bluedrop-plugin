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

/**
 * Generate a UUID string based on time and random number
 * It's a hybrid between version 1 and 4. But it's reported as version 4
 *
 * @param boolean $swap false=real UUID | true=swap ciphers to be incremental and no dashes (good for DB indexes)
 * @return string
 */
function uuid($swap = false)
{
    $r = str_split(bin2hex(random_bytes(10)), 4);
    list($low0, $high) = explode(" ", microtime());
    $t = '4' . substr(dechex($high), 0, 8) . substr(dechex($low0 * 1000000), 0, 5) . $r[0];
    list($high, $mid, $low0, $low1) = str_split($t, 4);
    $t = $r[1];
    $t[0] = dechex(hexdec($t[0]) & 0x3 | 0x8);
    if (!$swap) {
        $d = [$low0, $low1, $mid, $high, $t, $r[2], $r[3], $r[4]];
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', $d);
    } else {
        $d = [$high, $mid, $low0, $low1, $t, $r[2], $r[3], $r[4]];
        return vsprintf('%s%s%s%s%s%s%s%s', $d);
    }
}

$networkKey = 'pro-34590';

$PAGE->set_url(new moodle_url('/local/bluedrop/display.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Bluedrop');

global $DB, $PAGE, $OUTPUT;

$sqlCreateTable = "CREATE TABLE IF NOT EXISTS {local_bluedrop_table} (
        bluedrop_id INT AUTO_INCREMENT PRIMARY KEY,
        training_standard_key VARCHAR(50),
        timecompleted INT,
        external_class_id VARCHAR(36),
        instructor_names VARCHAR(100),
        evaluator_names VARCHAR(100),
        id INT,
        firstname VARCHAR(100),
        lastname VARCHAR(100),
        email VARCHAR(100),
        DOB VARCHAR(100),
        address VARCHAR(255),
        city VARCHAR(255),
        province VARCHAR(100),
        postal_code VARCHAR(100),
        country VARCHAR(100),
        phone1 VARCHAR(100),
        phone2 VARCHAR(100),
        fullname VARCHAR(255),
        flag INT DEFAULT 1
    ) AS
    SELECT IFNULL(MAX(CASE WHEN b.fieldid = 20 THEN b.charvalue END), '') AS training_standard_key, c.timecompleted, a.id AS external_class_id, '' AS instructor_names, '' AS evaluator_names,
           u.id, u.firstname, u.lastname, u.email,
           IFNULL(MAX(CASE WHEN d.fieldid = 8 THEN d.data END), '') AS dob,
           u.address, u.city,
           IFNULL(MAX(CASE WHEN d.fieldid = 11 THEN d.data END), '') AS Province,
           IFNULL(MAX(CASE WHEN d.fieldid = 10 THEN d.data END), '') AS Postal_Code,
           u.country, u.phone1, u.phone2,
           a.fullname
    FROM {user} u
    JOIN {course_completions} c ON u.id = c.userid
    JOIN {course} a ON a.id = c.course
    JOIN {user_info_data} d ON u.id = d.userid
    JOIN {customfield_data} b ON b.instanceid = a.id
    WHERE d.fieldid IN (8, 10, 11)
    GROUP BY u.id, u.firstname, u.lastname, u.email, u.address, c.id, c.timecompleted, a.fullname";

$DB->execute($sqlCreateTable);


$columnExists = $DB->get_record_sql(
    "SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'iomad'
    AND TABLE_NAME = 'cocoon_local_bluedrop_table'
    AND COLUMN_NAME = 'id_bin'",
);

if (empty($columnExists)) {
    // Alter the table to add the id_bin and id_hex columns
    $DB->execute("ALTER TABLE {local_bluedrop_table}
    ADD COLUMN `id_bin` VARBINARY(16) FIRST,
    ADD COLUMN `id_hex` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin AS (LOWER(HEX(`id_bin`))) VIRTUAL COMMENT 'uuid (r/o insert id_bin instead)' AFTER `id_bin`");

    // Set the default value for id_bin using a generated UUID
    $DB->execute("UPDATE {local_bluedrop_table} SET id_bin = UUID_TO_BIN(UUID(),1)");

    // Add unique indexes for id_bin and id_hex
    $DB->execute("ALTER TABLE {local_bluedrop_table}
        ADD UNIQUE INDEX `idbin_idx` (`id_bin` ASC) VISIBLE,
        ADD UNIQUE INDEX `idhex_idx` (`id_hex` ASC) VISIBLE");
}

// Retrieve unique course names from the cocoon_course table
$courseNames = $DB->get_fieldset_select('course', 'DISTINCT fullname', '', [], 'fullname');

// Get the course name filter and flag filter from the query string
$courseNameFilter = optional_param('coursename', '', PARAM_TEXT);
$flagFilter = optional_param('flag', '', PARAM_INT);

// Prepare the SQL query with the course name filter
$sql = "SELECT *
        FROM {local_bluedrop_table}
        WHERE 1";

$params = [];

if (!empty($courseNameFilter)) {
    $sql .= " AND fullname IN (SELECT fullname FROM {local_bluedrop_table} WHERE fullname LIKE :coursename)";
    $params['coursename'] = '%' . $courseNameFilter . '%';
}

if (!empty($flagFilter)) {
    $sql .= " AND flag = :flag";
    $params['flag'] = $flagFilter;
}

// Retrieve the records from the user_info_data table
$records = $DB->get_records_sql($sql, $params);

echo $OUTPUT->header();

echo '<h2>Bluedrop Records</h2>';

// Display the search filter form with the dropdown menu
echo '<form action="display.php" method="GET">'; // Change method to GET
echo '<label for="coursename">Course Name:</label>';
echo '<select id="coursename" name="coursename">';
echo '<option value="">All Courses</option>'; // Add an option to show all courses
foreach ($courseNames as $courseName) {
    $selected = ($courseNameFilter === $courseName) ? 'selected' : '';
    echo '<option value="' . $courseName . '" ' . $selected . '>' . $courseName . '</option>';
}
echo '</select>';

echo '<span style="margin-right: 10px;"></span>';

echo '<label for="flag">Flag:</label>';
echo '<select id="flag" name="flag">';
echo '<option value="">All</option>'; // Add an option to show all flags
$flags = [1, 2]; // Assuming you have flag values 0 and 1
$flagLabels = ['Not Submitted', 'Submitted']; // Labels for the flag values
foreach ($flags as $index => $flag) {
    $selected = ($flagFilter === strval($flag)) ? 'selected' : '';
    echo '<option value="' . $flag . '" ' . $selected . '>' . $flagLabels[$index] . '</option>';
}
echo '</select>';

echo '<input type="submit" value="Search">';
echo '</form>';

if (!empty($records) && !empty($courseNameFilter)) {
    // Display the table with the search results
    echo '<form action="submit_to_bluedrop.php" method="POST">'; // Add form for submitting to Bluedrop
    echo '<br>';
    echo '<table style="border-collapse: collapse;">'; // Add border-collapse to remove gaps between cells
    echo '<tr><th>Flag</th><th>UUID</th><th>trainingStandardKey</th><th>Completion Date</th><th>externalClassID</th><th>networkKey</th><th>Instructor Names</th><th>Evaluator Names</th><th>User ID</th>
    <th>First Name</th><th>Last Name</th><th>Email</th><th>BirthYear</th><th>Address</th><th>City</th><th>Province</th><th>Postal Code</th><th>Country</th><th>Phone #1</th><th>Phone #2</th></tr>';
    foreach ($records as $record) {
        echo '<tr>';
        if ($record->flag == 2) {
            echo '<td style="padding: 8px; color: green;">Submitted</td>';
        } else {
            echo '<td style="padding: 8px; color: red">Not Submitted</td>';
        }

        echo '<td style="padding: 8px;">' . $record->id_hex . '</td>';
        echo '<td style="padding: 8px;">' . $record->training_standard_key . '</td>';

        $iso8601Date = date("c", strtotime($record->timecompleted));
        echo '<td style="padding: 8px;">' . $iso8601Date . '</td>';

        echo '<td style="padding: 8px;">' . $record->external_class_id . '</td>';
        echo '<td style="padding: 8px;">' . $networkKey . '</td>';

        echo '<td style="padding: 8px;">' . $record->instructor_names . '</td>';
        echo '<td style="padding: 8px;">' . $record->evaluator_names . '</td>';
        
        echo '<td style="padding: 8px;">' . $record->id . '</td>';
        echo '<td style="padding: 8px;">' . $record->firstname . '</td>';
        echo '<td style="padding: 8px;">' . $record->lastname . '</td>';
        echo '<td style="padding: 8px;">' . $record->email . '</td>';

        $year = date('Y', $record->dob);
        echo '<td style="padding: 8px;">' . $year . '</td>';

        echo '<td style="padding: 8px;">' . $record->address . '</td>';
        echo '<td style="padding: 8px;">' . $record->city . '</td>';
        echo '<td style="padding: 8px;">' . $record->province . '</td>';
        echo '<td style="padding: 8px;">' . $record->postal_code . '</td>';
        echo '<td style="padding: 8px;">' . $record->country . '</td>';
        
        echo '<td style="padding: 8px;">' . $record->phone1 . '</td>';
        echo '<td style="padding: 8px;">' . $record->phone2 . '</td>';

        echo '<td style="padding: 8px;">' . $record->fullname . '</td>';

        echo '<td style="padding: 8px;"><a href="edit.php?bluedrop_id=' . $record->bluedrop_id . '&training_standard_key=' . urlencode($record->training_standard_key) . 
        '&timecompleted=' . urlencode($record->timecompleted) . 
        '&instructor_names=' . urlencode($record->instructor_names) . '&evaluator_names=' . urlencode($record->evaluator_names) .
        '&firstname=' . urlencode($record->firstname) . '&lastname=' . urlencode($record->lastname) .
        '&email=' . urlencode($record->email) . '&dob=' . urlencode($record->dob) .
        '&address=' . urlencode($record->address) . '&city=' . urlencode($record->city) .
        '&province=' . urlencode($record->province) . '&postal_code=' . urlencode($record->postal_code) .
        '&country=' . urlencode($record->country) . '&phone1=' . urlencode($record->phone1) .
        '&phone2=' . urlencode($record->phone2) . '&fullname=' . urlencode($record->fullname) .
        '">Edit</a></td>';

        echo '<td style="padding: 8px;"><a href="delete.php?bluedrop_id=' . $record->bluedrop_id . '">Delete</a></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<br>';
    echo '<button type="submit" class="btn btn-primary">Submit to Bluedrop</button>'; // Improved Submit button with Bootstrap classes
    echo '</form>';
} else if (empty($records)) {
    echo '<br>';
    echo '<p>No records found.</p>';
} else {
    echo '<br>';
    echo '<p>Search for course name</p>';
}
echo '<br>';
echo '<form action="update.php" method="POST">';
echo '<button type="submit" class="btn btn-info" name="update" value="1">Update Table</button>';
echo '</form>';
echo $OUTPUT->footer();
