# Teachermatic Web Service

The TeacherMatic Moodle plugin is a local plugin that communicates with
[Teachermatic](https://teachermatic.com) to allow the exporting of files and quizzes from TeacherMatic into Moodle courses and question banks.

![Teachermatic logo](pix/teachermatic.svg)

## Introduction
TeacherMatic is designed by educators and sector leaders to meet the diverse needs of teachers, senior leaders, administrators, support staff, marketing teams, and HR. Simplify workflows, reduce costs, and enhance institutional efficiency—all in one unified platform.
TeacherMatic is a comprehensive set of tools that includes a wide range of AI generators that create a variety of essential materials for teachers. From lesson plans and quizzes to learning activities, schemes of work and plenaries, TeacherMatic has it all. Our user-friendly interface and intuitive design make it easy for teachers to find the resources they need when they need them.

To use the plugin, please contanct our [support teams](https://teachermatic.com/contact/) since you will need an valid organisation license to use the plugin.

Please note that while the plugin itself is free, registration on Teachermatic website for
an valid organisation subscription is a separate process. Your support is appreciated!

## Requirements
* Moodle moodle 4.2+ (tested with version `2023042400`)
* Valid Teachermatic organisation subscription

## Installation

### Installing via uploaded ZIP file

1. Go to Plugin Installation: In the admin area, navigate to **Site administration**, then **Plugins**, and select **Install plugins**.
2. Upload the Plugin: Click on **Choose a file...** to upload the plugin file (usually a .zip file) that you have downloaded.
3. Install the Plugin: Follow the on-screen instructions to complete the plugin installation.
4. Configure the Plugin: After installation, configure the plugin settings as needed.

### Installing manually

1. Extract the contents of the ZIP file to the `local/teachermatic` directory of your Moodle installation.
2. Go to **Site administration** > **Notifications**.
3. Follow the on-screen instructions to install the plugin.

## After installation

### How to use the plugin?

To get started, work within your Moodle administration panel to enable the Teachermatic external service and create a dedicated web service token for it. With that token, navigate to the Teachermatic website to configure your organisation's integration and copy your unique Organisation ID. For the final step, return to the Teachermatic plugin settings in Moodle and paste the Organisation ID to complete the setup.

Once the integration is active, your organisation's members will see the Export to Moodle option available when using Teachermatic's generators.

## Need Help?
If you encounter any issues during this process, our support team is ready to assist you. Please reach out to us via our [contact page](https://teachermatic.com/contact/).