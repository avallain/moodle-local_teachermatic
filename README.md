# Teachermatic Web Service

Teachermatic Web Service is a Moodle local plugin designed to facilitate communication with the Teachermatic application for saving questions into the Moodle Question Bank.

## Installation

### Backend

1. Extract the contents of the ZIP file to the `local/teachermatic` directory of your Moodle installation.
2. Go to **Site administration** > **Notifications**.
3. Follow the on-screen instructions to install the plugin.

### Frontend

1. Go to Plugin Installation: In the admin area, navigate to **Site administration**, then **Plugins**, and select **Install plugins**.
2. Upload the Plugin: Click on **Choose a file...** to upload the plugin file (usually a .zip file) that you have downloaded.
3. Install the Plugin: Follow the on-screen instructions to complete the plugin installation.
4. Configure the Plugin: After installation, configure the plugin settings as needed.

## Available functions
The following functions are exposed by the Teachermatic Web Service:

| Funtion name                                    | Description                                                                                                    |
|-------------------------------------------------|----------------------------------------------------------------------------------------------------------------|
| `local_teachermatic_ping`                       | Get the web service status                                                                                     |
| `local_teachermatic_get_courses`                | Get the user enrolled courses by email address                                                                 |
| `local_teachermatic_create_multichoice`         | Create multiple choice questions                                                                               |
| `local_teachermatic_create_truefalse`           | Create true-false questions                                                                                    |
| `local_teachermatic_create_shortanswer`         | Create short answer questions                                                                                  |
| `local_teachermatic_create_course_mod_resource` | Create a mod_resource activity into a course.                                                                  |
| `local_teachermatic_get_courses_with_sections`  | Get the user enrolled courses with it is sections where the user is editing-teacher by the user email address. |

Detailed information about required parameters for each function can be found in the accompanying Postman collection.

## Issues and Feedback

If you encounter any issues or bugs, or have recommendations for new features, please feel free to **open an issue** on this repository.
Your feedback helps us improve Teachermatic Web Service.

## Copyright

© Teachermatic. All rights reserved.
