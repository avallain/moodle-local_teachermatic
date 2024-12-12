# Teachermatic Web Service

Teachermatic web service is a moodle local plugin that communicate with
teachermatic to save questions into Question Bank.

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
| Funtion name                            | Description                                    |
|-----------------------------------------|------------------------------------------------|
| `local_teachermatic_ping`               | Get the web service status                     |
| `local_teachermatic_get_courses`        | Get the user enrolled courses by email address |
| `local_teachermatic_create_multichoice` | Create multiple choice questions               |
| `local_teachermatic_create_truefalse`   | Create true-false questions                    |
| `local_teachermatic_create_shortanswer` | Create short answer questions                  |

Detail available parameters can be found in the postman collection

## License

> Teachermatic
