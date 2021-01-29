<?php
/**
* $Id$
*
* S3 class usage
*/

if (!class_exists('S3')) require_once 's3.php';

// AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'USE YOUR KEY');
if (!defined('awsSecretKey')) define('awsSecretKey', 'USE YOUR KEY');
$uploadFile = dirname(__FILE__).'/1.jpg'; // File to upload, we'll use the S3 class since it exists
$bucketName = uniqid('PHPUPLOAD'); // Temporary bucket. After upload to this "Temporary bucket" we can move the file to any bucket from s3 control panel.

// Check if our upload file exists
if (!file_exists($uploadFile) || !is_file($uploadFile))
	exit("\nERROR: No such file: $uploadFile\n\n");

// Check for CURL. CURL is must if not installed please install and try again.
if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll'))
	exit("\nERROR: CURL extension not loaded\n\n");

// Pointless without your keys!
if (awsAccessKey == 'change-this' || awsSecretKey == 'change-this')
	exit("\nERROR: AWS access information required\n\nPlease edit the following lines in this file:\n\n".
	"define('awsAccessKey', 'change-me');\ndefine('awsSecretKey', 'change-me');\n\n");

// Instantiate the class
$s3 = new S3(awsAccessKey, awsSecretKey);

// List your buckets:
echo "S3::listBuckets(): ".print_r($s3->listBuckets(), 1)."\n";

$bucketName = 'PHPTEST_BUCKET';
// Create a bucket with public read access
if ($s3->putBucket($bucketName, S3::ACL_PUBLIC_READ)) {
	echo "Created bucket {$bucketName}".PHP_EOL;

	// Put our file (also with public read access)
	if ($s3->putObjectFile($uploadFile, $bucketName, baseName($uploadFile), S3::ACL_PUBLIC_READ)) {
		echo "S3::putObjectFile(): File copied to {$bucketName}/".baseName($uploadFile).PHP_EOL;


		// Get the contents of our bucket
		$contents = $s3->getBucket($bucketName);
		echo "S3::getBucket(): Files in bucket {$bucketName}: ".print_r($contents, 1);


		// Get object info
		$info = $s3->getObjectInfo($bucketName, baseName($uploadFile));
		echo "S3::getObjectInfo(): Info for {$bucketName}/".baseName($uploadFile).': '.print_r($info, 1);


		
		
	} else {
		echo "S3::putObjectFile(): Failed to copy file\n";
	}
} else {
	echo "S3::putBucket(): Unable to create bucket (it may already exist and/or be owned by someone else)\n";
}