# Chunk upload snippet for CI4 and React using uppy library 

Upload large files in chunks

# CI4 setup guide

* Run composer require aws/aws-sdk-php.
* Import the sql in your database.
* Copy the App/Libraries/S3_bucket code or the entire library in your library folder.
* Copy the 2 routes given in App/Config/Routes.php into your routes code
* Copy the Controller and Model code into your respective controller and model
* Add this code to your .htaccess:
  * Header always set Access-Control-Allow-Methods "OPTIONS,GET,POST,PUT,DELETE,PATCH,HEAD"
Header always set Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Domain, Upload-Length, Upload-Offset, Tus-Resumable, upload-metadata"
* Add the following to your .env file:
  * S3_KEY = 'YOUR_KEY'
  * S3_SECRET = 'YOUR_S3_SECRET'
  * S3_BASE_URL = 'YOUR_S3_URL'
  * S3_DEFAULT_BUCKET = 'DEFAULT_S3_BUCKET' 

# React setup guide

* Run npm install @uppy/react @uppy/core @uppy/dashboard @uppy/tus @uppy/drag-drop @uppy/progress-bar @uppy/file-input
* Copy and paste the React/UploadFile.jsx code


# Need Our Support?
For Paid support you can reach out to us by sending an email on: info@wolfnetwork.in or directly call us or whatsapp us on: +91 9137166653
